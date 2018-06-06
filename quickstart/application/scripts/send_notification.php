<?php

require_once('includes.php');

global $shopinterest_config;

$merchants = MerchantsMapper::getMerchants($account_dbobj);

$email_service = new EmailService();
$email_service->setMethod('create_job');
foreach($merchants as $i=>$merchant) {
    //
    //if($merchant['username'] !== 'xxx@yahoo.com') continue;
    //
    $email_service->setParams(array(
        'to' => $merchant['username'],
        'from' => $shopinterest_config->support->email,
        'type' => NOTIFICATION,
        'data' => array(),
        'job_dbobj' => $job_dbobj
    ));
    $email_service->call();
}





