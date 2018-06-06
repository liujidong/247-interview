<?php

class MyorderItemsMapper {
    public static function getSalesCnt($dbobj, $store_id) {

        $sql1 = "select sum(product_quantity) as cnt from myorder_items where store_id = $store_id and status = ". COMPLETED;
        $cnt = 0;

        if($res = $dbobj->query($sql1, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = (int) $record['cnt'];
            }
        }
        return $cnt;
    }
 }