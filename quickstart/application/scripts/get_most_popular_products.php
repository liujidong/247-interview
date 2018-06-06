<?php

if(isset($argv[1]) && $argv[1] === 'test') {
    $GLOBALS['test'] = 1;
}

require_once('includes.php');

// update search_products table, set visters = 0
$account_dbobj->query("update search_products set page_views = 0 where page_views != 0");
Log::write(INFO, "update search_products set page_views = 0");

$filters = "ga:pagePath=~/[0-9a-zA-Z-]+\.(shopinterest|shopintoit)\.(com?)/products/item\?id=\d+";

$service = new GoogleService();
$service->setMethod('get_analytics_data');
$service->setParams(array(
    'day_from' => getNdaysago(30),
    'day_to' => getNdaysago(0),
    'opt_params' => array(
        'filters' => $filters,
        'sort' => '-ga:pageviews'
    )    
));
$service->call();
$data_rows = $service->getResponse();

foreach ($data_rows as $data_row) {

    $page_path = $data_row[0];
    $page_views = $data_row[2];

    $pattern = "#/([0-9a-zA-Z-]+)\.(shopinterest|shopintoit)\.com?/products/item\?id=(\d+)#";
    if(preg_match($pattern, $page_path, $matche)) {
        
        $store_subdomain = $matche[1];
        $product_id = $matche[3];

        $search_product_obj = new SearchProduct($account_dbobj);    
        $search_product_obj->findOne("store_subdomain = '$store_subdomain' and product_id = $product_id");   

        if($search_product_id = $search_product_obj->getId()) {
            // update search product record
            $search_product_obj->setPageViews($page_views);
            $search_product_obj->save();
            Log::write(INFO, "Set search_products id $search_product_id : store_id : {$search_product_obj->getStoreId()} product_id $product_id has $page_views page_views");
        }                
    }
}

