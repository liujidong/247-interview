<?php

require 'includes.php';

$change_logs = ChangeLogsMapper::getLogs($account_dbobj);

foreach($change_logs as $log) {

    $status = FAILED;

    Log::write(INFO, "now processing :".json_encode($log));
    
    $object_type = $log['object_type']; //'storetest_72-products'
    $object_id = $log['object_id']; // '20'

    $parts = explode('-',$object_type);
    $table = $parts[1];// products, pictures, categories, converted_pictures
    list($dbname, $store_id) = explode('_', $parts[0]);

    $data = array();
    $data['store_id'] = $store_id;
        
    $store_obj = new Store($account_dbobj, $store_id);
    $store_dbobj = DBObj::getStoreDBObj($store_obj->getHost(), $store_obj->getId());

    if(!$store_dbobj->is_db_existed()){
        Log::write(ERROR, "store db not exist".json_encode($log));

        $change_log_obj = new ChangeLog($account_dbobj, $log['id']);
        $change_log_obj->setStatus($status);
        $change_log_obj->save();
        continue;
    }

    if($table === 'products') {
        $data['product_id'] = $object_id;
    } else if($table === 'pictures') {
        $data['product_id'] = PicturesMapper::getProductId($store_dbobj, $object_id);
    } else if($table === 'categories') {
        $data['product_id'] = CategoriesMapper::getProdutId($store_dbobj, $object_id);
    } else if($table === 'converted_pictures') {
        $data['product_id'] = ConvertedPicturesMapper::getProductId($store_dbobj, $object_id);
    }

    if(!empty($data['store_id']) && !empty($data['product_id'])) {

        $status = PROCESSED;
        $job_18 = new Job($job_dbobj);

        $job_18->setType(UPDATE_SEARCH_PRODUCTS);
        $job_18->setData($data);
        $job_18->setStatus(CREATED);
        $job_18->save();

    } else {
        Log::write(INFO, "store or product id not exist".json_encode($log));
    }

    $change_log_obj = new ChangeLog($account_dbobj, $log['id']);
    $change_log_obj->setStatus($status);
    $change_log_obj->save();
}