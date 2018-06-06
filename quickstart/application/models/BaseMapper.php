<?php


class BaseMapper {
    
    public static function findOne($condition, $table_name, $dbobj) {
        $sql = "select * from $table_name";
        if(empty($condition)) {
            $where = '';
        } else {
            $where = "where $condition";
        }
        $limit = "limit 1";
        $sql = $sql.' '.$where.' '.$limit;
        $record = array('id'=>0);
        if($res = $dbobj->query($sql)) {
            $fetch_assoc = $dbobj->fetch_assoc($res);
            return is_array($fetch_assoc) ? $fetch_assoc : $record;
        }
        return $record;
    }
    
    public static function save($fields, $table_name, $dbobj) {
        $sql = getSaveSql($fields, $table_name, $dbobj);
        $dbobj->query($sql);
        return $dbobj->get_insert_id();
    }

    // both obj1 and obj2 are model class objects
    // trick: you dont need to fill data to the object but just use
    // $obj1 = new Object1();
    // $obj1->setId($id1);
    // $obj2 = new Object2();
    // $obj2->setId($id2);
    // by this way, you may be able to save two queries
    public static function saveAssociation($obj1, $obj2, $dbobj, $auto_sync_list = True) {
        if($auto_sync_list) {
            $ck1 = $obj1->getCacheKey();
            $old_data_1 = DAL::get($ck1);
            $ck2 = $obj2->getCacheKey();
            $old_data_2 = DAL::get($ck2);
            self::_internal_save_assoc($obj1, $obj2, $dbobj);
            DAL::s($ck1, $old_data_1);
            DAL::s($ck2, $old_data_2);
        } else {
            self::_internal_save_assoc($obj1, $obj2, $dbobj);
        }
    }

    public static function _internal_save_assoc($obj1, $obj2, $dbobj) {
    	
        $table1 = $obj1->getTableNameFromClassName();
        
        $table2 = $obj2->getTableNameFromClassName();
        $table = $table1.'_'.$table2;
        
       	$class1 = from_camel_case(get_class($obj1));
        
        $class2 = from_camel_case(get_class($obj2));
       	
        
        $field1 = $class1.'_id';
        $field2 = $class2.'_id';
        
        $id1 = $obj1->getId();
        
        $id2 = $obj2->getId();
        
        $sql = "insert into $table($field1, $field2, created) values('$id1', '$id2', now()) on duplicate key update
            $field1='$id1', $field2='$id2'";

        $dbobj->query($sql);
        
        return $dbobj->get_insert_id();
        
    }

    public static function deleteAssociation($obj1, $obj2, $dbobj, $auto_sync_list = True) {
        if($auto_sync_list) {
            $ck1 = $obj1->getCacheKey();
            $old_data_1 = DAL::get($ck1);
            $ck2 = $obj2->getCacheKey();
            $old_data_2 = DAL::get($ck2);
            self::_internal_del_assoc($obj1, $obj2, $dbobj);
            DAL::s($ck1, $old_data_1);
            DAL::s($ck2, $old_data_2);
        } else {
            self::_internal_del_assoc($obj1, $obj2, $dbobj);
        }
    }

    // both obj1 and obj2 are model class objects
    public static function _internal_del_assoc($obj1, $obj2, $dbobj) {
    	
        $table1 = $obj1->getTableNameFromClassName();
        
        $table2 = $obj2->getTableNameFromClassName();
        $table = $table1.'_'.$table2;
        
       	$class1 = from_camel_case(get_class($obj1));
        
        $class2 = from_camel_case(get_class($obj2));
       	
        
        $field1 = $class1.'_id';
        $field2 = $class2.'_id';
        
        $id1 = $obj1->getId();
        
        $id2 = $obj2->getId();
        
        $sql = "delete from $table where
            $field1='$id1' and $field2='$id2'";
        $dbobj->query($sql);
        
        return $dbobj->get_insert_id();
        
    }
    
    public static function select_query($sql, $dbobj) {
        $sql_start = "start transaction;";
        $dbobj->query($sql_start);
        
        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        
        $sql_end = "rollback;";
        $dbobj->query($sql_end);
        return $return;
    }

    public static function getCachedObjects(CacheKey $list_ck, $page = 0, $item_per_page = PRODUCT_NUM_PER_PAGE, $opt = array()) {
        $list_opt = $opt;
        $value_opt = $opt;
        if(isset($opt['list']) || isset($opt['value'])){
            $list_opt = isset($opt['list']) ? $opt['list'] : array();
            $value_opt = isset($opt['value']) ? $opt['value'] : array();
        }

        if($page > 0){
            $list_ck = $list_ck->limit($page, $item_per_page);
        } else {
            $limit = $list_ck->getLimitation();
            $page = isset($limit['page_num']) ? $limit['page_num'] : 1;
        }
        $object_keys = DAL::get($list_ck, $list_opt);
        $object_keys = is_array($object_keys) ? $object_keys : array();

        $entities = array();
        foreach($object_keys as $i => $key) {
            $m = BaseModel::findCachedOne($key, $value_opt);
            if(!empty($m)) { //ignore empty ones
                $entities[$i] = $m;
            }
        }

        $count = DAL::getListCount($list_ck, $list_opt);

        return array(
            'data' => $entities,
            'total_rows' => $count,
            'current_page' => $page
        );
    }
}
