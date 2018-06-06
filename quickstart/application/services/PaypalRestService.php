<?php

use PayPal\Rest\ApiContext as PayPalApiContext;
use PayPal\Auth\OAuthTokenCredential as PaypalOAuthTokenCredential;
use PayPal\Api\Amount as PayPalAmount;
use PayPal\Api\Details as PaypalDetails;
use PayPal\Api\Item as PaypalItem;
use PayPal\Api\ItemList as PaypalItemList;
use PayPal\Api\CreditCard as PaypalCreditCard;
use PayPal\Api\CreditCardToken as PaypalCreditCardToken;
use PayPal\Api\Payer as PaypalPayer;
use PayPal\Api\Payment as PaypalPayment;
use PayPal\Api\FundingInstrument as PaypalFundingInstrument;
use PayPal\Api\Transaction as PaypalTransaction;
use PayPal\Api\RedirectUrls as PaypalRedirectUrls;
use PayPal\Api\ExecutePayment as PaypalExecutePayment;
use PayPal\Api\PaymentExecution as PaypalPaymentExecution;

use PayPal\Api\Amount;
use PayPal\Api\Refund;
use PayPal\Api\Sale;

class PaypalRestService extends BaseService {

    private static $apiContext = null;

    public static function getApiContext(){
        global $paypalconfig;
        //if(self::$apiContext != null){
        //    return self::$apiContext;
        //}
        $apiContext = new PaypalApiContext(new PaypalOAuthTokenCredential(
            $paypalconfig->api->rest->client_id, $paypalconfig->api->rest->secret));
        $apiContext->setConfig(
            array(
                'mode' => $paypalconfig->api->rest->mode,
                'http.ConnectionTimeOut' => $paypalconfig->api->rest->http->connectiontimeout,
                'log.LogEnabled' => (bool)($paypalconfig->api->rest->log->enable),
                'log.FileName' => $paypalconfig->api->rest->log->filename,
                'log.LogLevel' => $paypalconfig->api->rest->log->level,
            )
        );
        //self::$apiContext = $apiContext;
        return $apiContext;
    }

    public static function getPaypalCardByPaypalCardId($paypal_card_id){
        $apiContext = self::getApiContext();
        $ret = null;
        try{
            $ret = PaypalCreditCard::get($paypal_card_id, $apiContext);
        } catch(Exception $e){
            error_log("PAYPAL REST ERROR, getPaypalCardByPaypalCardId 0 :\n" . print_r($e, true));
            return null;
        }
        return $ret;
    }

    // Paypal Vault: https://developer.paypal.com/webapps/developer/docs/api/#vault
    // Store a credit card: https://developer.paypal.com/webapps/developer/docs/api/#store-a-credit-card
    // Delete a stored credit card: https://developer.paypal.com/webapps/developer/docs/api/#delete-a-stored-credit-card
    // Look up a stored credit card: https://developer.paypal.com/webapps/developer/docs/api/#look-up-a-stored-credit-card
    public static function saveCreditCard($params){
        $params['card_type'] = strtolower(CreditCardUtil::guessCreditCardType($params['card_number']));

        $apiContext = self::getApiContext();
        $card = new PaypalCreditCard();
        $card->setNumber($params['card_number']);
        $card->setExpire_month($params['exp_month']);
        $card->setExpire_year($params['exp_year']);
        $card->setFirst_name($params['billing_first_name']);
        $card->setLast_name($params['billing_last_name']);
        $card->setType($params['card_type']);
        $card->setPayer_Id($params['user_id']);
        try {
            $ret = $card->create($apiContext);
        } catch(Exception $e) {
            error_log("PAYPAL REST ERROR, saveCreditCard 0 :\n" . print_r($e, true));
            $ret = false;
        }

        return $ret;
    }

