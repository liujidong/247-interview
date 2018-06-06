<?php

class FeaturedProductsMapper {

    public static function getCachedObjectList($params) {

        if(php_sapi_name() !== 'cli') {
            return array();
        }
        global $dbconfig;
        
        $cacheKey = $params['_cachekey'];
        $condition_sql = str_replace('and store_status = 2 ', '', $cacheKey->conditionSQL());
        $parameters = $cacheKey->getParameters();
        $featured = $parameters['featured'];
        $global_category_id = $parameters['global_category_id'];
        $products = array();
        $dbname = $dbconfig->store->name;
        
        // loop through all active stores
        $store_keys = DAL::get(lck_stores(ACTIVATED));
        foreach($store_keys as $store_key) {
            $store_cachekey = CacheKey::q($store_key);
            $store_parameters = $store_cachekey->getParameters();
            $store_id = $store_parameters['id'];
            
            // we should not create the store featured products cache list 
            // because it is not used anywhere
            // so we will still do the sql query to get the featured products
            $store_dbname = $dbname.'_'.$store_id;
            $store_dbobj = DBObj::getStoreDBObjById($store_id);
            $products = array_merge($products, 
                    ProductsMapper::getActiveFeaturedProductsCacheKeys($featured, $global_category_id, $store_dbobj));
            
        }
        return $products;
    }

}