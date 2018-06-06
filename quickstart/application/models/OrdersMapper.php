<?php

class OrdersMapper {

    public static function getOrders($dbobj, $statuses=array('Pending', 'Completed')) {
        
        $status_filter = '';
        if(!empty($statuses)) {
            $status_filter = 'where';
            foreach($statuses as $i=>$status) {
                if($i === 0) {
                    $status_filter = $status_filter." payment_status='$status'";
                } else {
                    $status_filter = $status_filter." or payment_status='$status'";
                }
            }
        }
        
        $sql = "select * from orders $status_filter order by updated desc";
        $return = array();
        
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        
        return $return;
        
    }
    
    public static function getOrderDetails($order_id, $dbobj) {
        $sql = "select
                    o.id as order_id,
                    o.status as order_status,
                    o.shopper_id, o.currency_code,
                    round(o.total,2) as order_total,
                    round(o.price,2) as order_price,
                    round(o.tax,2) as order_tax,
                    round(o.shipping,2) as order_shipping,
                    o.note as order_note,
                    o.to_name as order_to_name,
                    o.to_address_id as order_to_address_id,
                    oi.quantity as item_quantity,
                    oi.price as item_price,
                    oi.shipping as item_shipping,
                    p.name as product_name,
                    p.description as product_description,
                    p.price as product_price,
                    p.shipping as product_shipping,
                    group_concat(pi.url) as picture_urls
            from 
            orders o join order_items oi on (o.id=oi.order_id) join
            products p on (oi.product_id=p.id) join 
            products_pictures pp on (pp.product_id=p.id) join
            pictures pi on (pi.id=pp.picture_id)
            where
            o.id=$order_id group by oi.id";
        $return = array();
        
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $record['item_price'] = round($record['item_price'] * $record['item_quantity'], 2);
                $return[] = $record;
            }
        }
        
        return $return;
        
    }
    
}

