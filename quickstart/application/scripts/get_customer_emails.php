<?php

require_once('includes.php');

$store_infos = StoresMapper::getAllStoreIdsAndStatus($account_dbobj);

$count = 0;

foreach ($store_infos as $store_info) {
    ddd('start from:'.$store_info['id']);
    
    $store_id = $store_info['id'];
    $store_dbobj = DBObj::getStoreDBObjById($store_id);
    
    $emails = CustEmailMapper::getAllCustomerEmails($store_dbobj);
    ddd($emails);
    $count += sizeof($emails);
    
    ddd('total emails:'.$count);
}
