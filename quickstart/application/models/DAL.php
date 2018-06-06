<?php
/*
 * usage:
 * DAL is mainly used for reading the data, especially on the public pages.
 * For CUD (Create, Update and Delete), we still need to rely on the Model & Mappers
 * to operate them on MySQL.
 *
 * When an object is created in MySQL,
 *    no KV needs to be created, KList needs to add the key of the object.
 * When an object is updated in MySQL,
 *    the KV needs to be deleted, KList staby untouched.
 * When an object is deleted,
 *     the KV needs to be deleted, KList needs to delete the key of the object.
 *
 *
 *
 * Entity Mapper Class always has the higher priority to return the data
 *
 * convention for MySQL mappers:
 *
 * ObjectsMapper::getCachedObject if for getting a single object
 * ObjectsMapper::getCachedObjectList if for getting a list of object ids
 *
 * If the Entity Mapper Class does not exist, then we check the database table
 *
 *
 *
 */


class DAL {

    public static function get(CacheKey $key, $opt = array()) {
        if($key->getType() === 'value') {
            return self::getKV($key, $opt);
        } else if ($key->getType() === 'list'){
            return self::getKList($key, $opt);
        }
        return false;
    }

    private static function getKV(CacheKey $key, $opt = array()) {
        $force = isset($opt['force']) ? (bool)($opt['force']) : false;
        $ignore_cache = isset($opt['ignore_cache']) ? (bool)($opt['ignore_cache']) : false;

        // try to get the value from redis -- hash
        global $redis;
        $key_string = $key->cacheKey();
        if(!$force && !$ignore_cache && !empty2($value = $redis->hGetAll($key_string))) {
            return $value;
        }

        // cache miss, get it through mapper or from mysql
        $table_info = get_table_info();
        $entity = $key->getEntity();
        $table_name = to_plural($entity);
        $dbname = $key->getDBName();
        $dbobj = self::getDBObj($dbname);

        // Check if the Mapper::getCachedObject exist
        $entity_mapper_class = get_entity_mapper_class($entity);
        if(method_exists($entity_mapper_class, 'getCachedObject')) {
            $value = $entity_mapper_class::getCachedObject($key->getParameters(), $dbobj);
            if(!$ignore_cache && !empty($value)){
                $redis->hMSet($key->cacheKey(), $value);
            }
            return $value;
        } else if(!is_null($dbobj) && !empty($table_info[$table_name])) {
            // get the data from the db table
            $condition = $key->conditionSQL();
            $entity_record = BaseMapper::findOne($condition, $table_name, $dbobj);
            if(!$ignore_cache && $entity_record['id'] !== 0) {
                $redis->hMSet($key->cacheKey(), $entity_record);
            }
            if(empty($entity_record) || empty($entity_record['id'])) return false;
            return $entity_record;
        }
        return false;
    }

