<?php

class JobsMapper {
    
    public static function getNextJobId($type, $dbobj) {
        $created_status = CREATED;
        $processing_status = PROCESSING;
        $uniqid = uniqid();
        $update_sql = sprintf("update jobs set uniqid='$uniqid', status=$processing_status where type=$type and status=$created_status order by priority desc, created asc limit 1");
        $dbobj->query($update_sql);
        $select_sql = sprintf("select id from jobs where uniqid='$uniqid' and status=$processing_status");        
        if($res = $dbobj->query($select_sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['id'];
            }
        }
        return false;
    }
    
    public static function getJobs($filter,$dbobj){
        $wheres = array();
        if(isset($filter['store_id'])){
            $wheres[] = 'data like "%store_id\":\"'.$filter['store_id'].'%"';   
        }
        if(isset($filter['type'])){
            $wheres[] = ' type = '.$filter['type'];
        }
        if(isset($filter['status'])){
            $wheres[] = ' status in ('.implode(',', $filter['status']).')';
        }
        
        $where = '';
        if(!empty($wheres)){
            $where .= "where ".implode(' and ', $wheres);
        }
        
        $sql = "select * from jobs ".$where;
        
        $return = array();
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[]=$record;
            }
        }
        return $return;
    }
    
}


