<?php

class ShippingDestinationsMapper {

    public static function getShippingDestinations($dbobj){
        $sql = "select *
                from shipping_destinations
                where status != " . DELETED;
        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function can_delete($dbobj, $shipping_id) {

        $sql = "select count(*) as cnt
                from shipping_destinations where shipping_option_id = $shipping_id and 
                status != " . DELETED;
        $return = false;
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return = default2Int($record['cnt']) > 1 ? TRUE : FALSE;
            }
        }

        return $return;
    }
}
