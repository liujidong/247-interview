<?php

require_once('includes.php');

if(empty($argv[1])) {
    die('ERROR: php update_mass_payment_csv.php <csv file>');
}

$filename = $argv[1];
$data = file_get_contents($filename);
$lines = explode("\n", $data);

foreach($lines as $line) {
    $items = str_getcsv($line);
    
    if(!isset($items[3])) {
        die('ERROR: payment id is missing');
    }  
    
    list($text, $payment_id) = explode('-', $items[3]);

    if(empty($items[5])) {
        $status = PROCESSED;
    } else {
        $status = FAILED;
    }

    PaymentItemsMapper::updatePaymentsStatus($payment_id, $status, $account_dbobj);

}
