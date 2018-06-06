<?php
require_once __DIR__.'/../../scripts/includes.php';

$status = 'FAILURE';
$messages = array();

$browser = PinterestBrowser::getInstance("xxx@shopinterest.co", "xxx");
$u = $browser->login();

if($u === "qingliangtest") {
    $status = 'SUCCESS';
    $messages[] = 'Pinterest Login Test Succeeds';
} else {
    $messages[] = 'Pinterest Login Test Fails';
}

ddd($status, $messages);
