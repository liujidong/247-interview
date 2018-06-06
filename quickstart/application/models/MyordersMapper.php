<?php

class MyordersMapper {

    public static function fillMyorder($dbobj, $order_grp){
        global $dbconfig;

        $user_id = $order_grp->getUserId();
        $cart_id = CartsMapper::findCurrentCartForUser($dbobj, $user_id);
        $cart = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name . ".cart?id=$cart_id"));
        $cart_items = CartsMapper::getProductsInCart($dbobj, $cart_id);
        $coupon = NULL;
        if(!empty($cart['coupon_code'])){
            $coupon = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name . ".mycoupon?code=" . $cart['coupon_code']));
        }
        if(count($cart_items) < 1) return 0;
        $order_grp->setCartId($cart_id);
        $aid = $cart['aid'];
        if(empty($aid)){
            $aid = DEFAULT_AID;
        }
        $order_grp->setAid($aid);
        $order_grp->setCurrencyCode($cart_items[0]['currency']);
        if(!empty($coupon) && $coupon['scope'] == SITE){
            $order_grp->setCouponCode($cart['coupon_code']);
        }
        $order_grp->save();
        $order_grp_id = $order_grp->getId();
        // clear old orders
        $sql = "update myorders set status = " . DELETED . "
                where myorder_group_id = $order_grp_id";
        $dbobj->query($sql, $dbobj);
        // fill new ones
        $order_items = array();
        foreach($cart_items as $item){
            $oi = array('status' => ACTIVATED);
            $oi['store_id'] = $item['store_id'];
            $oi['product_id'] = $item['product_id'];
            $oi['product_name'] = $item['name'];
            $oi['product_description'] = $item['description'];
            $oi['product_quantity'] = $item['quantity'];
            $oi['product_price'] = $item['price'];
            $oi['product_shipping'] = $item['shipping'];
            $oi['product_commission'] = $item['commission'];
            $oi['custom_fields'] = $item['custom_fields'];
            $oi['coupon_code'] = $item['coupon_code'];
            $order_items[$oi['store_id']][] = $oi;
        }
        foreach($order_items as $sid => $ois){
            $order = new Myorder($dbobj);
            $order->setStatus(ACTIVATED);
            $order->setPaymentStatus(ORDER_UNPAID);
            $order->setMyorderGroupId($order_grp_id);
            $order->setStoreId($sid);
            if(!empty($coupon) && $coupon['scope'] == STORE && $coupon['store_id'] == $sid){
                $order->setCouponCode($cart['coupon_code']);
            }
            $order->save();
            $order_id  = $order->getId();
            $items = array();
            foreach($ois as $item){
                $item['order_id'] = $order_id;
                $items[] = $item;
            }
            BaseModel::saveObjects($dbobj, $items, "myorder_items");
            self::updateOrderShipping($dbobj, $order_id, 'Standard', $order_grp->getShippingCountry());
        }
        return count($cart_items);
    }

    public static function clearStoreItems($dbobj, $store_id){
        $sql = "update myorder_items mi join myorders mo on (mi.order_id = mo.id)
                set mi.status = " . DELETED ." where mi.store_id = $store_id and mo.status = " . ACTIVATED;
        if($res = $dbobj->query($sql, $dbobj)) {
            return true;
        }
        return false;
    }

    public static function getOrderItems($dbobj, $order_id){
        global $dbconfig;

        $sql = "select mi.*
                from myorder_items mi
                where mi.order_id = $order_id and mi.status = " . ACTIVATED;

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $store_id = $record['store_id'];
                $product_id = $record['product_id'];
                $ck = CacheKey::q($dbconfig->store->name . "_" . $store_id . ".product?id=" . $product_id);
                $record = array_merge(BaseModel::findCachedOne($ck), $record);
                $record['product_price'] = $record['price'];
                $record['thumb'] = reset($record['pictures']['45']);
                $record['currency'] = $record['store_currency'];
                $record['currency_symbol'] = currency_symbol($record['currency']);
                $record['subtotal'] = $record['product_quantity'] * $record['product_price'] +
                    $record['product_shipping'];
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function updateOrderShipping($dbobj, $order_id, $shipping_name, $dest){
        global $dbconfig;

        $items = self::getOrderItems($dbobj, $order_id);
        if(count($items)<1) return false;
        $store_id = $items[0]['store_id'];
        $product_ids = array_map(function($i){return $i['product_id'];}, $items);
        $store_dbobj = DBObj::getStoreDBObjById($store_id);
        $shipping_options = ShippingOptionsMapper::getShippingOptionsForOrder(
            $store_dbobj, $product_ids, $dest);

        $shipping_option = isset($shipping_options[$shipping_name]) ? $shipping_options[$shipping_name]: NULL;
        if(!$shipping_option) return false;
        $order = new Myorder($dbobj);
        $order->setId($order_id);
        $order->set("shipping_option_id", $shipping_option['shipping_option_id']);
        $order->set("shipping_destination_id", $shipping_option['dest_id']);
        $order->save();

        $total_shipping = 0;
        $items_to_save = array();
        for($i = 0; $i < count($items); $i++){
            $tmp_shipping = 0;
            $free_shipping = FALSE;
            if(!empty($item['coupon_code'])){
                $item_coupon = BaseModel::findCachedOne(
                    CacheKey::q($dbconfig->account->name . ".mycoupon?code=" . $item['coupon_code']));
                if(isset($item_coupon['free_shipping']) && $item_coupon['free_shipping']){
                    $free_shipping = TRUE;
                }
            }
            if(!$free_shipping){
                if(empty($total_shipping)){
                    $tmp_shipping = $shipping_option['base'] + $shipping_option['additional'] * ($items[0]['product_quantity'] - 1);
                } else {
                    $tmp_shipping = $shipping_option['additional'] * $items[$i]['product_quantity'];
                }
            }
            $total_shipping += $tmp_shipping;
            $items_to_save[] = array(
                "id" => $items[$i]['id'],
                "product_shipping" => $tmp_shipping,
            );
        }

        BaseModel::saveObjects($dbobj, $items_to_save, "myorder_items");

        $sql = "update myorders set shipping = $total_shipping, total = price + shipping + tax
                where id = $order_id and status = " . ACTIVATED;
        $dbobj->query($sql, $dbobj);
        return true;
    }

    public static function getOrderGroupByOrderId($dbobj, $oid) {
        $sql = "select g.*
                from myorder_groups g
                join myorders o on(g.id = o.myorder_group_id)
                where o.id = $oid and g.status = ". ACTIVATED . " limit 1";
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record;
            }
        }
    }

    public static function getOrdersForMerchant($dbobj, $store_id, $status = '', $page_num = 1, $page_size = 10){
        $limit = " order by id desc limit " . (($page_num-1) * $page_size) . "," . $page_size;
        $cond = " status != " . DELETED;
        if(!empty($status)){
            $cond = $cond . " and payment_status = " . $status;
        }
        $sql = "select * from myorders where store_id = $store_id and $cond $limit";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getOrdersCntForMerchant($dbobj, $store_id, $status = ''){
        $cond = " status != " . DELETED;
        if(!empty($status)){
            $cond = $cond . " and payment_status = " . $status;
        }
        $sql = "select count(1) as cnt from myorders where store_id = $store_id and $cond";

        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['cnt'];
            }
        }
        return 0;
    }

    public static function getCachedObject($params, $dbobj) {
        $oid = $params['id'];
        $sql = "select mo.*,
                g.currency_code, g.user_id,
                g.shipping_first_name, g.shipping_last_name,
                g.shipping_addr1, g.shipping_addr2,
                g.shipping_city, g.shipping_state, g.shipping_country,
                g.shipping_zip, g.shipping_phone, g.shipping_email
                from myorders mo
                join myorder_groups g on (mo.myorder_group_id = g.id)
                where mo.id = $oid";
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $record['currency_symbol'] = currency_symbol($record['currency_code']);
                return $record;
            }
        }
        return NULL;
    }

    public static function getCachedObjectList($params, $dbobj) {
        $ck = $params['_cachekey'];
        $dbname = $ck->getDBName();
        $sort = $ck->getOrderInfo();
        $sort = $sort['orderby'];
        $label = $ck->getLabel();

        $keys = array();
        $orders = array();
        $sql = "";

        if($label == "FOR_STORE" || isset($params['store_id'])){
            $sql = "select id, updated from myorders where status!=" .DELETED ."
                    and " . $ck->conditionSQL();
        }else if($label == "FOR_SHOPPER" || isset($params['user_id'])){
            $sql = "select mo.id, mo.updated
                    from myorders mo
                    join myorder_groups g on (g.id = mo.myorder_group_id)
                    where g.status!=" .DELETED;

            if(isset($params['payment_status'])){
                $sql = $sql . " and mo.payment_status = " . $params['payment_status'];
            }
            if( $params['status'] != DELETED){
                $sql = $sql . " and mo.status = " . $params['status'];
            } else {
                $sql = $sql . " and mo.status != " . DELETED;
            }
            $sql = $sql . " and g.user_id = " . $params['user_id'];
            $sql = $sql . ' order by mo.updated desc';
        }else{
            $sql = "select id, updated from myorders where " . $ck->conditionSQL();
        }

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $orders[] = $record;
            }
        }

        if(count($sort) != 1) { // no order, no score
            foreach($orders as $order) {
                $key = $dbname.".myorder?id=".$order['id'];
                $keys[] = $key;
            }
        } else {
            $order_key = $sort[0];
            foreach($orders as $order) {
                $score = 0;
                if($order_key == 'updated'){
                    $score = strtotime2($order['updated']);
                } else {
                    $score = isset($product[$order_key]) ? $product[$order_key] : 0;
                }
                $key = $dbname.".myorder?id=".$order['id'];
                $keys[$key] = $score;
            }
        }
        return $keys;
    }

    public static function getOrdersCountForUser($dbobj, $uid, $status){
        $sql = "select count(1) as cnt
                from myorders mo
                join myorder_groups mg on (mo.myorder_group_id = mg.id)
                where mo.payment_status = $status and mo.status != " . DELETED . "
                and mg.user_id =$uid and mg.status != " . DELETED;
        if($res = $dbobj->query($sql)){
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['cnt'];
            }
        }
        return 0;
    }

    public static function getOrdersCountForStore(
        $dbobj, $store_id, $payment_status = NULL, $status = NULL, $date_after = NULL
    ){
        $cond = "";
        if(!empty($payment_status)){
            $cond = "mo.payment_status = $payment_status";
        } else {
            $cond = "mo.payment_status != " . ORDER_UNPAID;
        }

        if(!empty($status)){
            $cond .= " and mo.status = $status";
        } else {
            $cond .= " and mo.status != " . DELETED;
        }

        if(!empty($date_after)){
            $cond .= " and mo.created > $date_after";
        }

        $sql = "select count(1) as cnt
                from myorders mo
                join myorder_groups mg on (mo.myorder_group_id = mg.id)
                where mo.store_id =$store_id and mg.status != " . DELETED . " and ". $cond;

        if($res = $dbobj->query($sql)){
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['cnt'];
            }
        }
        return 0;
    }

    public static function getSaleAmountForStore(
        $dbobj, $store_id, $payment_status = NULL, $status = NULL, $date_after = NULL
    ){
        $cond = "";
        if(!empty($payment_status)){
            $cond = "mo.payment_status = $payment_status";
        } else {
            $cond = "mo.payment_status != " . ORDER_UNPAID;
        }

        if(!empty($status)){
            $cond .= " and mo.status = $status";
        } else {
            $cond .= " and mo.status != " . DELETED;
        }

        if(!empty($date_after)){
            $cond .= " and mo.created > $date_after";
        }

        $sql = "select sum(mo.total) as amount
                from myorders mo
                join myorder_groups mg on (mo.myorder_group_id = mg.id)
                where mo.store_id =$store_id and mg.status != " . DELETED . " and ". $cond;
        if($res = $dbobj->query($sql)){
            if($record = $dbobj->fetch_assoc($res)) {
                return isset($record['amount']) ? $record['amount'] : 0;
            }
        }
        return 0;
    }
}
