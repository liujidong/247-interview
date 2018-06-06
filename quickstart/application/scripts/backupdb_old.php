<?php

require_once('includes.php');

global $dbconfig, $shopinterest_config;

$account_host = $dbconfig->account->host;
$account_user = $dbconfig->account->user;
$account_password = $dbconfig->account->password;
$account_dbname = $dbconfig->account->name;

$store_name = $dbconfig->store->name;
$store_user = $dbconfig->store->user;
$store_password = $dbconfig->store->password;

$dbbackupdir = $shopinterest_config->dbbackup->dir;

if(mkdir2($dbbackupdir)) {
    
    //back up accountdb
    backupdb($account_host, $account_user, $account_password, $account_dbname, $dbbackupdir);
    //back up storedb
    $store_hosts = StoresMapper::getStoreHosts($account_dbobj);
    foreach ($store_hosts as $store_host) {
        $file_name = $store_name.'_'.$store_host;
        backupdb($store_host, $store_user, $store_password, $file_name, $dbbackupdir);        
    }

}








