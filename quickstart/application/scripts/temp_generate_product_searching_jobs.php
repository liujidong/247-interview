<?php
require_once('includes.php');

$store_ids = StoresMapper::getMissingStoreInfo($account_dbobj);

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

foreach ($store_ids as $store_id) {
    echo "store id: $store_id\n";
    $store = new Store($account_dbobj, $store_id);
    $store_host = $store->getHost();
    $store_status = $store->getStatus();
    
    $job_type = PRODUCT_SEARCH;
    $data = array('id'=>$store_id, 'host'=>$store_host, 'status' => $store_status);
    $job13 = new Job($job_dbobj);
    $job13_id=$job13->getId();
    $job13->setType($job_type);
    $job13->setData($data);
    $job13->setHash1();
    $job13->save();
}
