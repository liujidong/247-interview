<?php
require_once('includes.php');

$store_infos=StoresMapper::getAllStoreInfo($account_dbobj);

/* 
// debug code
$store = new Store($account_dbobj);
$store->findOne('id=18');
$store_infos = array(
    0 => array(
        'id' => $store->getId(),
        'host' => $store->getHost(),
        'status' => $store->getStatus()
    )
);
*/

foreach ($store_infos as $store_info) {
    $job_type = PRODUCT_SEARCH;
    $data = array('id'=>$store_info['id'], 'host'=>$store_info['host'], 'status' => $store_info['status']);
    $job13 = new Job($job_dbobj);
    $job13_id=$job13->getId();
    $job13->setType($job_type);
    $job13->setData($data);
    $job13->setHash1();
    $job13->save();
}
