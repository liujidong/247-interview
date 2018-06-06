<?php

require 'includes.php';

$merchant_info = MerchantsMapper::getMerchantStoreInfo($account_dbobj);

foreach ($merchant_info as $merchant) {

    $to_email = $merchant['username'];
    $store_url = $merchant['subdomain'].getSiteDomain();
    
    // test data
    //$to_email = 'wyx@mailinator.com';
    //$store_url = 'kikicat2101311.shopinterest.co';
    
    $day_from = getNdaysago();
    $day_to = getNdaysago(0);
    $filters = "ga:pagePath=@$store_url";

    $service = new GoogleService();
    $service->setMethod('get_analytics_data');
    $service->setParams(array(
        'day_from' => $day_from,
        'day_to' => $day_to,
        'opt_params' => array(
            'filters' => $filters
        )
    ));
    $service->call();

    $data_rows = $service->getResponse();
    $data_rows_fmt = array();
    foreach($data_rows as $row){
        $n = array();
        $n['c0_0'] = "http:/". $row[0];
        $n['c0_1'] = trim($row[0], '/');
        $n['c1'] = $row[1];
        $n['c2'] = $row[2];
        $n['c3'] = sprintf("%.2f", round($row[3], 2));
        $n['c4'] = sprintf("%.2f", round($row[4], 2));
        $data_rows_fmt[] = $n;
    }

    $service = new EmailService();
    $service->setMethod('create_job');
    $service->setParams(array(
        'to' => $to_email,
        'from' => 'xxx@shopinterest.co',
        'type' => MERCHANT_SITE_ANALYTICS_WEEKLY_REPORT,
        'data' => array(
            'site_url' => getURL(),
            'empty' => empty($data_rows),
            'rows' => $data_rows_fmt,
        ),
        'job_dbobj' => $job_dbobj
    ));
    $service->call();  

}

