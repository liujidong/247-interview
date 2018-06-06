<?php

class CouponsMapper {

    public static function getDeal($store_id, $product_id, $dbobj){
        $where = array();

        $where['store_id'] = ' store_id ='.$store_id ;
        $where['product_id'] = ' product_id = '. $product_id ;
        $where['start_time'] = ' start_time <= now() ';
        $where['end_time'] = ' now() <= end_time ';

        $sql = "select * from coupons where ";
        $sql .= implode(' AND ', $where);

        $return = array();

        if ($res = $dbobj->query($sql)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }

        }
        return $return;
    }

    public static function getActiveDeals($dbobj, $status = array(ACTIVATED), $include_expired=0){
        if($include_expired === 0) {
            $filter_time = ' and c.start_time < now() and c.end_time > now() ';
        } else {
            $filter_time = ' ';
        }
        $sql = "select c.*,
                case
                when c.status = 2 then 'ACTIVE'
                when c.status = 0 then 'CREATED'
                when c.status = 127 then 'INACTIVE'
                end as deal_status,
                s.currency
                from coupons c
                left join stores s on (s.id = c.store_id)
                where c.status in (".implode(',', $status). ")
                $filter_time
                and c.scope = ".PRODUCT.
                " order by c.start_time desc, deal_status asc ";
        $return = array();
        if($res=$dbobj->query($sql)){
            while($record=$dbobj->fetch_assoc($res)){
                $return[]=$record;
            }
        }
        return $return;
    }

    public static function getDealByCode($code, $dbobj){
        $sql = "select * from coupons where code = '$code'";
        $return = array();

        if ($res = $dbobj->query($sql)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }

        }
        return $return;
    }

}
