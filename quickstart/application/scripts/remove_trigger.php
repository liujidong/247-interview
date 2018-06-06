<?php

require_once 'includes.php';

$store_ids = StoresMapper::getAllStoreIds($account_dbobj);
foreach($store_ids as $store_id) {
    remove_trigger($store_id);
}


function remove_trigger($store_id) {
    global $dbconfig;

    $store_user = $dbconfig->store->user;
    $store_password = $dbconfig->store->password;
    $store_dbname = $dbconfig->store->name.'_'.$store_id;

    $store = new Store(Dbobj::getAccountDBObj());
    $store->findOne('id='.$store_id);
    $store_host = $store->getHost();

    $sql_str =
        "drop trigger if exists `products_after_update`;-
drop trigger if exists `products_after_insert`;-
drop trigger if exists `pictures_after_update`;-
drop trigger if exists `pictures_after_insert`;-
drop trigger if exists `pictures_after_delete`;-
drop trigger if exists `categories_after_update`;-
drop trigger if exists `categories_after_insert`;-
drop trigger if exists `categories_after_delete`;-
drop trigger if exists `converted_pictures_after_update`;-
drop trigger if exists `converted_pictures_after_insert`;-
drop trigger if exists `converted_pictures_after_delete`;";

    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);

    $sql_array = explode('-', $sql_str);
    foreach($sql_array as $sql) {
        $store_dbobj->query($sql);
    }

    echo "DONE: store $store_id\n";
}