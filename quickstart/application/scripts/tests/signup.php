<?php
require_once __DIR__.'/../../scripts/includes.php';

$status = 'FAILURE';
$messages = array();

$password = generate_password();
$rand_number = rand(100, 1000000);
$username = 'xxx+'.$rand_number.'@gmail.com';

$messages[] = "test username: $username test password: $password";

$url = getURL('/api/register');

$messages[] = "api url: ".$url;

if(APPLICATION_ENV === 'production') {
    $response = json_decode(curl_post($url, array(
        'username' => $username,
        'password' => $password,
        'first_name' => 'liang',
        'last_name' => 'huang',
        'open_store' => 1
    )), true);
} else {
    $response = json_decode(curl_post($url, array(
        'username' => $username,
        'password' => $password,
        'first_name' => 'liang',
        'last_name' => 'huang',
        'open_store' => 1
    ), array(), array('username' => 'staging', 'password' => 'xxx')), true);
}



if($response['status'] === 'success') {
    $status = 'SUCCESS';
    $messages[] = json_encode($response);
    $messages[] = 'Signup Test Succeeds';
} else {
    $messages[] = json_encode($response);
    $messages[] = 'Signup Test Fails';
}

ddd($status, $messages);
      