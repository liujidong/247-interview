<?php

class Sub {
    
    public static $registered_event = array();
    
    public static function subscribe($channel, $fn = null) {
        global $redis;
        
        if(is_callable($fn)) {
            self::register($channel, $fn);
        }
        $job = $redis->rPop($channel);

        if($job === false) {
            return false;
        }
        
        $job = json_decode($job, true);
        $type = $job['type'];
        $data = $job['data'];

        if(in_array($type, array_keys(self::$registered_event))) {
            call_user_func(self::$registered_event[$type], $data);
        }
        return true;
    }

    public static function register($event, $fn) {
        if(is_string($event) && is_callable($fn)) {
            self::$registered_event[$event] = $fn;
        }
    }
    
}
Sub::$registered_event = array(
    UPDATE_PRODUCT_STATUS => function($msg) {

        $product_key = $msg['id'];
        $product_ck = CacheKey::q($msg['id']);
        $dbname = $product_ck->getDBName();

        $list_ck = CacheKey::q($dbname.'.products')->_and('status=active');
        $active_product_test = CacheKey::q('products?pictures!=')
            ->_and(
                CacheKey::c("name!=&price>0&quantity>0&&global_category_id!=0")
            );
        
        DAL::delete($product_ck);
        $new_one = DAL::get($product_ck);

        $op = $active_product_test->test($new_one) ? ADD_KList : DEL_KList;
        
        if($op === DEL_KList) {
            DAL::deleteFromList($list_ck, array($product_key));
        } else if ($op === ADD_KList){
            DAL::addToList($list_ck, array($product_key => 1));
        }
    }
);
