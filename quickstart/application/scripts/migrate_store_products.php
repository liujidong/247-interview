<?php

require_once('includes.php');

//$featured_product = array(
// 'status', 0 2 127
// 'store_id',
// 'product_id',
// 'score',
//);
$featured_products = SearchProductsMapper::getMigrateStoreProductsData($account_dbobj);

foreach ($featured_products as $featured_product) {
    
    $status = $featured_product['status'];
    $store_id = $featured_product['store_id'];
    $product_id = $featured_product['product_id'];
    $score = $featured_product['score'];
    
    if($status == DELETED) {
        continue;
    }
    
    $search_product = new SearchProduct($account_dbobj);
    $search_product->findOne("store_id = $store_id and product_id = $product_id");
    $search_product_id = $search_product->getId();
    if(empty($search_product_id)) {
        echo "Error: cant find in search_product table, store_id : $store_id and  product_id : $product_id \n";
        //need insert this info to search_product table
    } else {
        $search_product->setFeatured(true);
        $search_product->setScore($score);
        $search_product->save();
        echo "Success: update search_product table, store_id : $store_id and  product_id : $product_id \n";        
    }
}






