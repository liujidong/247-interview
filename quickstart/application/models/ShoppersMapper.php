<?php

class ShoppersMapper {
    
    public static function getShopperByOrder($dbobj, $order_id){
        $sql = "select o.shopper_id from orders o
                where o.id = $order_id ";
        $return = array();
        
        if($res = $dbobj->query($sql)) {
            $record = $dbobj->fetch_assoc($res);
            $return = $record;
        }
        
        return $return;
    }
}
?>