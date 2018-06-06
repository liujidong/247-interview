<?php

class PinterestPinsMapper {
    public static function getAllPinsId($dbobj, $pin_id=0){
        $where = 'where id>='.$pin_id;
        $sql = "select id from pinterest_pins $where order by id";
        
        $return = array();
     	
     	if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record['id'];
            }
     	}
     	return $return;
    }
    
    public static function getUnSyncPins($dbobj, $limit=100) {
        $sql = "select pp.* from pinterest_pins pp 
            left join pin_images pi 
            on (pp.id = pi.pinterest_pin_id) 
            where (pi.pinterest_pin_id is NULL or pi.image_45 = '') and (pi.status!=".DELETED." or pi.status is NULL)
            limit 0,$limit";
        $return = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)){
                $return[] = $record;
            }
        }
        return $return;        
    }
}


