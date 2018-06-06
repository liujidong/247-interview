<?php

require_once('includes.php');

global $dbconfig;

$store_user = $dbconfig->store->user;
$store_password = $dbconfig->store->password;

$store_ids = StoresMapper::getAllStoreIds($account_dbobj);

foreach($store_ids as $store_id) {

    $store = new Store(Dbobj::getAccountDBObj());
    $store->findOne('id='.$store_id);
    $store_host = $store->getHost();
    $store_dbname = $dbconfig->store->name.'_'.$store_id;
        
    $alter_sql_file = "store.v21.".APPLICATION_ENV.".sql";
    executeSQLByHost($store_host, $store_user, $store_password, $store_dbname, $alter_sql_file);
}