    public function get_paypal_card(){
        $params = $this->params;

        $account_dbobj = $params['account_dbobj'];

        $user_id = $params['user_id'];
        $card_number = $params['card_number'];
        $card_no_l4 = $account_dbobj->escape(substr($card_number, -4));
        $our_card = new CreditCard($account_dbobj);
        $our_card->findOne("user_id = $user_id and card_number = '$card_no_l4'");
        if($our_card->getId()>0){
            $this->status = 0;
            $this->response['is_new_card'] = false;
            $this->response['our_card'] = $our_card;
            $paypal_card = self::getPaypalCardByPaypalCardId($our_card->getPaypalCardId());
            if(!$our_card->getVerified()){
                self::do_verify_cerdit_card($our_card);
                if($our_card->getVerified()){
                    WalletsMapper::updateWalletAfterVerifiedCard($account_dbobj, $user_id);
                }
            }
            if(empty($paypal_card)){
                $this->status = 1;
            }
            $this->response['paypal_card'] = $paypal_card;
            return;
        }

        try{
            $ret = self::saveCreditCard($params);
        } catch(Exception $e){
            //dddd($e);
            error_log("PAYPAL REST ERROR, get_paypal_card 0 :\n" . print_r($e, true));
            $this->errnos[PAYPAL_VAULT_STORE_CARD_ERROR] = 1;
            $this->status = 1;
            return;
        }
        if(!$ret){
            $this->errnos[PAYPAL_VAULT_STORE_CARD_ERROR] = 1;
            $this->status = 1;
            return;
        }
        $our_card = new CreditCard($account_dbobj);
        $our_card->setCardNumber($card_no_l4);
        $our_card->setUserId($user_id);
        $our_card->setStatus(ACTIVATED);
        $our_card->setPaypalCardId($ret->getId());
        $our_card->setExpMonth($params['exp_month']);
        $our_card->setExpYear($params['exp_year']); // 4 bytes
        $our_card->setValidUntil(preg_replace('/[a-z]+/i', ' ', $ret->getValidUntil()));
        $our_card->save();

        self::do_verify_cerdit_card($our_card);
        if($our_card->getVerified()){
            WalletsMapper::updateWalletAfterVerifiedCard($account_dbobj, $user_id);
        }

        $this->status = 0;
        $this->response['is_new_card'] = true;
        $this->response['our_card'] = $our_card;
        $this->response['paypal_card'] = $ret;
    }


    public static function getTransactionsFromMyorderGroup($account_dbobj, $myorder_grp){
        $orders = MyorderGroupsMapper::getOrders($account_dbobj, $myorder_grp->getId());
        $payment_items = array();
        // items information

        $service = NativeCheckoutService::getInstance();
        $service->setMethod("myorder_summary");
        $params = array(
            'account_dbobj' => $account_dbobj,
            'order_group' => $myorder_grp,
        );
        $service->setParams($params);
        $service->call();

        $response = $service->getResponse();

        foreach($response['items_by_store'] as $store_id => $order_items){
            foreach($order_items as $oi){
                $subtotal = $oi['subtotal'] +  $oi['tax'];
                $item1 = new PaypalItem();
                $item1->setName(substr($oi['name'], 0, 120))
                      ->setCurrency($oi['currency'])
                      ->setQuantity($oi['product_quantity'])
                      ->setPrice(sprintf("%.2f", ((int)($subtotal/$oi['product_quantity']*100))/100.0));
                $payment_items[] = $item1;
            }
        }
        $itemList = new PaypalItemList();
        $itemList->setItems($payment_items);

        // ### Additional payment details
        $price_total = array_reduce(
            $payment_items,
            function($l, $r){return $l + $r->getPrice()*$r->getQuantity();}, 0);
        $shipping_total = (double)$myorder_grp->getShipping();
        $tax = 0;
        $details = new PaypalDetails();
        $details->setShipping(sprintf("%.2f", $shipping_total))
                ->setTax(sprintf("%.2f", $tax))
                ->setSubtotal(sprintf("%.2f", $price_total));

        // ### Amount
        $currency_symbol = count($payment_items)>0 ? $payment_items[0]->getCurrency() : "USD";
        $amount = new PaypalAmount();
        $amount->setCurrency($currency_symbol)
               ->setTotal(sprintf("%.2f", $price_total + $shipping_total + $tax))
               ->setDetails($details);

        // ### Transaction
        $transaction = new PaypalTransaction();
        $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription("Shopintoit");
        return array($transaction);
    }

