<?php

require_once('includes.php');

$payments = PaymentItemsMapper::getPayments($account_dbobj);

$filename = 'masspayments-'.get_current_datetime_filename().'.csv';
$fh = fopen($filename, 'w');

foreach($payments as $payment) {
    $fields = array();
    // create a payment record
    $payment_obj = new Payment($account_dbobj);
    $payment_obj->setSender($payment['sender']);
    $payment_obj->setReceiver($payment['receiver']);
    $payment_obj->setAmt($payment['amt']);
    $payment_obj->save();
    
    // update the payment item id and status to processing
    $payment_item_ids = explode(',', $payment['payment_item_ids']);
    foreach($payment_item_ids as $payment_item_id) {
        $payment_item = new PaymentItem($account_dbobj);
        $payment_item->findOne('id='.$payment_item_id);
        $payment_item->setPaymentId($payment_obj->getId());
        $payment_item->setStatus(PROCESSING);
        $payment_item->save();
    }
    
    $fields = array(
        $payment['email'], 
        $payment['amt'], 
        $payment['currency_code'], 
        'payment-'.$payment_obj->getId(), 
        'Thank you for your business.'
    );

    fputcsv($fh, $fields);    

}

fclose($fh);










