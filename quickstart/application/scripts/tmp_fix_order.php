<?php

require_once('includes.php');

$myorder_grp = new MyorderGroup($account_dbobj, 49);

NativeCheckoutService::send_receipt_email(
    $account_dbobj, $job_dbobj, $myorder_grp
);
// mark order and cart completed
MyorderGroupsMapper::setMyorderGroupCompleted($account_dbobj, $myorder_grp->getId());
CartsMapper::setCartCompleted($account_dbobj, $myorder_grp->getCartId());