    public function do_vault_payment(){
        $params = $this->params;

        $account_dbobj = $params['account_dbobj'];
        $myorder_grp = $params['order_group'];
        $user_id = $params['user_id'];

        $this->response['pay_method'] = 'creditcard';
        // get saved cerdit card
        if($myorder_grp->getPaymentMethod() != "creditcard"){
            $this->errnos[NC_UNSPPORTED_PAYMETHOD] = 1;
            $this->status =1;
            return;
        }
        //$out_card = new CreditCard($account_dbobj);
        //$out_card->findOne("id = " . $myorder_grp->getPaymentInfo());
        $creditCardToken = new PaypalCreditCardToken();
        $creditCardToken->setCreditCardId($myorder_grp->getPaymentInfo());
        $creditCardToken->setPayerId($user_id);

        $fi = new PaypalFundingInstrument();
        $fi->setCreditCardToken($creditCardToken);

        $payer = new PaypalPayer();
        $payer->setPaymentMethod("credit_card")
              ->setFundingInstruments(array($fi))
              ->setPayerInfo(array("payer_id" => $user_id));

        // ### transactions
        $transactions = self::getTransactionsFromMyorderGroup($account_dbobj, $myorder_grp);

        // ### Payment
        $payment = new PaypalPayment();
        $payment->setIntent("sale")
                ->setPayer($payer)
                ->setTransactions($transactions);

        // create payment
        try {
            $apiContext = self::getApiContext();
            $payment->create($apiContext);

            //Execute the payment
            //$execution = new PaypalPaymentExecution();
            //$execution->setPayerId($user_id);
            //$paymentId = $payment->getId();
            //$payment = PaypalPayment::get($paymentId, $apiContext);
            //$result = $payment->execute($execution, $apiContext);

            $myorder_grp->setPaymentInfo($myorder_grp->getPaymentInfo(). "," . $payment->getId());
            $myorder_grp->save();
            $this->status = 0;
        } catch (Exception $ex) {
            // fail
            error_log("PAYPAL REST ERROR, do_vault_payment 0 :\n" . print_r($ex, true));
            $this->errnos[PAYPAL_VAULT_PAY_ERROR] = 1;
            $this->status = 1;
            return;
            //echo "Exception: " . $ex->getMessage() . PHP_EOL;
            //var_dump($ex->getData());
        }
    }

    public function do_paypal_confirm(){
        $params = $this->params;

        $account_dbobj = $params['account_dbobj'];
        $myorder_grp = $params['order_group'];
        $user_id = $params['user_id'];
        $return_url = $params['return_url'];
        $cancel_url = $params['cancel_url'];

        $this->response['pay_method'] = 'paypal';

        if($myorder_grp->getPaymentMethod() != "paypal"){
            $this->errnos[NC_UNSPPORTED_PAYMETHOD] = 1;
            $this->status =1;
            $this->response['error_msg'] = "wrong pay method";
            return;
        }
        // ### Payer
        $payer = new PaypalPayer();
        $payer->setPaymentMethod("paypal");

        // ### transactions
        $transactions = self::getTransactionsFromMyorderGroup($account_dbobj, $myorder_grp);

        // ### Redirect urls
        $redirectUrls = new PaypalRedirectUrls();
        $redirectUrls->setReturnUrl($return_url)
                     ->setCancelUrl($cancel_url);

        // ### Payment
        $payment = new PaypalPayment();
        $payment->setIntent("sale")
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions($transactions);

        // ### Create Payment
        try {
            $apiContext = self::getApiContext();
            $payment->create($apiContext);
            $this->status = 0;
        } catch (Exception $ex) {
            // fail
            // echo "Exception: " . $ex->getMessage() . PHP_EOL;
            error_log("PAYPAL REST ERROR, do_paypal_confirm 0 :\n" . print_r($ex, true));
            $this->errnos[PAYPAL_VAULT_PAY_ERROR] = 1;
            $this->status = 1;
            $this->response['error_msg'] = "Exception: " . $ex->getMessage();
            //var_dump($ex->getData());
            return;
        }

        // ### Get redirect url
        foreach($payment->getLinks() as $link) {
            if($link->getRel() == 'approval_url') {
                $redirectUrl = $link->getHref();
                $this->response['redirect_url'] = $redirectUrl;
                break;
            }
        }
        // ### Redirect buyer to PayPal website
        // Save the payment id so that you can 'complete' the payment
        // once the buyer approves the payment and is redirected
        // back to your website.

        //$_SESSION['paymentId'] = $payment->getId();
        //if(isset($redirectUrl)) {
        //    header("Location: $redirectUrl");
        //    exit;
        //}
        $this->response['payment_id'] = $payment->getId();
        $myorder_grp->setPaymentInfo($this->response['payment_id']);
        $myorder_grp->save();
        $this->status = 0;
    }

