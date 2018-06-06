<?php

class StoreGlobalCategoriesMapper {

    public static function getCachedObject($params, $store_dbobj) {
        global $dbconfig;
        $cat_id = $params['id'];
        $ret = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name . ".global_category?id=$cat_id"));
        if(empty($ret)) return NULL;
        $deleted = DELETED;
        $sql = "select count(1) as product_cnt
                from products p
                where
                p.name!='' and p.price>0 and p.quantity>0 and p.status != $deleted
                and p.global_category_id = $cat_id";

        if($res = $store_dbobj->query($sql)) {
            if($record = $store_dbobj->fetch_assoc($res)) {
                $ret['product_cnt'] = $record['product_cnt'];
            }
        }
        return $ret;
    }
        
    public static function getCachedObjectList($params, $store_dbobj){
        $ck = $params['_cachekey'];
        $dbname = $ck->getDBName();
        $sql = "select distinct(global_category_id) as cat_id
                from products p
                where
                p.name!='' and p.price>0 and p.quantity>0 and p.status != 127 and p.global_category_id !=0";
        $ret = array();
        if($res = $store_dbobj->query($sql, $store_dbobj)) {
            while($record = $store_dbobj->fetch_assoc($res)) {
                $cid = $record['cat_id'];
                if(empty($cid))continue;
                $key = $dbname.".store_global_category?id=".$cid;
                $ret[$key] = $cid;
            }
        }
        return $ret;
    }

}