<?php

class MyorderGroupsMapper {

    public static function findOrCreateOrderGroup($dbobj, $user_id){
        $cart_id = CartsMapper::findCurrentCartForUser($dbobj, $user_id);
        $cond = "user_id = " . $user_id . " and cart_id = " . $cart_id . "
                 and status = " . ACTIVATED . " and payment_status = " . ORDER_UNPAID;
        $order = new MyorderGroup($dbobj);
        $order->findOne($cond);
        if($order->getId()<1){
            $order->setStatus(ACTIVATED);
            $order->setPaymentStatus(ORDER_UNPAID);
            $order->setUserId($user_id);
            $order->setCartId($cart_id);
            $order->save();
        }
        return $order;
    }


    public static function createMyorderGroup(
        $dbobj, $uid, $pay_method, $pay_info){
        // create myorder
        $myorder_grp = MyorderGroupsMapper::findOrCreateOrderGroup($dbobj, $uid);
        //$myorder_grp->setStatus(ACTIVATED);
        $myorder_grp->setUserId($uid);
        $myorder_grp->setPaymentMethod($pay_method);
        $myorder_grp->setPaymentInfo($pay_info);
        NativeCheckoutService::fillAddresses($myorder_grp, $_REQUEST, "shipping");
        $myorder_grp->save();
        // fill order items
        $num = MyordersMapper::fillMyorder($dbobj, $myorder_grp);
        return $myorder_grp;
    }

    public static function getOrders($dbobj, $order_grp_id, $status = -1){
        $status_cond = '';
        if(!empty($status)){
            if($status<0){
                $status_cond = " and mo.status != " . DELETED;
            } else {
                $status_cond = " and mo.status = " . $status;
            }
        }
        $sql = "select mo.*,
                s.name as store_name, s.subdomain, s.currency
                from myorders mo
                join stores s on (s.id = mo.store_id)
                where mo.myorder_group_id = $order_grp_id $status_cond";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $store_id = $record['store_id'];
                $subdomain = $record['subdomain'];
                $store_url = getStoreUrl($subdomain);
                $record['store_url'] = $store_url;
                $record['currency_symbol'] = currency_symbol($record['currency']);
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function updateMyorderGroupShipping($dbobj, $order_grp_id) {
        $sql = "select sum(shipping) as  shipping from myorders
                where myorder_group_id = $order_grp_id and status = " . ACTIVATED;

        $total_shipping = 0;
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $total_shipping = $record['shipping'];
            }
        }
        $sql = "update myorder_groups
                set shipping = $total_shipping, total = price + shipping
                where id = $order_grp_id";
        $dbobj->query($sql, $dbobj);
    }

    public static function setMyorderGroupCompleted($dbobj, $order_grp_id) {
        $o = new MyorderGroup($dbobj);
        $o->findOne("id=$order_grp_id");
        if($o->getId() !== 0) {
            $o->setStatus(COMPLETED);
            $o->setPaymentStatus(ORDER_PAID);
            $o->save();
            $sql = "update myorders set
                    status = " . COMPLETED . ",
                    payment_status = " . ORDER_PAID . "
                    where myorder_group_id = $order_grp_id and status = " . ACTIVATED;
            $dbobj->query($sql, $dbobj);
            $aid = $o->getAid();
            // fill sale wallet and resell order/wallet
            WalletsMapper::fillWallets($dbobj, $o->getId());
        }

        $myorders = self::getOrders($dbobj, $order_grp_id, $status = COMPLETED);
        foreach($myorders as $mo){
            $items = MyordersMapper::getOrderItems($dbobj, $mo['id']);
            foreach($items as $item){
                ProductsMapper::decreaseCount($item['store_id'], $item['product_id'], $item['custom_fields'], $item['product_quantity']);
            }
            Store::clearCache($mo['store_id']);
        }
    }

    public static function checkProductsInStockForOrders($dbobj, $order_grp_id, &$errors){
        $ret =TRUE;
        $myorders = self::getOrders($dbobj, $order_grp_id, $status = ACTIVATED);
        foreach($myorders as $mo){
            $items = MyordersMapper::getOrderItems($dbobj, $mo['id']);
            foreach($items as $item){
                $qty_in_stock = ProductsMapper::getProductQuantity($item['store_id'], $item['product_id'], $item['custom_fields']);
                if($qty_in_stock < $item['product_quantity']){
                    $errors[] = array(
                        'store_id' => $item['store_id'],
                        'product_id' => $item['product_id'],
                        'custom_field' => $item['custom_fields'],
                    );
                    $ret = FALSE;
                }
            }
        }
        return $ret;
    }

}