    private static function getKList(CacheKey $key, $opt = array()) {
        $force = isset($opt['force']) ? (bool)($opt['force']) : false;
        $ignore_cache = isset($opt['ignore_cache']) ? (bool)($opt['ignore_cache']) : false;

        // try to get the value from redis -- list or sorted set
        global $redis;

        if($force){
            $redis->delete($key->cacheKey());
        }

        $order_info = $key->getOrderInfo();
        $orderby = $order_info['orderby'];
        $use_list = true;
        if(count($orderby) === 1) {
            $use_list = false; // use sorted set then
        }

        $page_info = $key->getLimitation();
        $page_num = isset($page_info['page_num'])? (int)$page_info['page_num'] : 0;
        $page_size = isset($page_info['page_size'])? (int)$page_info['page_size'] : 0;
        $start = ($page_num - 1)*$page_size;
        $end = $start + $page_size - 1;

        if($use_list) {
            if(!$force && !$ignore_cache &&!empty2($value = $redis->lRange($key->cacheKey(), $start, $end))) {
                return $value;
            }
        } else {
            if($order_info['order_direction'] === 'DESC') {
                if(!$force && !$ignore_cache && !empty2($value = $redis->zRevRange($key->cacheKey(), $start, $end))) {
                    return $value;
                }
            } else {
                if(!$force && !$ignore_cache && !empty2($value = $redis->zRange($key->cacheKey(), $start, $end))) {
                    return $value;
                }
            }
        }

        // cache miss, get it through mapper
        $entity = $key->getEntity();
        $dbname = $key->getDBName();

        $dbobj = self::getDBObj($dbname);

        // Check if the Mapper::getCachedObjects exist
        $entity_mapper_class = get_entity_mapper_class($entity);
        if(method_exists($entity_mapper_class, 'getCachedObjectList')) {
            $records = $entity_mapper_class::getCachedObjectList($key->getParameters(), $dbobj);
            // for list, $records is array($key0, $key1, $key2, ...)
            // for sorted set, $records is array(key0=>score0, key1=>score1,...),
            if($ignore_cache){
                if($use_list) {
                    return $records;
                } else {
                    return array_keys($records);
                }
            }
            foreach($records as $i => $s) {
                if($use_list) {
                    $redis->rPush($key->cacheKey(), $s);
                } else {
                    $redis->zAdd($key->cacheKey(), $s, $i);
                }
            }
            if(!empty($records)) {
                return self::getKList($key, array());
            }
        }
        return false;
    }

    public static function getListCount(CacheKey $key, $opt = array()) {
        $force = isset($opt['force']) ? (bool)($opt['force']) : false;
        $ignore_cache = isset($opt['ignore_cache']) ? (bool)($opt['ignore_cache']) : false;
        
        global $redis;
        $order_info = $key->getOrderInfo();
        $orderby = $order_info['orderby'];
        $use_list = true;
        if(count($orderby) === 1) {
            $use_list = false; // use sorted set then
        }
        
        if($ignore_cache) {

            $entity = $key->getEntity();
            $dbname = $key->getDBName();

            $dbobj = self::getDBObj($dbname);

            $entity_mapper_class = get_entity_mapper_class($entity);
            if(method_exists($entity_mapper_class, 'getCachedObjectListCount')) {
                return $entity_mapper_class::getCachedObjectListCount($key->getParameters(), $dbobj);
            }
            return 0;
        }
        if($force){
            self::getKList($key, array('force' => true));
        }

        $ret = 0;
        if($use_list) {
            $ret = $redis->lLen($key->CacheKey());
        } else {
            $ret = $redis->zCard($key->CacheKey());
        }
        if($force) return $ret;
        if($ret < 1){
            $ret = self::getListCount($key, array('force' => true));
        }
        return $ret;
    }

    public static function delete(CacheKey $key) {
        global $redis;
        $redis->del($key->cacheKey());
    }

    
    public static function addToList(CacheKey $key, $ids) {
        global $redis;

        $order_info = $key->getOrderInfo();
        $orderby = $order_info['orderby'];
        $use_list = true;
        if(count($orderby) === 1) {
            $use_list = false; // use sorted set then
        }

        // for list, $records is array($key0, $key1, $key2, ...)
        // for sorted set, $records is array(key0=>score0, key1=>score1,...),
        foreach($ids as $i => $s) {
            if($use_list) {
                $redis->rPush($key->cacheKey(), $s);
            } else {
                $redis->zAdd($key->cacheKey(), $s, $i);
            }
        }
    }


    public static function deleteFromList(CacheKey $key, $ids) {
        global $redis;

        $order_info = $key->getOrderInfo();
        $orderby = $order_info['orderby'];
        $use_list = true;
        if(count($orderby) === 1) {
            $use_list = false; // use sorted set then
        }

        foreach($ids as $i=>$k) {
            if($use_list) {
                $redis->lRem($key->cacheKey(), $k);
            } else {
                $redis->zRem($key->cacheKey(), $k);
            }

        }

    }

