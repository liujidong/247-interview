<?php

// input: start store id, end store id (optional)

$start_store_id = 0;
$end_store_id = PHP_INT_MAX;

if($argc === 2) {
    $start_store_id = intval($argv[1]);
} else if($argc === 3) {
    $start_store_id = intval($argv[1]);
    $end_store_id = intval($argv[2]);
}


require_once('includes.php');

// dump table info to redis
get_table_info(true);

$stores = StoresMapper::getAllStoreInfo($account_dbobj, $start_store_id, $end_store_id);

$schema_version = intval2(getSchemaVersion('store'));

foreach($stores as $store) {
    updateStoreDBSchema($store['id'], $schema_version);
};



