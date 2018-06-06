<?php
class ResellOrderItemsMapper {

    public static function getUserIdOfAid($dbobj, $aid){
        $user_id = 0;
        if(startsWith($aid, "assoc")){
            $user_id = (substr($aid, 5));
        } else {
            $user = new User($dbobj);
            $user->findOne("aid = '" . $dbobj->escape($aid) . "'");
            $user_id = $user->getId();
        }
        return $user_id;
    }

    public static function getResellOrderItemsCount($dbobj, $uid, $date_after = NULL){
        $cond = "";
        if(!empty($date_after)){
            $cond .= " and roi.created > $date_after";
        }

        $sql = "select count(1) as cnt
                from resell_order_items roi
                where roi.status != " . DELETED . "
                and roi.user_id =$uid " . $cond;

        if($res = $dbobj->query($sql)){
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['cnt'];
            }
        }
        return 0;
    }

    public static function getCachedObject($params, $dbobj) {
        $oid = $params['id'];
        $sql = "select roi.*,
                mi.store_id, mi.product_id, mi.product_name,
                mi.product_quantity, mi.product_price, mi.product_commission,
                mi.custom_fields
                from resell_order_items roi
                join myorder_items mi on (roi.myorder_item_id = mi.id)
                where roi.id = $oid";
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $record['currency_symbol'] = currency_symbol($record['currency']);
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

        if($label == "FOR_SHOPPER" || isset($params['user_id'])){
            $sql = "select roi.id, roi.updated
                    from resell_order_items roi
                    where roi.user_id = " . $params['user_id'] . "
                    and roi.status != " . DELETED;
            if(isset($params['payment_status'])){
                $sql = $sql . " and roi.payment_status = " . $params['payment_status'];
            }
            $sql = $sql . ' order by roi.updated desc';
        }else{
            return $keys;
        }

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $orders[] = $record;
            }
        }

        if(count($sort) != 1) { // no order, no score
            foreach($orders as $roi) {
                $key = $dbname.".resell_order_item?id=".$roi['id'];
                $keys[] = $key;
            }
        } else {
            $order_key = $sort[0];
            foreach($orders as $roi) {
                $score = 0;
                if($order_key == 'updated'){
                    $score = strtotime2($roi['updated']);
                } else {
                    $score = isset($product[$order_key]) ? $product[$order_key] : 0;
                }
                $key = $dbname.".resell_order_item?id=".$roi['id'];
                $keys[$key] = $score;
            }
        }
        return $keys;
    }
}
