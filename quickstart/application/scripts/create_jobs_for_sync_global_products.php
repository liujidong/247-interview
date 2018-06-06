<?php
require_once('includes.php');

$stores = StoresMapper::getAllStoreInfo($account_dbobj, 0, PHP_INT_MAX, ACTIVATED);

foreach ($stores as $store) {
    $job_type = SYNC_GLOBAL_PRODUCTS;
    $data = array('store_id'=>$store['id']);
    $job = new Job($job_dbobj);
    $job->setType($job_type);
    $job->setData($data);
    $job->save();
}