    public static function getDBObj($dbname) {

        global $dbconfig;
        $store_dbname = $dbconfig->store->name;
        $store_dbname_reg = '/^'.$store_dbname.'_\d+$/';

        $dbobj = null;
        if($dbname === $dbconfig->account->name) {
            $dbobj = DBObj::getAccountDBObj();
        } else if($dbname === $dbconfig->job->name) {
            $dbobj = DBObj::getJobDBObj();
        } else if (preg_match($store_dbname_reg, $dbname)){
            $dbobj = DBObj::getStoreDBObjByDBName($dbname);
        }
        return $dbobj;
    }

    public static function s(CacheKey $entity_key, $old_data = NULL) {
        global $maintain_list, $dbconfig;

        $cachekey_type = $entity_key->getEntity();
        $pre_defined_rule = array();
        $dbname_map = array();

        $dbname = $entity_key->getDBName();
        $store_dbname = $dbconfig->store->name;
        if(!empty($dbname) && startsWith($dbname, $store_dbname)) { // check if is an store db name
            $dbname_map[$store_dbname] = $dbname;
        }

        if(in_array($cachekey_type, array_keys($maintain_list))) {
            $pre_defined_rule = $maintain_list[$cachekey_type];
        } else { // no maintained list for this model type
            DAL::delete($entity_key);
            return;
        }
        if($old_data === NULL){
            // DO NOT PASS A NULL HERE:
            //   if you pass a NULL, and at the same time, the
            //   $old_value is not cached in redis, the below line will
            //   get the data in database, and use it as the $old_data,
            //   in this situation, the $old_data and the $new_data may be
            //   the same (latest) state of the model
            // IF YOU ARE VERY SURE THAT THE OLD DATA IS CACHED IN REDIS
            // NOW, YOU CAN PASS ME A NULL ^_^, BUT I AM VERY SURE THAT YOU WON'T
            // SURE ABOUT THAT.
            // ddd("[[[GOT A NULL AS OLD DATA]]]");
            $old_data = DAL::get($entity_key);
        }
        DAL::delete($entity_key);
        $new_data = DAL::get($entity_key);
        foreach($pre_defined_rule as $match_key){
            $use_set = FALSE;
            $old_score = 0;
            $new_score = 0;
            $order_attr = $match_key->getOrderInfo();
            if(count($order_attr['orderby']) == 1){
                $use_set = TRUE;
                $old_score = self::get_order_score($cachekey_type, $order_attr['orderby'][0], $old_data);
                $new_score = self::get_order_score($cachekey_type, $order_attr['orderby'][0], $new_data);
            }

            $old_lists = $match_key->match($old_data, $dbname_map);
            $new_lists = $match_key->match($new_data, $dbname_map);
            // compare $old_lists(A) and $new_lists(B)
            // del from old lists(A-B), add to new lists(B-A)
            $lists_need_del = array_diff($old_lists, $new_lists);
            foreach($lists_need_del as $i => $list_key) {
                DAL::deleteFromList(CacheKey::q($list_key), array($entity_key->cacheKey()));
            }

            $lists_need_add = array_diff($new_lists, $old_lists);
            foreach($lists_need_add as $i => $list_key) {
                if($use_set) {
                    DAL::addToList(CacheKey::q($list_key), array($entity_key->cacheKey() => $new_score));
                } else {
                    DAL::addToList(CacheKey::q($list_key), array($entity_key->cacheKey()));
                }
            }

            // sort (A^B) if needed
            if($use_set && ($old_score != $new_score)) {
                $lists_need_resort = array_intersect($old_lists, $new_lists);
                foreach($lists_need_resort as $i => $list_key){
                    DAL::addToList(CacheKey::q($list_key), array($entity_key->cacheKey() => $new_score));
                }
            }
        }
    }
    
    private static function get_order_score($model_type, $attr, &$model) {
        if ($attr === 'updated') {
            return strtotime2(isset($model[$attr]) ? $model[$attr] : 'now');
        }
        if (isset($model[$attr]))
            return $model[$attr];
        return 0;
    }

}
