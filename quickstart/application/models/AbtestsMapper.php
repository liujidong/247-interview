<?php

class AbtestsMapper {
    
    public static function getAllAbtests($dbobj) {
        $sql = 'select * from abtests where status='.CREATED.' order by id';
        
        $return = array();
     	
     	if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
     	}
     	return $return;
     }
    
}