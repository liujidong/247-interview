<?php

require_once('includes.php');

$payments = PaymentsMapper::getPayments($account_dbobj);
foreach($payments as $payment) {
    $payment_obj = new Payment($account_dbobj);
    $payment_obj->findOne('id='.$payment['id']);
    $payment_account = new PaymentAccount($account_dbobj);
    $payment_account->findOne('paypal_account_id='.$payment['sender']);
    $save = false;
    if($payment_account->getId() !== 0) {
        $payment_obj->setSender($payment_account->getId());
        $save = true;
    }
    $payment_account->findOne('paypal_account_id='.$payment['receiver']);
    if($payment_account->getId() !== 0) {
        $payment_obj->setReceiver($payment_account->getId());
        $save = true;
    }
    if($save) {
        $payment_obj->save();
        ddd('payment migrated '.$payment_obj->save());
    }
}

$payment_items = PaymentItemsMapper::getPaymentItems($account_dbobj);
foreach($payment_items as $payment_item) {
    $payment_item_obj = new PaymentItem($account_dbobj);
    $payment_item_obj->findOne('id='.$payment_item['id']);
    $payment_account = new PaymentAccount($account_dbobj);
    $payment_account->findOne('paypal_account_id='.$payment['sender']);
    $save = false;
    if($payment_account->getId() !== 0) {
        $payment_item_obj->setSender($payment_account->getId());
        $save = true;
    }
    $payment_account->findOne('paypal_account_id='.$payment_item['receiver']);
    if($payment_account->getId() !== 0) {
        $payment_item_obj->setReceiver($payment_account->getId());
        $save = true;
    }
    if($save) {
        $payment_item_obj->save();
        ddd('payment item migrated '.$payment_item_obj->save());
    }
}






