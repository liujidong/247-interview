<?php

require_once('includes.php');

$store_infos = StoresMapper::getAllStoreInfo($account_dbobj);

foreach ($store_infos as $store_info) {
    
    $store_id = $store_info['id'];
    $store_host = $store_info['host'];
    
    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);
    
    $version = StoresMapper::get_version($store_dbobj);
    if($version != 20) {
        ddd("*******store id: $store_id version: $version");
    } else {
        //ddd("store id: $store_id version: $version");
    }
}
