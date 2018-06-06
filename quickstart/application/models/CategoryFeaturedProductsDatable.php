<?php

class CategoryFeaturedProductsCache extends FeaturedProductsCache {

    public static $ck_for_read = null;        

    public static function read($data=array(), $ck=NULL) {

        $current_page = default2Int($data['page'], 1);
        $items_per_page = default2Int($data['items_per_page'], DATATABLE_ITEMS_PER_PAGE);

        $response = array();

        $return = BaseMapper::getCachedObjects(self::$ck_for_read, $current_page, $items_per_page);

        $response['current_page'] = $return['current_page'];
        $response['items_per_page'] = $items_per_page;
        $response['rows'] = $return['data'];
        $response['status'] = 'success';
        return $response;
    }
}
CategoryFeaturedProductsCache::$ck = lck_category_featured_products('_');
CategoryFeaturedProductsCache::$ck_for_read = lck_admin_featured_products();
CategoryFeaturedProductsCache::$featured_type = CATEGORY_FEATURED;
