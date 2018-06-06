<?php

class CartsMapper {

    public static function findCurrentCartForUser($dbobj, $user_id=NULL,  $create=True) {
        $cond = "";
        if(empty($user_id)){
            $sql = "select
                    c.*
                    from carts c
                    where c.session_id = '" . get_sessid() . "' and c.status = " . ACTIVATED;
        } else {
            $sql = "select
                    c.*
                    from carts c
                    inner join users_carts uc on (c.id = uc.cart_id)
                    where uc.user_id = " . $user_id . " and c.status = " . ACTIVATED;
        }


        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['id'];
            }
        }
        if(!$create) return 0;
        $cart = new Cart($dbobj);
        $cart->setStatus(ACTIVATED);
        if(empty($user_id)){
            $cart->setSessionId(get_sessid());
        }
        $cart->save();
        $cart_id = $dbobj->get_insert_id();

        if(!empty($user_id)){
            $user = new User($dbobj);
            $user->setId($user_id);
            BaseMapper::saveAssociation($user, $cart, $dbobj);
        }
        return $cart_id;
    }

    public static function transferCart($dbobj, $user_id) {
        $anon_cart_id = self::findCurrentCartForUser($dbobj, NULL, False);
        if(empty($anon_cart_id)) return;
        if(empty2(self::getItemsCntInCart($dbobj, $anon_cart_id))) return;
        $user_cart_id = self::findCurrentCartForUser($dbobj, $user_id, False);
        if(empty($user_cart_id)){
            $user = new User($dbobj);
            $user->setId($user_id);
            $cart = new Cart($dbobj, $anon_cart_id);
            $cart->setSessionId('');
            $cart->save();
            BaseMapper::saveAssociation($user, $cart, $dbobj);
            return;
        }
        $sql_0 = "update cart_items set status = " . DELETED . " where cart_id = " . $user_cart_id;
        $sql_1 = "update cart_items set cart_id = " . $user_cart_id . " where cart_id = " . $anon_cart_id;
        $dbobj->query($sql_0, $dbobj);
        $dbobj->query($sql_1, $dbobj);
    }

    public static function getItemsCntInCart($dbobj, $cart_id){
        if(empty($cart_id)) return 0;
        $sql = "select sum(quantity) as cnt from cart_items where cart_id = $cart_id and status !=" . DELETED;
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $r = $record['cnt'];
                if(empty($r))return 0;
                return $r;
            }
        }
        return 0;
    }

    public static function clearStoreItems($dbobj, $store_id){
        $sql = "update cart_items set status = " . DELETED ." where store_id = $store_id";
        if($res = $dbobj->query($sql, $dbobj)) {
            return true;
        }
        return false;
    }

    public static function getCurrencyOfCart($dbobj, $cart_id){
        $sql = "select s.currency
                from cart_items ci
                join stores s on (s.id = ci.store_id)
                where ci.cart_id = $cart_id and ci.status !=" . DELETED . "
                limit 1";
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['currency'];
            }
        }
        return null;
    }

    public static function getDealerOfCart($dbobj, $cart_id){
        $sql = "select ci.dealer
                from cart_items ci
                where ci.cart_id = $cart_id and ci.status !=" . DELETED . "
                limit 1";
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['dealer'];
            }
        }
        return null;
    }

    public static function addProductToCart(
        $dbobj, $cart_id,
        $store_id, $product_id, $quantity, $custom_field = '',
        $dealer ='' , $external_id = '',
        $aid = '') {
        $product_num = 0;

        $cond = "cart_id = $cart_id and store_id = $store_id " .
            "and custom_fields = '" . $dbobj->escape($custom_field) . "' ".
            "and product_id = $product_id and status !=" . DELETED;
        if(!empty($dealer) && !empty($external_id)){
            $cond = "cart_id = $cart_id " .
                "and dealer = '" . $dbobj->escape($dealer) . "' ".
                "and external_id = '" . $dbobj->escape($external_id) . "' ".
                "and status !=" . DELETED;
        }

        $citem = new CartItem($dbobj);
        $citem->findOne($cond);
        if($citem->getId()>0){
            $quantity = $citem->getQuantity() + $quantity;
            if($quantity<1){
                $citem->setStatus(DELETED);
                $citem->save();
            } else {
                $citem->setQuantity($quantity);
                $product_num = $quantity;
                $citem->save();
            }
        } else {
            if(!empty($quantity) && $quantity>0){
                $citem->setCartId($cart_id);
                $citem->setStoreId($store_id);
                $citem->setProductId($product_id);
                $citem->setQuantity($quantity);
                $citem->setCustomFields($custom_field);
                $citem->setDealer($dealer);
                $citem->setExternalId($external_id);
                $product_num = $quantity;
                $citem->save();
            }
        }

        if(!empty($aid)){
            $cart = new Cart($dbobj);
            $cart->setId($cart_id);
            $cart->setAid($aid);
            $cart->save();
        }

        $cart_num = self::getItemsCntInCart($dbobj, $cart_id);

        return array($cart_num, $product_num);
    }

    public static function getTotalPricesOfCart($dbobj, $cart_id){
        global $dbconfig;

        $sql = "select ci.*
                from cart_items ci
                where ci.cart_id = $cart_id and ci.status != " . DELETED ;

        $cs = NULL;
        $total = 0;
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $store_id = $record['store_id'];
                $product_id = $record['product_id'];
                $quantity = $record['quantity'];
                if(!empty($store_id)){
                    $ck = CacheKey::q($dbconfig->store->name . "_" . $store_id . ".product?id=" . $product_id);
                    // the store maybe changed
                    $p = BaseModel::findCachedOne($ck, array('force'=>true));
                } else if($record['dealer'] == 'amazon'){
                    $ck = CacheKey::q($dbconfig->account->name . ".amazon_product?asin=" . $record['external_id']);
                    // the store maybe changed
                    $p = BaseModel::findCachedOne($ck, array('force'=>true));
                }
                if(empty($cs)) {
                    $cs = currency_symbol(default2String($p['store_currency'], 'USD'));
                }
                $total += $quantity * $p['price'];
            }
        }
        return $cs . $total;
    }

    public static function getProductsInCart($dbobj, $cart_id) {
        global $dbconfig;

        $sql ="select ci.*
               from cart_items ci
               where ci.cart_id = $cart_id and ci.status != " . DELETED . "
               group by ci.store_id, ci.product_id, ci.custom_fields, ci.external_id
               order by ci.store_id";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $store_id = $record['store_id'];
                $product_id = $record['product_id'];
                if(!empty($store_id)){
                    $ck = CacheKey::q($dbconfig->store->name . "_" . $store_id . ".product?id=" . $product_id);
                    // the store maybe changed
                    $p = BaseModel::findCachedOne($ck, array('force'=>true));
                    $record = array_merge($p, $record);
                } else if($record['dealer'] == 'amazon'){
                    $ck = CacheKey::q($dbconfig->account->name . ".amazon_product?asin=" . $record['external_id']);
                    // the store maybe changed
                    $p = BaseModel::findCachedOne($ck, array('force'=>true));
                    $record = array_merge($p, $record);
                }
                $record['thumb'] = reset($record['pictures']['45']);
                $record['currency'] = default2String($record['store_currency'], 'USD');
                $record['currency_symbol'] = currency_symbol($record['currency']);
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getProductsInCurrentCart($dbobj, $user_id) {
        $cart_id = self::findCurrentCartForUser($dbobj, $user_id);
        return self::getProductsInCart($dbobj, $cart_id);
    }

    public static function setCartCompleted($dbobj, $cart_id) {
        $o = new Cart($dbobj);
        $o->findOne("id=$cart_id");
        if($o->getId() !== 0) {
            $o->setStatus(COMPLETED);
            $o->save();
        }
        $sql = "udpate cart_items set status =  " . COMPLETED ."
                where cart_id = $cart_id and status = " . ACTIVATED;

        $dbobj->query($sql, $dbobj);
    }

    public static function applyCouponToCurrentCart($dbobj, $coupon, $user_id){
        $cart_id = self::findCurrentCartForUser($dbobj, $user_id);
        if($coupon['scope'] == SITE){
            $cart = new Cart($dbobj);
            $cart->setId($cart_id);
            $cart->setCouponCode($coupon['code']);
            $cart->save();
            MycouponsMapper::decreaseUsageLimit($dbobj, $coupon['code']);
            return array("coupon_code"=>$coupon['code'], 'apply_type'=> SITE, 'apply_id'=> 0);
        } else {
            $products = self::getProductsInCart($dbobj, $cart_id);
            if($coupon['scope'] == STORE){
                foreach($products as $p){
                    if($p['store_id'] == $coupon['store_id']){
                        $cart = new Cart($dbobj);
                        $cart->setId($cart_id);
                        $cart->setCouponCode($coupon['code']);
                        $cart->save();
                        MycouponsMapper::decreaseUsageLimit($dbobj, $coupon['code']);
                        return array("coupon_code"=>$coupon['code'], 'apply_type'=> STORE, 'apply_id'=> $p['store_id']);
                    }
                }
            } else if($coupon['scope'] == PRODUCT){
                foreach($products as $p){
                    if($p['store_id'] == $coupon['store_id'] && $p['product_id'] == $coupon['product_id']){
                        $cart_item = new CartItem($dbobj);
                        $cart_item->setId($p['id']);
                        $cart_item->setCouponCode($coupon['code']);
                        $cart_item->save();
                        MycouponsMapper::decreaseUsageLimit($dbobj, $coupon['code']);
                        return array("coupon_code"=>$coupon['code'], 'apply_type'=> PRODUCT, 'apply_id'=> $p['product_id']);
                    }
                }
            }
        }
        return false;
    }

    public static function clearCouponOfCurrentCart($dbobj, $coupon, $user_id){
        $cart_id = self::findCurrentCartForUser($dbobj, $user_id);
        $cart = new Cart($dbobj, $cart_id);
        if($cart->getCouponCode() == $coupon){
            $cart->setCouponCode('');
            $cart->save();
            MycouponsMapper::decreaseUsageLimit($dbobj, $coupon, -1);
            return true;
        }
        $products = self::getProductsInCart($dbobj, $cart_id);
        foreach($products as $p){
            //if($p['store_id'] == $coupon['store_id'] && $p['product_id'] == $coupon['product_id']){
            $cart_item = new CartItem($dbobj, $p['id']);
            if($cart_item->getCouponCode() == $coupon){
                $cart_item->setCouponCode('');
                $cart_item->save();
                MycouponsMapper::decreaseUsageLimit($dbobj, $coupon, -1);
                return true;
            }
            //}
        }
        return false;
    }

    public static function getProductCountInCart($dbobj, $user_id, $store_id, $product_id, $cf, $dealer="", $ext_id=''){
        $cart_id = self::findCurrentCartForUser($dbobj, $user_id);
        $sql = "select quantity from cart_items
                where cart_id = $cart_id and store_id = $store_id
                and product_id = $product_id and status != 127
                and custom_fields = '" . $dbobj->escape($cf) . "'";

        if(!empty($dealer) && !empty($ext_id)){
            $sql = "select quantity from cart_items
                where cart_id = $cart_id and dealer = '" . $dbobj->escape($dealer) . "'
                and external_id = '" . $dbobj->escape($ext_id) . "'
                and status != 127";
        }

        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['quantity'];
            }
        }
        return 0;
    }

    public static function deleteProductsFromCart($dbobj, $user_id, $products){
        $cart_id = self::findCurrentCartForUser($dbobj, $user_id);
        foreach($products as $pinfo){
            $sql = "update cart_items set status = 127
                    where cart_id = $cart_id and store_id = " . $pinfo['store_id'] . "
                    and product_id = " . $pinfo['product_id'];
            $res = $dbobj->query($sql, $dbobj);
        }
    }
}
