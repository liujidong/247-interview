<?php

require_once('includes.php');

$store_infos = StoresMapper::getAllStoreInfo($account_dbobj);

foreach ($store_infos as $store_info) {
    
    $store_id = $store_info['id'];
    $store_host = $store_info['host'];
    
    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);

    if(!StoresMapper::check_table_exist($store_dbobj, 'shipping_options')) {
        ddd("*******store id: $store_id version 19 is missing");
    } else {
        //ddd("store id: $store_id version: $version");
    }
}