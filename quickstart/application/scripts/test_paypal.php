<?php

require_once('includes.php');

$service = PaypalRestService::getInstance();
$service::saveCreditCard(array(
    'card_number' => '48158',
    'exp_month' => '07',
    'exp_year' => '2016',
    'billing_first_name' => 'Liang',
    'billing_last_name' => 'Huang',
    'user_id' => 1888
));

    