    public function do_paypal_payment(){
        try{
            $params = $this->params;

            $account_dbobj = $params['account_dbobj'];
            $myorder_grp = $params['order_group'];
            $user_id = $params['user_id'];

            $paymentId = $myorder_grp->getPaymentInfo();
            $apiContext = self::getApiContext();
            $payment = PaypalPayment::get($paymentId, $apiContext);

            // PaymentExecution object includes information necessary
            // to execute a PayPal account payment.
            // The payer_id is added to the request query parameters
            // when the user is redirected from paypal back to your site
            $execution = new PaypalPaymentExecution();
            $execution->setPayerId($_REQUEST['payer_id']);

            //Execute the payment
            // (See bootstrap.php for more on `ApiContext`)
            $result = $payment->execute($execution, $apiContext);

            $payment = PaypalPayment::get($paymentId, $apiContext);
            $email = $payment->getPayer()->getPayerInfo()->getEmail();
            $myorder_grp->setPaymentInfo($email . "," . $myorder_grp->getPaymentInfo());
            $myorder_grp->save();
        }catch(Exception $e){
            error_log("PAYPAL REST ERROR, do_paypal_payment 0 :\n" . print_r($e, true));
            $this->errnos[PAYPAL_VAULT_STORE_CARD_ERROR] = 1;
            $this->status = 1;
        }
        //var_dump($result);
        $this->status = 0;
    }

    public static function do_verify_cerdit_card($card){
        $user_id = $card->getUserId();

        $creditCardToken = new PaypalCreditCardToken();
        $creditCardToken->setCreditCardId($card->getPaypalCardId());
        $creditCardToken->setPayerId($user_id);

        $fi = new PaypalFundingInstrument();
        $fi->setCreditCardToken($creditCardToken);

        $payer = new PaypalPayer();
        $payer->setPaymentMethod("credit_card")
              ->setFundingInstruments(array($fi))
              ->setPayerInfo(array("payer_id" => $user_id));

        // ### transactions
        $payment_items = array();
        // items information
        $item1 = new PaypalItem();
        $item1->setName("Shopintoit CreditCard Verification")
              ->setCurrency("USD")
              ->setQuantity(1)
              ->setPrice(1.00);
        $payment_items[] = $item1;
        $itemList = new PaypalItemList();
        $itemList->setItems($payment_items);

        // ### Additional payment details
        $details = new PaypalDetails();
        $details->setShipping(0)
                ->setTax(0)
                ->setSubtotal(1.00);

        // ### Amount
        $amount = new PaypalAmount();
        $amount->setCurrency('USD')
               ->setTotal(1.00)
               ->setDetails($details);

        // ### Transaction
        $transaction = new PaypalTransaction();
        $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription("Shopintoit");
        $transactions = array($transaction);

        // ### Payment
        $payment = new PaypalPayment();
        $payment->setIntent("sale")
                ->setPayer($payer)
                ->setTransactions($transactions);

        // create payment
        try {
            $apiContext = self::getApiContext();
            $payment->create($apiContext);
            $t=$payment->getTransactions();
            $t=$t[0];
            $rr=$t->getRelatedResources();
            $rr=$rr[0];
            $sale = $rr->getSale();
            $amt = new PaypalAmount();
            $amt->setCurrency('USD')
                ->setTotal('1.00');

            $refund = new Refund();
            $refund->setAmount($amt);
            $apiContext = self::getApiContext();
            $sale->refund($refund, $apiContext);
            $card->setVerified(1);
            $card->save();
            return true;
        } catch (Exception $ex) {
            error_log("PAYPAL REST ERROR, do_verify_cerdit_card 0 :\n" . print_r($ex, true));
            return false;
        }
    }

    public function test(){
        $apiContext = self::getApiContext();
        $card = new PaypalCreditCard();
        $card->setNumber('4417119669820331');
        $card->setExpire_month('11');
        $card->setExpire_year('2018');
        $card->setFirst_name('Joe');
        $card->setLast_name('Shopper');
        $card->setType('visa');
        $card->setPayer_Id('123456789');
        try{
            $ret = $card->create($apiContext);
        } catch(Exception $e){
            dddd($e);
        }
        dddd($ret);
    }
}
