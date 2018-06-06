<?php

class TestController extends StaticController {

    public function init() {
        /* Initialize action controller here */
        $this->view->site_version = 1;

    }

    //$store_id_$product_id:$user_id:quantity
    public function indexAction() {
        $this->view->global_categories=array();
        dddd(CreditCardUtil::guessCreditCardType('5201 0880 1151 3260'));
        $service = PaypalRestService::getInstance();
        $service->setMethod("test");
        $service->call();
    }
    
    public function echoAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        echo "echo echo...";
    }
    
    public function servercheckAction() {
        global $dbconfig, $redis_config;
        $result = array('httpd'=>'ok');

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        // check database
        if(is_resource($conn = @mysql_connect($dbconfig->account->host,
        $dbconfig->account->user, $dbconfig->account->password)) &&
        !@mysql_select_db($dbconfig->account->dbname, $conn)) {
            $result['db_account'] = 'ok';
        } else {
            $result['db_account'] = 'error';
        }

        if(is_resource($conn = @mysql_connect($dbconfig->store->host,
        $dbconfig->store->user, $dbconfig->store->password)) &&
        !@mysql_select_db($dbconfig->store->dbname . '_1', $conn)) {
            $result['db_store'] = 'ok';
        } else {
            $result['db_store'] = 'error';
        }

        if(is_resource($conn = @mysql_connect($dbconfig->job->host,
        $dbconfig->job->user, $dbconfig->job->password)) &&
        !@mysql_select_db($dbconfig->job->dbname, $conn)) {
            $result['db_job'] = 'ok';
        } else {
            $result['db_job'] = 'error';
        }

        // check redis
        try{
            $redis = new Redis();
            $redis->connect($redis_config->server->host, $redis_config->server->port);
            $result['redis'] = 'ok';
        }catch(Exception $e){
            $result['redis'] = 'error';
        }

        echo json_encode($result);
    }

    // actionType=PAY, CREATE, PAY_PRIMARY
    // currencyCode=USD
    // cancelUrl=http://www.staging.shopinterest.co:8083/test/chainedpayments?cancel=true
    // returnUrl=http://www.staging.shopinterest.co:8083/test/chainedpayments?return=true
    // requestEnvelope.errorLanguage=en_US
    // receiverList.receiver(0).amount=100
    // receiverList.receiver(0).email=mercha_1342385059_biz@gmail.com
    // receiverList.receiver(0).primary=true
    // receiverList.receiver(1).amount=10
    // receiverList.receiver(1).email=sell2_1337294610_biz@gmail.com
    // receiverList.receiver(1).primary=false
    // receiverList.receiver(2).amount=1
    // receiverList.receiver(2).email=shopin_1342385307_biz@gmail.com
    // receiverList.receiver(2).primary=false
    public function chainedpaymentsAction() {
        if (!empty($_REQUEST['submit'])) {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(TRUE);
            $_REQUEST['amt'] = 100;
            $total = $_REQUEST['amt'];
            $paypal_seller = 'mercha_1342385059_biz@gmail.com';
            $paypal_sales_associate = 'sell2_1337294610_biz@gmail.com';
            $paypal_shopinterest = 'shopin_1342385307_biz@gmail.com';


            $service = new PaypalService();
            $service->setMethod('adaptive_pay');
            $service->setParams(array(
                'apiparams' => array(
                    'actionType' => 'CREATE',
                    'currencyCode' => 'USD',
                    'cancelUrl' => 'http://www.staging.shopinterest.co:9083/test/closelightbox',
                    'returnUrl' => 'http://www.staging.shopinterest.co:9083/test/closelightbox',
                    'requestEnvelope.errorLanguage=en_US' => 'en_US',
                    'receiverList.receiver(0).amount' => $total,
                    'receiverList.receiver(0).email' => $paypal_seller,
                    'receiverList.receiver(0).primary' => 'true',
                    'receiverList.receiver(1).amount' => $total * 0.1,
                    'receiverList.receiver(1).email' => $paypal_sales_associate,
                    'receiverList.receiver(1).primary' => 'false',
                    'receiverList.receiver(2).amount' => 1,
                    'receiverList.receiver(2).email' => $paypal_shopinterest,
                    'receiverList.receiver(2).primary' => 'false',
                )
            ));
            $service->call();

            // response:
//            Array
//            (
//                [responseEnvelope_timestamp] => 2012-11-29T17:04:44.833-08:00
//                [responseEnvelope_ack] => Success
//                [responseEnvelope_correlationId] => 01581286ac81e
//                [responseEnvelope_build] => 4110101
//                [payKey] => AP-78G17867PE038164C
//                [paymentExecStatus] => CREATED
//            )

            parse_str(urldecode($service->getResponse()), $response);
            echo $response['payKey'];
            //$this->view->payKey = $response['payKey'];
            //echo $response['payKey'];
            //ddd($response);
//            if($response['responseEnvelope_ack'] === 'Success' && $response['paymentExecStatus'] === 'CREATED') {
//                global $paypalconfig;
//                //$paypal_login_url = $paypalconfig->api->login_url.'?cmd='.$paypalconfig->api->adaptivepayments->cmd.'&paykey='.$response['payKey'];
//                //ddd($paypal_login_url);
//                //redirect($paypal_login_url);
//                echo $response['payKey'];
//            } else {
//                dddd("Error happens in ap pay request");
//            }
        }
    }

    public function sessionAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        ddd($_SESSION);
    }

    public function closelightboxAction() {

    }

    public function emaillightboxAction() {

    }

    public function purchaseAction() {

    }

    public function affiliatestoreAction() {

    }

    public function admininterfaceAction() {

    }

    public function paypalAction() {

        require __DIR__ . '/../../library/paypal/rest/bootstrap.php';



        // ### CreditCard
        // A resource representing a credit card that can be
        // used to fund a payment.
        $card = new PayPal\Api\CreditCard();
        $card->setType("visa")
                ->setNumber("4417119669820331")
                ->setExpireMonth("11")
                ->setExpireYear("2019")
                ->setCvv2("012")
                ->setFirstName("Joe")
                ->setLastName("Shopper");

        // ### FundingInstrument
        // A resource representing a Payer's funding instrument.
        // For direct credit card payments, set the CreditCard
        // field on this object.
        $fi = new PayPal\Api\FundingInstrument();
        $fi->setCreditCard($card);

        // ### Payer
        // A resource representing a Payer that funds a payment
        // For direct credit card payments, set payment method
        // to 'credit_card' and add an array of funding instruments.
        $payer = new PayPal\Api\Payer();
        $payer->setPaymentMethod("credit_card")
                ->setFundingInstruments(array($fi));

        $currency_code = 'USD';

        // ### Itemized information
        // (Optional) Lets you specify item wise
        // information
        $item1 = new PayPal\Api\Item();
        $item1->setName('Ground Coffee 40 oz')
                ->setCurrency($currency_code)
                ->setQuantity(1)
                ->setPrice('7.50');
        $item2 = new PayPal\Api\Item();
        $item2->setName('Granola bars')
                ->setCurrency($currency_code)
                ->setQuantity(5)
                ->setPrice('2.00');

        $itemList = new PayPal\Api\ItemList();
        $itemList->setItems(array($item1, $item2));

        // ### Additional payment details
        // Use this optional field to set additional
        // payment information such as tax, shipping
        // charges etc.
        $details = new PayPal\Api\Details();
        $details->setShipping('1.20')
                ->setTax('1.30')
                ->setSubtotal('17.50');

        // ### Amount
        // Lets you specify a payment amount.
        // You can also specify additional details
        // such as shipping, tax.
        $amount = new PayPal\Api\Amount();
        $amount->setCurrency($currency_code)
                ->setTotal("20.00")
                ->setDetails($details);

        // ### Transaction
        // A transaction defines the contract of a
        // payment - what is the payment for and who
        // is fulfilling it.
        $transaction = new PayPal\Api\Transaction();
        $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription("Payment description");

        // ### Payment
        // A Payment Resource; create one using
        // the above types and intent set to sale 'sale'
        $payment = new PayPal\Api\Payment();
        $payment->setIntent("sale")
                ->setPayer($payer)
                ->setTransactions(array($transaction));

        // ### Create Payment
        // Create a payment by calling the payment->create() method
        // with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
        // The return object contains the state.
        try {
            $payment->create($apiContext);
        } catch (PayPal\Exception\PPConnectionException $ex) {
            echo "Exception: " . $ex->getMessage() . PHP_EOL;
            var_dump($ex->getData());
            exit(1);
        }

        $this->view->payment = $payment;

    }

    public function f5Action() {
        $this->_helper->layout->disableLayout();
    }

    public function firebaseSigninAction() {
        $this->_helper->layout->disableLayout();
        Redis_Session::start();

        if($this->is_user()) {
            redirect('/test/firebase-home');
        }
    }

    public function firebaseAuthAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        Redis_Session::start();
        $user_session = new Redis_Session_Namespace('user');
        $return = array('auth' => false);

        if(!empty($_REQUEST['firebaseAuthToken'])) {
            if(is_authenticated($_REQUEST['firebaseAuthToken'])) {
                $return['auth'] = true;
                $user_session->user_id = 101;
            }
        }

        echo json_encode($return);

    }

    private function is_user() {
        $user_session = new Redis_Session_Namespace('user');
        if($user_session->user_id) {
            return true;
        } else {
            return false;
        }
    }

    public function firebaseHomeAction() {
        $this->_helper->layout->disableLayout();
        Redis_Session::start();

        if(!$this->is_user()) {
            redirect('/test/firebase-signin');
        }



    }

    public function firebaseDetailsAction() {
        $this->_helper->layout->disableLayout();
    }

    public function firebaseCleanCookieAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        Redis_Session::destroy();
    }

    public function callbackAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        echo 1;
    }

    public function setCookieAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        setcookie('testcookie', 'averylongtoken');
        echo "this is the content\n";
    }

    public function redisSessionAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        Redis_Session::start();
        $user_session = new Redis_Session_Namespace('user');
        $shopper_session = new Redis_Session_Namespace('shopper');
        $user_session->user_id = 5;
        $user_session->username = 'xxx@yahoo.com';
        $shopper_session->shopper_id = 5;
        dddd(Redis_Session::get_session_array());
    }

    public function redisSessionDestroyAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        Redis_Session::destroy();
    }

    public function revealAction() {
        $this->_helper->layout->disableLayout();
    }
    
    // where to create a test subscription button:
    // https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_subscr-intro-outside
    // test merchant account: promer_1342550510_biz@gmail.com
    // test buyer account: xxx@shopinterest.co
    // ipn url set in the buyer account: http://www.staging.shopinterest.co/test/ipn
    // post back url to verify the ipn: https://www.sandbox.paypal.com/cgi-bin/webscr
    // reference doc: https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNIntro/
    // reference doc: https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/subscribe_buttons/#id08ADFG0C0P2
    // reference doc: https://developer.paypal.com/docs/classic/products/payment-data-transfer/
    // Identity Token:pIOyzuk0aBK3eUgn2Y1baEvGRiBuwWjHk2C2W-T08IuMVAXEHfmwQy-o8fe
    // pdt sample codes: https://github.com/paypal/pdt-code-samples/blob/master/paypal_pdt.php
    // pdt integration doc: https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/paymentdatatransfer/
    // subscription variables: https://www.paypal.com/cgi-bin/webscr?cmd=p/acc/ipn-subscriptions-outside
    // html form variables for recurring payment: https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/#id08A6HI00JQU
    public function subscribeAction() {
        $this->_helper->layout->disableLayout();
//        error_log(json_encode($_REQUEST));
//        if(!empty($_REQUEST)) {
//            
//            $url = "https://www.sandbox.paypal.com/cgi-bin/webscr?"; // Change to www.sandbox.paypal.com to test against sandbox
//
//
//            // read the post from PayPal system and add 'cmd'
//            $req = 'cmd=_notify-synch';
//
//            $tx_token = $_GET['tx'];
//            $auth_token = "pIOyzuk0aBK3eUgn2Y1baEvGRiBuwWjHk2C2W-T08IuMVAXEHfmwQy-o8fe";
//            $req .= "&tx=$tx_token&at=$auth_token";
//            error_log($url.$req);
//            error_log(curl_post($url.$req));
//        }
        
        
    }
    
