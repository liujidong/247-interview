<?php

require_once('includes.php');

if(!isset($argv[1])) {
    $merchants = MerchantsMapper::getMerchants($account_dbobj);
    foreach($merchants as $merchant) {
        $merchant_id = $merchant['id'];
        ddd('start migrating merchant '.$merchant_id);
        migrate_merchant($merchant_id, $account_dbobj);
        ddd('merchant '.$merchant_id.' migrated');
    }
} else {
    $merchant_id = intval($argv[1]);
    ddd('start migrating merchant '.$merchant_id);
    migrate_merchant($merchant_id, $account_dbobj);
    ddd('merchant '.$merchant_id.' migrated');
}





