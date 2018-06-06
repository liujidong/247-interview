<?php

$sendgrid_api_username = 'xxx';
$sendgrid_api_password = 'xxx';
$sendgrid_api_endpoint = 'https://sendgrid.com/api/mail.send.json';

$server_check_url = "http://shopinterest.co/test/servercheck";

$email_to = 'xxx@shopinterest.co';
$content ="";

function curl_post($url, $postfields=array(), $headers=array()) {

    // Set the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response =  curl_exec($ch);
    $curl_errno = curl_errno($ch);
    if($curl_errno > 0) {
        $http_status = '500';
    } else {
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }

    curl_close($ch);
    return array($http_status, $response);
}

$error = array();

list($code, $check_result) = curl_post($server_check_url);
if($code == 200 && $check_result) {
    $ret = json_decode($check_result, true);
    if(empty($ret)){
        $error[] = "server_check_api";
    } else {
        foreach($ret as $k => $v) {
            if($v != 'ok'){
                $error[] = $k;
            }
        }
    }
} else if($code != 200){
    $error[] = "server_check_api";
} else {
    $error[] = "check_script";
}

if(count($error)<1) die();

$content = implode("\n", array_map(function($v){return "<li>$v</li>";},$error));
$content = "Some errors occured:<br><ul> $content </ul>";

$postfields = array(
    'to' => $email_to,
    'toname' => 'Shopinerest Team',
    'subject' => 'Shopinerest.co Server Health Alert',
    'html' => $content,
    'from' => 'xxx@shopinerest.co',
    'fromname' => 'Shopinterest Tech Team',
    'replyto' => 'xxx@shopinerest.co',
    'api_user' => $sendgrid_api_username,
    'api_key' => $sendgrid_api_password,
);

list($code, $response) = curl_post($sendgrid_api_endpoint, $postfields);
if($response) {
    $response_array = json_decode($response, true);
    if($response_array['message'] === 'success') {
        //send ok
    } else if($response_array['message'] === 'error') {
        // send error
    } else {
        // unknow
    }
} else {
    // curl error
}