//    public function pdtAction() {
//        $this->_helper->layout->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
//        if (empty($_REQUEST['tx'])) {
//            return;
//        }
//
//        $url = "https://www.sandbox.paypal.com/cgi-bin/webscr?";
//        $tx_token = $_REQUEST['tx'];
//        $auth_token = "pIOyzuk0aBK3eUgn2Y1baEvGRiBuwWjHk2C2W-T08IuMVAXEHfmwQy-o8fe";
//        $req = "cmd=_notify-synch&tx=$tx_token&at=$auth_token";
//        $response = curl_post($url . $req);
//        // parse the data
//        $lines = explode("\n", $response);
//        if (strcmp($lines[0], "SUCCESS") != 0) {
//            return;
//        }
//        
//        $fields = array();
//        for ($i = 1; $i < count($lines); $i++) {
//            list($key, $val) = explode("=", $lines[$i]);
//            $fields[urldecode($key)] = urldecode($val);
//        }
//        
//        
//    }
    public function ipnAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        header("HTTP/1.1 200 OK");
        
        $query = http_build_query($_REQUEST);
        $query = 'cmd=_notify-validate&'.$query;
        
        error_log(json_encode($_REQUEST));
        error_log('query:'.$query);
        error_log('***response:'.file_get_contents('https://www.sandbox.paypal.com/cgi-bin/webscr?'.$query));
    }
    
    // reference: http://cloudinary.com/documentation/php_integration#getting_started_guide
    public function cloudinaryAction() {
        $this->_helper->layout->disableLayout();
        
    }
    
    // input: url
    // output: url, url_45, url_70, url_192, url_236, url_550, url_736
    public function cloudinaryUploadAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        if(empty($_REQUEST['url'])) {
            die('failure');
        }
        
        $url = $_REQUEST['url'];
        
        global $cloudinary_config;

        \Cloudinary::config(array(
            "cloud_name" => $cloudinary_config->api->cloud_name,
            "api_key" => $cloudinary_config->api->key,
            "api_secret" => $cloudinary_config->api->secret
        ));
        
        // public id
        $public_id = 'ts/s101/'.uniqid();
        $format = 'jpg';
        $options = array(
            'public_id' => $public_id,
            'format' => $format
        );
        
        echo json_encode(\Cloudinary\Uploader::upload($url, $options));
    }
}
