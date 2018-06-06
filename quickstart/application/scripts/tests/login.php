<?php
require_once __DIR__.'/../../scripts/includes.php';

$status = 'FAILURE';
$messages = array();

global $shopinterest_config;
$password = $shopinterest_config->superpassword;
$username = 'xxx@shopinterest.co';

$url = getURL('/api/login');

if(APPLICATION_ENV === 'production') {
    $response = json_decode(curl_post($url, array(
        'username' => $username,
        'password' => $password
    )), true);
} else {
    $response = json_decode(curl_post($url, array(
        'username' => $username,
        'password' => $password
    ), array(), array('username' => 'staging', 'password' => 'xxx')), true);
}



if($response['status'] === 'success') {
    $status = 'SUCCESS';
    $messages[] = 'Login Test Succeeds';
} else {
    $messages[] = 'Login Test Fails';
}

ddd($status, $messages);
      