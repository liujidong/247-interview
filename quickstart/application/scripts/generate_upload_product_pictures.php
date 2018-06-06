<?php
require_once('includes.php');

$store_infos = StoresMapper::getAllStoreInfo($account_dbobj);

foreach ($store_infos as $store_info) {
    
    $store_id = $store_info['id'];
    $store_host = $store_info['host'];
    //$store_status = $store_info['status'];
    echo "processing store : $store_id \n";
    
    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);
    
    if(!$store_dbobj->is_db_existed()) {
        continue;
    }    
        
    $products_ids = ProductsMapper::getAllProductIds($store_dbobj);
    
    foreach ($products_ids as $product_id) {
        
        echo "store id : $store_id, product id : $product_id \n";
        
        $data = array(
            'store_id'=> $store_id, 
            'product_id' => $product_id,
        );
        
        $job14 = new Job($job_dbobj);
        $job14->setType(UPLOAD_PRODUCT_PICTURES);
        $job14->setData($data);
        $job14->save();            
    }
}
