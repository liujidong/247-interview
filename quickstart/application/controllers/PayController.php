<?php

class PayController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
    }
//Array
//(
//    [TOKEN] => EC-2CS64912LL101582L
//    [CHECKOUTSTATUS] => PaymentActionNotInitiated
//    [TIMESTAMP] => 2012-08-13T20:18:31Z
//    [CORRELATIONID] => ac3635d0c5a05
//    [ACK] => Success
//    [VERSION] => 65.0
//    [BUILD] => 3435050
//    [EMAIL] => custom_1342461735_per@gmail.com
//    [PAYERID] => U59FQ6DKJG9NG
//    [PAYERSTATUS] => verified
//    [FIRSTNAME] => customer
//    [LASTNAME] => random
//    [COUNTRYCODE] => US
//    [SHIPTONAME] => Liang Huang
//    [SHIPTOSTREET] => 40732 wolcott dr
//    [SHIPTOCITY] => fremont
//    [SHIPTOSTATE] => CA
//    [SHIPTOZIP] => 94538
//    [SHIPTOCOUNTRYCODE] => US
//    [SHIPTOCOUNTRYNAME] => United States
//    [ADDRESSSTATUS] => Unconfirmed
//    [CURRENCYCODE] => USD
//    [AMT] => 56.42
//    [SHIPPINGAMT] => 12.00
//    [HANDLINGAMT] => 0.00
//    [TAXAMT] => 4.42
//    [DESC] => Your Order in liangdev
//    [INSURANCEAMT] => 0.00
//    [SHIPDISCAMT] => 0.00
//    [PAYMENTREQUEST_0_CURRENCYCODE] => USD
//    [PAYMENTREQUEST_0_AMT] => 56.42
//    [PAYMENTREQUEST_0_SHIPPINGAMT] => 12.00
//    [PAYMENTREQUEST_0_HANDLINGAMT] => 0.00
//    [PAYMENTREQUEST_0_TAXAMT] => 4.42
//    [PAYMENTREQUEST_0_DESC] => Your Order in liangdev
//    [PAYMENTREQUEST_0_INSURANCEAMT] => 0.00
//    [PAYMENTREQUEST_0_SHIPDISCAMT] => 0.00
//    [PAYMENTREQUEST_0_NOTETEXT] => i need a large size pants
//    [PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID] => mercha_1342385059_biz@gmail.com
//    [PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED] => false
//    [PAYMENTREQUEST_0_PAYMENTREQUESTID] => order-27
//    [PAYMENTREQUEST_0_SHIPTONAME] => Liang Huang
//    [PAYMENTREQUEST_0_SHIPTOSTREET] => 40732 wolcott dr
//    [PAYMENTREQUEST_0_SHIPTOCITY] => fremont
//    [PAYMENTREQUEST_0_SHIPTOSTATE] => CA
//    [PAYMENTREQUEST_0_SHIPTOZIP] => 94538
//    [PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE] => US
//    [PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME] => United States
//    [PAYMENTREQUEST_0_ADDRESSSTATUS] => Unconfirmed
//    [PAYMENTREQUEST_1_CURRENCYCODE] => USD
//    [PAYMENTREQUEST_1_AMT] => 1.00
//    [PAYMENTREQUEST_1_SHIPPINGAMT] => 0.00
//    [PAYMENTREQUEST_1_HANDLINGAMT] => 0.00
//    [PAYMENTREQUEST_1_TAXAMT] => 0.00
//    [PAYMENTREQUEST_1_DESC] => service fee
//    [PAYMENTREQUEST_1_INSURANCEAMT] => 0.00
//    [PAYMENTREQUEST_1_SHIPDISCAMT] => 0.00
//    [PAYMENTREQUEST_1_NOTETEXT] => this is not fair, why i need to pay the service fee
//    [PAYMENTREQUEST_1_SELLERPAYPALACCOUNTID] => shopin_1342385307_biz@gmail.com
//    [PAYMENTREQUEST_1_INSURANCEOPTIONOFFERED] => false
//    [PAYMENTREQUEST_1_PAYMENTREQUESTID] => order-0
//    [PAYMENTREQUESTINFO_0_PAYMENTREQUESTID] => order-27
//    [PAYMENTREQUESTINFO_0_ERRORCODE] => 0
//    [PAYMENTREQUESTINFO_1_PAYMENTREQUESTID] => order-0
//    [PAYMENTREQUESTINFO_1_ERRORCODE] => 0
//)
//Array
//(
//    [token] => EC-9NM3210444114994U
//    [PayerID] => MDWTZ2SEK9Z8S
//)
//credit card response
//Array
//(
//    [TOKEN] => EC-9NM3210444114994U
//    [CHECKOUTSTATUS] => PaymentActionNotInitiated
//    [TIMESTAMP] => 2012-08-13T20:37:17Z
//    [CORRELATIONID] => 97ef20a66433e
//    [ACK] => Success
//    [VERSION] => 65.0
//    [BUILD] => 3332236
//    [EMAIL] => liangyahooo@yahoo.com
//    [PAYERID] => MDWTZ2SEK9Z8S
//    [PAYERSTATUS] => unverified
//    [FIRSTNAME] => Liang
//    [LASTNAME] => Huang
//    [COUNTRYCODE] => US
//    [SHIPTONAME] => Liang Huang
//    [SHIPTOSTREET] => 40732 Wolcott Dr
//    [SHIPTOCITY] => Fremont
//    [SHIPTOSTATE] => CA
//    [SHIPTOZIP] => 94538
//    [SHIPTOCOUNTRYCODE] => US
//    [SHIPTOCOUNTRYNAME] => United States
//    [ADDRESSSTATUS] => Confirmed
//    [CURRENCYCODE] => USD
//    [AMT] => 56.42
//    [SHIPPINGAMT] => 12.00
//    [HANDLINGAMT] => 0.00
//    [TAXAMT] => 4.42
//    [DESC] => Your Order in liangdev
//    [INSURANCEAMT] => 0.00
//    [SHIPDISCAMT] => 0.00
//    [PAYMENTREQUEST_0_CURRENCYCODE] => USD
//    [PAYMENTREQUEST_0_AMT] => 56.42
//    [PAYMENTREQUEST_0_SHIPPINGAMT] => 12.00
//    [PAYMENTREQUEST_0_HANDLINGAMT] => 0.00
//    [PAYMENTREQUEST_0_TAXAMT] => 4.42
//    [PAYMENTREQUEST_0_DESC] => Your Order in liangdev
//    [PAYMENTREQUEST_0_INSURANCEAMT] => 0.00
//    [PAYMENTREQUEST_0_SHIPDISCAMT] => 0.00
//    [PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID] => mercha_1342385059_biz@gmail.com
//    [PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED] => false
//    [PAYMENTREQUEST_0_PAYMENTREQUESTID] => order-30
//    [PAYMENTREQUEST_0_SHIPTONAME] => Liang Huang
//    [PAYMENTREQUEST_0_SHIPTOSTREET] => 40732 Wolcott Dr
//    [PAYMENTREQUEST_0_SHIPTOCITY] => Fremont
//    [PAYMENTREQUEST_0_SHIPTOSTATE] => CA
//    [PAYMENTREQUEST_0_SHIPTOZIP] => 94538
//    [PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE] => US
//    [PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME] => United States
//    [PAYMENTREQUEST_1_CURRENCYCODE] => USD
//    [PAYMENTREQUEST_1_AMT] => 1.00
//    [PAYMENTREQUEST_1_SHIPPINGAMT] => 0.00
//    [PAYMENTREQUEST_1_HANDLINGAMT] => 0.00
//    [PAYMENTREQUEST_1_TAXAMT] => 0.00
//    [PAYMENTREQUEST_1_DESC] => service fee
//    [PAYMENTREQUEST_1_INSURANCEAMT] => 0.00
//    [PAYMENTREQUEST_1_SHIPDISCAMT] => 0.00
//    [PAYMENTREQUEST_1_SELLERPAYPALACCOUNTID] => shopin_1342385307_biz@gmail.com
//    [PAYMENTREQUEST_1_INSURANCEOPTIONOFFERED] => false
//    [PAYMENTREQUEST_1_PAYMENTREQUESTID] => order-0
//    [PAYMENTREQUESTINFO_0_PAYMENTREQUESTID] => order-30
//    [PAYMENTREQUESTINFO_0_ERRORCODE] => 0
//    [PAYMENTREQUESTINFO_1_PAYMENTREQUESTID] => order-0
//    [PAYMENTREQUESTINFO_1_ERRORCODE] => 0
//)
    
    
    //Array ( [token] => EC-5XE058319H269841U [PayerID] => CFVJDEWTXQ83L ) 
    public function indexAction() {
        
        global $paypalconfig;
        
        $order = $this->shopper_session->order;
        $store_id = $this->shopper_session->store_id;
        
        if(empty($order) || empty($store_id) || empty($_REQUEST['token']) || 
            empty($_REQUEST['PayerID']) || $order->paypal->token !== $_REQUEST['token']) {
            redirect('http://'.$_SERVER['HTTP_HOST']);
        }
       
        $account_dbobj = $this->account_dbobj;
        $store_dbobj = $this->store_dbobj;
        $token = $_REQUEST['token'];
        $payer_id = $_REQUEST['PayerID'];
        $this->shopper_session->order->paypal->payer_id = $payer_id;

        $service = PaypalService::getInstance();
        $service->setMethod('get_customer_details');
        $service->setParams(array(
            'token' => $token
        ));
        $service->call();
        if($service->getStatus() === 0) {
            // customer
            $response = $service->getResponse();
            
            // the order id and service order id need to match the results from the get_customer_results
            if($token === $response['TOKEN'] && 
                getOrderPaymentRequestId($store_id,$order->order_id) === $response['PAYMENTREQUESTINFO_0_PAYMENTREQUESTID'] &&
                getServiceOrderPaymentRequestId($order->service_order_id) === $response['PAYMENTREQUESTINFO_1_PAYMENTREQUESTID']) {
                
                // create the shopper
                $shopper = new Shopper($account_dbobj);
                $shopper->save();

                // create the paypal account for the shopper
                $paypal_account = new PaypalAccount($account_dbobj);
                $paypal_account->findOne("username='".$account_dbobj->escape($response['EMAIL'])."'");
                $paypal_account->setUsername($response['EMAIL']);
                $paypal_account->setFirstName($response['FIRSTNAME']);
                $paypal_account->setLastName($response['LASTNAME']);
                $paypal_account->setPayerId($response['PAYERID']);
                $paypal_account->setCountryCode($response['COUNTRYCODE']);
                $paypal_account->setPayerStatus($response['PAYERSTATUS']);
                $paypal_account->save();
                $this->shopper_session->order->paypal->username = $paypal_account->getUsername();
                $this->shopper_session->order->paypal->first_name = $paypal_account->getFirstName();
                $this->shopper_session->order->paypal->last_name = $paypal_account->getLastName();

                // associate the shopper and the paypal account
                BaseMapper::saveAssociation($shopper, $paypal_account, $account_dbobj);

                // associate the store and the shopper
                $store = new Store($account_dbobj);
                $store->setId($store_id);
                BaseMapper::saveAssociation($store, $shopper, $account_dbobj);

                // create address
                $address = new Address($account_dbobj);
                $address->setAddr1($response['SHIPTOSTREET']);
                $address->setCity($response['SHIPTOCITY']);
                $address->setState($response['SHIPTOSTATE']);
                $address->setCountry($response['SHIPTOCOUNTRYCODE']);
                $address->setZip($response['SHIPTOZIP']);
                $address->setPaypalAddressStatus($response['ADDRESSSTATUS']);
                $address->save();
                $this->shopper_session->order->address = $address;

                // flll the note, to_name, to_address_id, currency_code to the order
                $order_obj = new Order($store_dbobj);
                $order_obj->findOne('id='.$order->order_id);
                $order_obj->setStatus(PROCESSING);
                $order_obj->setNote(empty($response['PAYMENTREQUEST_0_NOTETEXT'])?'':$response['PAYMENTREQUEST_0_NOTETEXT']);
                $order_obj->setToName($response['SHIPTONAME']);
                $order_obj->setToAddressId($address->getId());
                $order_obj->setCurrencyCode($response['CURRENCYCODE']);
                $order_obj->save();
                $this->shopper_session->order->note = $order_obj->getNote();
                $this->shopper_session->order->to_name = $order_obj->getToName();
                
                
                // fill the note to the service order
                $service_order = new ServiceOrder($account_dbobj);
                $service_order->findOne('id='.$order->service_order_id);
                $service_order->setStatus(PROCESSING);
                $service_order->save();

                $this->view->order = $order_obj;
                $this->view->service_order = $service_order;
                $this->view->address = $address;
                $this->view->paypal_account = $paypal_account;

            }




        } else {
            // if cant get the details of customer, then redirect to 
            // store home page
            redirect('http://'.$_SERVER['HTTP_HOST']);
        }
    }
    
    public function confirmAction() {
        
        $order = $this->shopper_session->order;
        $store_id = $this->shopper_session->store_id;
        
        if(empty($order) || empty($store_id) || empty($order->paypal->token) || 
            empty($order->paypal->payer_id)) {
            redirect('http://'.$_SERVER['HTTP_HOST']);
        }
        
        $account_dbobj = $this->account_dbobj;
        $store_dbobj = $this->store_dbobj;
        
        if($_REQUEST['submit']) {
            $service = PaypalService::getInstance();
            $service->setMethod('make_payment');
            $payform = $order->paypal->payform;
            $fields = array(
                'TOKEN' => $order->paypal->token,
                'PAYERID' => $order->paypal->payer_id
            );
            $fields = array_merge($fields, $payform);
            $service->setParams($fields);
            $service->call();
            $response = $service->getResponse();

            if($service->getStatus() === 0) {
                $this->view->order_status = 'success';
                
                // update order status to processed
                $order_obj = new Order($store_dbobj);
                $order_obj->findOne('id='.$order->order_id);
                $order_obj->setStatus(PROCESSED);
                $order_obj->save();
                // update service order status to processed
                $service_order = new ServiceOrder($account_dbobj);
                $service_order->findOne('id='.$order->service_order_id);
                $service_order->setStatus(PROCESSED);
                $service_order->save();
                
                // clear order cookie
                setcookie ("order", "", time() - 3600, '/', 'shopinterest.co');
                // clear order session
                unset($this->shopper_session->order);
            } else {
                $this->view->order_status = 'failed';
                
                // update order status to failed
                $order_obj = new Order($store_dbobj);
                $order_obj->findOne('id='.$order->order_id);
                $order_obj->setStatus(FAILED);
                $order_obj->save();
                // update service order status to failed
                $service_order = new ServiceOrder($account_dbobj);
                $service_order->findOne('id='.$order->service_order_id);
                $service_order->setStatus(FAILED);
                $service_order->save();
                
            }
        }
        
        
        $this->view->continue_shopping_url = 'http://'.$_SERVER['HTTP_HOST'];
    }
    
    public function nowAction() {
        
    }
    
    // input: $_REQUEST([token] => EC-5XE058319H269841U [PayerID] => CFVJDEWTXQ83L) 
    public function thankyouAction() {
        Log::write(INFO, 'SESSION: '.json_encode($_SESSION));
        Log::write(INFO, 'COOKIE: '.json_encode($_COOKIE));
        Log::write(INFO, 'REQUEST: '.json_encode($_REQUEST));
        global $paypalconfig;
        
        $order = $this->shopper_session->order;
        $store_id = $this->shopper_session->store_id;
        
        if(empty($order) || empty($store_id) || empty($_REQUEST['token']) || 
            empty($_REQUEST['PayerID']) || $order->paypal->token !== $_REQUEST['token']) {
            redirect('http://'.$_SERVER['HTTP_HOST']);
        }
       
        // output
        $this->view->status = 'success';
        $this->view->retry_link = '/pay/thankyou?token='.$_REQUEST['token'].'&PayerID='.$_REQUEST['PayerID'];
        
        $account_dbobj = $this->account_dbobj;
        $store_dbobj = $this->store_dbobj;
        $token = $_REQUEST['token'];
        $payer_id = $_REQUEST['PayerID'];
        $this->shopper_session->order->paypal->payer_id = $payer_id;

        $service = PaypalService::getInstance();
        $service->setMethod('get_customer_details');
        $service->setParams(array(
            'token' => $token
        ));
        $service->call();
        if($service->getStatus() === 0) {
            // customer
            $response = $service->getResponse();
            
            // the order id and service order id need to match the results from the get_customer_results
            if($token === $response['TOKEN'] && 
                getOrderPaymentRequestId($store_id,$order->order_id) === $response['PAYMENTREQUESTINFO_0_PAYMENTREQUESTID']) {
                
                // create the shopper
                $shopper = new Shopper($account_dbobj);
                $shopper->findOne("username='".$account_dbobj->escape($response['EMAIL'])."'");
                $shopper_id = $shopper->getId();
                if(empty($shopper_id)) {
                    $shopper->setUsername($response['EMAIL']);
                    $shopper->save();
                    Log::write(INFO, 'Created a shopper '.$shopper_id);
                }
                Log::write(INFO, 'Get the shopper '.$shopper_id.' '.$response['EMAIL']);

                // create the paypal account for the shopper
                $paypal_account = new PaypalAccount($account_dbobj);
                $paypal_account->findOne("username='".$account_dbobj->escape($response['EMAIL'])."'");
                $paypal_account->setUsername($response['EMAIL']);
                $paypal_account->setFirstName($response['FIRSTNAME']);
                $paypal_account->setLastName($response['LASTNAME']);
                $paypal_account->setPayerId($response['PAYERID']);
                $paypal_account->setCountryCode($response['COUNTRYCODE']);
                $paypal_account->setPayerStatus($response['PAYERSTATUS']);
                $paypal_account->save();
                $this->shopper_session->order->paypal->username = $paypal_account->getUsername();
                $this->shopper_session->order->paypal->first_name = $paypal_account->getFirstName();
                $this->shopper_session->order->paypal->last_name = $paypal_account->getLastName();
                Log::write(INFO, 'Created a paypal account '.$paypal_account->getId().' '.$response['EMAIL']);

                // associate the shopper and the paypal account
                BaseMapper::saveAssociation($shopper, $paypal_account, $account_dbobj);
                Log::write(INFO, 'Created the association bw shoppers and paypal accounts '.$shopper->getId().' '.$paypal_account->getId());

                // associate the store and the shopper
                $store = new Store($account_dbobj);
                $store->setId($store_id);
                BaseMapper::saveAssociation($store, $shopper, $account_dbobj);
                Log::write(INFO, 'Created the association bw stores and shoppers '.$store_id.' '.$shopper->getId());

                // create address
                $address = new Address($account_dbobj);
                $address->setAddr1($response['SHIPTOSTREET']);
                $address->setCity($response['SHIPTOCITY']);
                $address->setState($response['SHIPTOSTATE']);
                $address->setCountry($response['SHIPTOCOUNTRYCODE']);
                $address->setZip($response['SHIPTOZIP']);
                $address->setPaypalAddressStatus($response['ADDRESSSTATUS']);
                $address->save();
                $this->shopper_session->order->address = $address;
                Log::write(INFO, 'Created an address '.$address->getId());

                // flll the note, to_name, to_address_id, currency_code to the order
                $order_obj = new Order($store_dbobj);
                $order_obj->findOne('id='.$order->order_id);
                $order_obj->setStatus(PROCESSING);
                $order_obj->setShopperId($shopper->getId());
                $order_obj->setNote(empty($response['PAYMENTREQUEST_0_NOTETEXT'])?'':$response['PAYMENTREQUEST_0_NOTETEXT']);
                $order_obj->setToName($response['SHIPTONAME']);
                $order_obj->setToAddressId($address->getId());
                $order_obj->setCurrencyCode($response['CURRENCYCODE']);
                $order_obj->setPaymentStatus($response['CHECKOUTSTATUS']);
                $order_obj->save();
                $this->shopper_session->order->note = $order_obj->getNote();
                $this->shopper_session->order->to_name = $order_obj->getToName();
                Log::write(INFO, 'Update the order '.$order_obj->getId().' set shipping address shopper id note...');
                
                // fill the note to the service order if the service is not zero
                $service_order = new ServiceOrder($account_dbobj);
                $service_order->findOne('id='.$order->service_order_id);
                $service_order->setStatus(PROCESSING);
                $service_order->save();
                Log::write(INFO, 'Update the service order');
                
                /* make the payment now*/
                
                $service = PaypalService::getInstance();
                $service->setMethod('make_payment');
                $payform = $order->paypal->payform;
                $fields = array(
                    'TOKEN' => $token,
                    'PAYERID' => $payer_id
                );
                $fields = array_merge($fields, $payform);
                $service->setParams($fields);
                $service->call();
                $response = $service->getResponse();

                if($service->getStatus() === 0) {
                    Log::write(INFO, 'Succeeded making the payment');
                    // update order status to processed
                    $order_obj = new Order($store_dbobj);
                    $order_obj->findOne('id='.$order->order_id);
                    $order_obj->setStatus(PROCESSED);
                    $order_obj->setPaymentStatus($response['PAYMENTINFO_0_PAYMENTSTATUS']);
                    $order_obj->save();
                    Log::write(INFO, 'mark the order status as processed');
                    // update service order status to processed
                    $service_order = new ServiceOrder($account_dbobj);
                    $service_order->findOne('id='.$order->service_order_id);
                    $service_order->setStatus(PROCESSED);
                    $service_order->save();
                    Log::write(INFO, 'mark the service order status as processed');

                    // clear order cookie
                    //setcookie ("order", "", time() - 3600, '/', 'shopinterest.co');
                    nuke_cookie('order');
                    Log::write(INFO, 'nuke the order entry in the cookie');
                    // clear order session
                    unset($this->shopper_session->order);
                    Log::write(INFO, 'nuke the order entry in the shopper session');
                    
                    global $shopinterest_config;
                    // send an email -- purchase confirmation
                    $service->destroy();
                    $service = EmailService::getInstance();
                    $service->setMethod('create_job');
                    $service->setParams(array(
                        'to' => $paypal_account->getUsername(),
                        'from' => $shopinterest_config->support->email,
                        'type' => SHOPPER_PURCHASE_CONFIRMATION,
                        'data' => array(
                            'site_url' => getURL(),
                            'store_id' => $store_id,
                            'order_id' => $order->order_id,
                            'datetime' => get_current_datetime(),
                            'products' => $order->products,
                            'price' => $order->price,
                            'tax' => $order->tax,
                            'shipping' => $order->shipping,
                            'total' => $order->total
                        ),
                        'job_dbobj' => $this->job_dbobj
                    ));
                    $service->call();

                    Log::write(INFO, 'Purchase confirmation email to '.$paypal_account->getUsername());
                    
                    // send an email -- sale notification
                    $service->destroy();
                    $service = EmailService::getInstance();
                    $service->setMethod('create_job');
                    $service->setParams(array(
                        'to' => $this->shopper_session->merchant_username,
                        'from' => $shopinterest_config->support->email,
                        'type' => MERCHANT_SALE_NOTIFICATION,
                        'data' => array(
                            'site_url' => getURL(),
                            'store_id' => $store_id,
                            'order_id' => $order->order_id,
                            'datetime' => get_current_datetime(),
                            'products' => $order->products,
                            'price' => $order->price,
                            'tax' => $order->tax,
                            'shipping' => $order->shipping,
                            'total' => $order->total
                        ),
                        'job_dbobj' => $this->job_dbobj
                    ));
                    $service->call();

                    Log::write(INFO, 'Sales Notication to '.$this->shopper_session->merchant_username);
                    
                } else {
                    $this->view->status = 'failure';
                    Log::write(WARN, 'Failed making the payment');
                    // update order status to failed
                    $order_obj = new Order($store_dbobj);
                    $order_obj->findOne('id='.$order->order_id);
                    $order_obj->setStatus(FAILED);
                    $order_obj->save();
                    Log::write(WARN, 'mark the order status as failed '.$order_obj->getId());
                    // update service order status to failed
                    $service_order = new ServiceOrder($account_dbobj);
                    $service_order->findOne('id='.$order->service_order_id);
                    $service_order->setStatus(FAILED);
                    $service_order->save();
                    Log::write(INFO, 'mark the service order status as failed '.$service_order->getId());
                }

            }

        } else {
            // cant get the details of customer, then redirect to 
            Log::write(WARN, 'Failed to get the details of the customer');
            $this->view->status = 'failure';
        }
        
        
        
    }
    
    // input: $_REQUEST([token] => EC-5XE058319H269841U [PayerID] => CFVJDEWTXQ83L) 
    public function returnAction() {

        global $paypalconfig, $shopinterest_config, $redis;
        $transaction_percentage = $paypalconfig->transaction_percentage;
        $transaction_flatfee = $paypalconfig->transaction_flatfee;

        Log::write(INFO, 'REQUEST: '.json_encode($_REQUEST));
        
        // validate the access of this page
        if(!isset($_REQUEST['token']) ||
           !isset($_REQUEST['PayerID']) ||    
           !isset($this->shopper_session) || !isset($this->shopper_session->transaction) || 
           !isset($this->shopper_session->transaction['transaction_info']) ||
           !isset($this->shopper_session->transaction['api_SetExpressCheckout']['request']) ||
           !isset($this->shopper_session->transaction['api_SetExpressCheckout']['response']) ||  
           !isset($this->shopper_session->store_id) ||
           ($this->shopper_session->store_id !== $this->shopper_session->transaction['transaction_info']['service_order']['store_id']) ||
           ($this->shopper_session->transaction['transaction_info']['user_session_id'] !== session_id())) {
            
            Log::write(WARN, 'Not a valid callback, redirect...');
            redirect(getURL());
        }
        
        $transaction = &$this->shopper_session->transaction;
        $transaction['return_params'] = $_REQUEST;
        $account_dbobj = $this->account_dbobj;
        
        $store_id = $transaction['transaction_info']['service_order']['store_id'];
        $store = new Store($account_dbobj, $store_id);
        Log::write(INFO, 'store info: '.json_encode($store));
        
        $store_host = $store->getHost();
        $transaction_fee_waived = $store->getTransactionFeeWaived();
        $store_dbobj = DbObj::getStoreDBObj($store_host, $store_id);
        $user_id = empty($this->user_session->user_id)?0:$this->user_session->user_id;
        $associate_account = '';
        $user_session_id = $this->shopper_session->transaction['transaction_info']['user_session_id'];

        // initialize the output to view
        $this->view->status = 'success';
        $this->view->retry_link = '/pay/return?token='.$transaction['return_params']['token'].
                '&PayerID='.$transaction['return_params']['PayerID'];        
        $store_subdomain = $store->getSubdomain();
        $this->view->store_url = getStoreUrl($store_subdomain);
        
        // get customer details
        $transaction['api_GetExpressCheckoutDetails']['request'] = array(
            'token' => $transaction['return_params']['token']
        );
        Log::write(INFO, 'GetExpressCheckoutDetails call');
        $service = new PaypalService();
        $service->setMethod('get_customer_details');
        $service->setParams($transaction['api_GetExpressCheckoutDetails']['request']);
        $service->call();
        $transaction['api_GetExpressCheckoutDetails']['response'] = $service->getResponse();
        Log::write(INFO, 'GetExpressCheckoutDetails response '.$transaction['api_GetExpressCheckoutDetails']['response']);
        // resonpse for api_GetExpressCheckoutDetails
        //        Array
        //        (
        //            [TOKEN] => EC-5AU88947SY143812Y
        //            [CHECKOUTSTATUS] => PaymentActionNotInitiated
        //            [TIMESTAMP] => 2013-02-01T17:49:15Z
        //            [CORRELATIONID] => db5b51021e9ec
        //            [ACK] => Success
        //            [VERSION] => 65.0
        //            [BUILD] => 5060305
        //            [EMAIL] => custom_1342461735_per@gmail.com
        //            [PAYERID] => U59FQ6DKJG9NG
        //            [PAYERSTATUS] => verified
        //            [FIRSTNAME] => customer
        //            [LASTNAME] => random
        //            [COUNTRYCODE] => US
        //            [SHIPTONAME] => Liang Huang
        //            [SHIPTOSTREET] => 40732 wolcott dr
        //            [SHIPTOCITY] => fremont
        //            [SHIPTOSTATE] => CA
        //            [SHIPTOZIP] => 94538
        //            [SHIPTOCOUNTRYCODE] => US
        //            [SHIPTOCOUNTRYNAME] => United States
        //            [ADDRESSSTATUS] => Confirmed
        //            [CURRENCYCODE] => USD
        //            [AMT] => 22.00
        //            [ITEMAMT] => 10.00
        //            [SHIPPINGAMT] => 10.00
        //            [HANDLINGAMT] => 0.00
        //            [TAXAMT] => 2.00
        //            [DESC] => Your Order in hgtv
        //            [INSURANCEAMT] => 0.00
        //            [SHIPDISCAMT] => 0.00
        //            [L_NAME0] => Customize solid red, white or blue cloth napkins with iron-on American flag patches.
        //            [L_QTY0] => 1
        //            [L_TAXAMT0] => 0.00
        //            [L_AMT0] => 10.00
        //            [L_ITEMWEIGHTVALUE0] =>    0.00000
        //            [L_ITEMLENGTHVALUE0] =>    0.00000
        //            [L_ITEMWIDTHVALUE0] =>    0.00000
        //            [L_ITEMHEIGHTVALUE0] =>    0.00000
        //            [PAYMENTREQUEST_0_CURRENCYCODE] => USD
        //            [PAYMENTREQUEST_0_AMT] => 22.00
        //            [PAYMENTREQUEST_0_ITEMAMT] => 10.00
        //            [PAYMENTREQUEST_0_SHIPPINGAMT] => 10.00
        //            [PAYMENTREQUEST_0_HANDLINGAMT] => 0.00
        //            [PAYMENTREQUEST_0_TAXAMT] => 2.00
        //            [PAYMENTREQUEST_0_DESC] => Your Order in hgtv
        //            [PAYMENTREQUEST_0_INSURANCEAMT] => 0.00
        //            [PAYMENTREQUEST_0_SHIPDISCAMT] => 0.00
        //            [PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID] => sell2_1337294610_biz@gmail.com
        //            [PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED] => false
        //            [PAYMENTREQUEST_0_PAYMENTREQUESTID] => order-18-78
        //            [PAYMENTREQUEST_0_SHIPTONAME] => Liang Huang
        //            [PAYMENTREQUEST_0_SHIPTOSTREET] => 40732 wolcott dr
        //            [PAYMENTREQUEST_0_SHIPTOCITY] => fremont
        //            [PAYMENTREQUEST_0_SHIPTOSTATE] => CA
        //            [PAYMENTREQUEST_0_SHIPTOZIP] => 94538
        //            [PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE] => US
        //            [PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME] => United States
        //            [PAYMENTREQUEST_0_ADDRESSSTATUS] => Confirmed
        //            [L_PAYMENTREQUEST_0_NAME0] => Customize solid red, white or blue cloth napkins with iron-on American flag patches.
        //            [L_PAYMENTREQUEST_0_QTY0] => 1
        //            [L_PAYMENTREQUEST_0_TAXAMT0] => 0.00
        //            [L_PAYMENTREQUEST_0_AMT0] => 10.00
        //            [L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE0] =>    0.00000
        //            [L_PAYMENTREQUEST_0_ITEMLENGTHVALUE0] =>    0.00000
        //            [L_PAYMENTREQUEST_0_ITEMWIDTHVALUE0] =>    0.00000
        //            [L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE0] =>    0.00000
        //            [PAYMENTREQUESTINFO_0_PAYMENTREQUESTID] => order-18-78
        //            [PAYMENTREQUESTINFO_0_ERRORCODE] => 0
        //        )
        
        if($service->getStatus() !== 0 ||
           empty($transaction['api_GetExpressCheckoutDetails']['response']['PAYMENTREQUEST_0_PAYMENTREQUESTID'])) {
            // cant get the details of customer
            $this->view->status = 'failure';
            Log::write(WARN, 'GetExpressCheckoutDetails failed, return...');
            return;
        } else {
            $payment_request_id = $transaction['api_GetExpressCheckoutDetails']['response']['PAYMENTREQUEST_0_PAYMENTREQUESTID'];
            $store_order_id = parseOrderPaymentRequestId($payment_request_id);
            if(!$store_order_id) {
                $this->view->status = 'failure';
                Log::write(WARN, 'store order id parsing error, return...');
                return;
            }
            $payment_request_store_id = $store_order_id['store_id'];
            $payment_request_order_id = $store_order_id['order_id'];
            Log::write(INFO, 'store id: '.$payment_request_store_id);
            Log::write(INFO, 'order id: '.$payment_request_order_id);
            if($payment_request_store_id != $this->shopper_session->store_id || 
                    $payment_request_order_id != $transaction['transaction_info']['order']['id']) {
                $this->view->status = 'failure';
                Log::write(WARN, 'store id or order id doesnt match the session data, return...');
                return;
            }
            $order = new Order($store_dbobj);
            $order->findOne('id='.$payment_request_order_id);
            if($order->getId() === 0) {
                $this->view->status = 'failure';
                Log::write(ERROR, 'cant find the order in the db, return...');
                return;
            } else {
                // validate the order info
                if($order->getTotal() != (string) $transaction['transaction_info']['order']['total'] || 
                   $order->getPrice() != (string) $transaction['transaction_info']['order']['price'] ||
                   $order->getShipping() != (string) $transaction['api_GetExpressCheckoutDetails']['response']['SHIPPINGAMT'] ||
                   $order->getShipping() != (string) $transaction['transaction_info']['order']['shipping'] || 
                   $order->getTax() != (string) $transaction['api_GetExpressCheckoutDetails']['response']['TAXAMT'] ||
                   $order->getTax() != (string) $transaction['transaction_info']['order']['tax']) {
                    $this->view->status = 'failure';
                    Log::write(WARN, 'order validation failed, return...');
                    return;
                }
            }
        }

        $products = $transaction['transaction_info']['order']['items'];  
        $unavailable_products = $transaction['transaction_info']['unavailable_products'];
                
        foreach ($products as $product) {
            // check if quantity key alive
            $key = generate_product_quantity_key($store_id, $product['product_id'], $user_session_id);
            if(!$redis->ttl($key)) {
                $this->view->status = 'failure';
                paypal_logger('Cache Time out, return...');
                return;                
            }
        }
        
        // create the shipping address
        $address = new Address($account_dbobj);
        $address->setName(isset($transaction['api_GetExpressCheckoutDetails']['response']['SHIPTONAME'])?
                $transaction['api_GetExpressCheckoutDetails']['response']['SHIPTONAME']:'');
        $address->setAddr1(isset($transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOSTREET'])?
                $transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOSTREET']:'');
        $address->setAddr2(isset($transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOSTREET2'])?
                $transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOSTREET2']:'');
        $address->setCity(isset($transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOCITY'])?
                $transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOCITY']:'');
        $address->setState(isset($transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOSTATE'])?
                $transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOSTATE']:'');
        $address->setCountry(isset($transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOCOUNTRYCODE'])?
                $transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOCOUNTRYCODE']:'');
        $address->setZip(isset($transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOZIP'])?
                $transaction['api_GetExpressCheckoutDetails']['response']['SHIPTOZIP']:'');
        $address->setPaypalAddressStatus(isset($transaction['api_GetExpressCheckoutDetails']['response']['ADDRESSSTATUS'])?
                $transaction['api_GetExpressCheckoutDetails']['response']['ADDRESSSTATUS']:'');
        $address->save();
        Log::write(INFO, 'address info: '.json_encode($address));

        // flll the note, to_name, to_address_id to the order
        $order->setStatus(PROCESSING);
        $order->setNote(empty($transaction['api_GetExpressCheckoutDetails']['response']['PAYMENTREQUEST_0_NOTETEXT'])?
                '':$transaction['api_GetExpressCheckoutDetails']['response']['PAYMENTREQUEST_0_NOTETEXT']);
        $order->setToName($transaction['api_GetExpressCheckoutDetails']['response']['SHIPTONAME']);
        $order->setToAddressId($address->getId());
        $order->setUserId($user_id);
        $order->setToEmail($transaction['api_GetExpressCheckoutDetails']['response']['EMAIL']);
        $order->setToFirstName($transaction['api_GetExpressCheckoutDetails']['response']['FIRSTNAME']);
        $order->setToLastName($transaction['api_GetExpressCheckoutDetails']['response']['LASTNAME']);
        $order->setPaymentStatus($transaction['api_GetExpressCheckoutDetails']['response']['CHECKOUTSTATUS']);
        $order->save();
        $transaction['transaction_info']['order']['status'] = $order->getStatus();
        $transaction['transaction_info']['order']['payment_status'] = $order->getPaymentStatus();
        $transaction['transaction_info']['order']['note'] = $order->getNote();
        $transaction['transaction_info']['order']['to_name'] = $order->getToName();
        $transaction['transaction_info']['order']['to_address_id'] = $order->getToAddressId();
        $transaction['transaction_info']['order']['user_id'] = $order->getUserId();
        $transaction['transaction_info']['order']['to_email'] = $order->getToEmail();
        $transaction['transaction_info']['order']['to_first_name'] = $order->getToFirstName();
        $transaction['transaction_info']['order']['to_last_name'] = $order->getToLastName();
        Log::write(INFO, 'order info update: '.json_encode($order));
        
        // update the status of the service order
        $service_order = new ServiceOrder($account_dbobj);
        $service_order->findOne('id='.$transaction['transaction_info']['service_order']['id']);
        if($service_order->getId() !== 0) {
            $service_order->setStatus(PROCESSING);
            $service_order->save();
            $transaction['transaction_info']['service_order']['status'] = PROCESSING;
        }
        Log::write(INFO, 'service order info update: '.json_encode($service_order));
        
        // update the status of the sale
        if($transaction['transaction_info']['sales']['total_commission_amt'] !== 0) {
            foreach($transaction['transaction_info']['sales']['items'] as $i => $item) {
                $sale = new Sale($account_dbobj);
                $sale->findOne('id='.$item['id']);
                if($sale->getId() !== 0) {
                    $sale->setStatus(PROCESSING);
                    $sale->save();
                    $transaction['transaction_info']['sales']['items'][$i]['status'] = PROCESSING;
                    Log::write(INFO, 'sale info update: '.json_encode($sale));
                }
            }
        }

        
        Log::write(INFO, 'SetExpressCheckout call');

        // do express checkout -- make the payment
        $service = new PaypalService();
        $service->setMethod('make_payment');
        $payform = $transaction['api_SetExpressCheckout']['request'];
        $fields = array(
            'TOKEN' => $transaction['api_GetExpressCheckoutDetails']['response']['TOKEN'],
            'PAYERID' => $transaction['api_GetExpressCheckoutDetails']['response']['PAYERID']
        );
        $fields = array_merge($fields, $payform);
        $service->setParams($fields);
        $service->call();
        $transaction['api_DoExpressCheckoutPayment']['request'] = $fields;
        $transaction['api_DoExpressCheckoutPayment']['response'] = $service->getResponse();
        
        Log::write(INFO, 'SetExpressCheckout response'.json_encode($transaction['api_DoExpressCheckoutPayment']['response']));
        
        if($service->getStatus() !== 0) {
            $this->view->status = 'failure';
            Log::write(INFO, 'SetExpressCheckout failed, return...');
            return;
        }
        
        // response for api_DoExpressCheckoutPayment
        //        Array
        //        (
        //            [TOKEN] => EC-24B85767PD761524G
        //            [SUCCESSPAGEREDIRECTREQUESTED] => false
        //            [TIMESTAMP] => 2013-02-01T23:33:03Z
        //            [CORRELATIONID] => c6254fc6aec21
        //            [ACK] => Success
        //            [VERSION] => 65.0
        //            [BUILD] => 5060305
        //            [INSURANCEOPTIONSELECTED] => false
        //            [SHIPPINGOPTIONISDEFAULT] => false
        //            [PAYMENTINFO_0_TRANSACTIONID] => 68U90634XE741035L
        //            [PAYMENTINFO_0_TRANSACTIONTYPE] => cart
        //            [PAYMENTINFO_0_PAYMENTTYPE] => instant
        //            [PAYMENTINFO_0_ORDERTIME] => 2013-02-01T23:32:59Z
        //            [PAYMENTINFO_0_AMT] => 22.00
        //            [PAYMENTINFO_0_FEEAMT] => 0.94
        //            [PAYMENTINFO_0_TAXAMT] => 2.00
        //            [PAYMENTINFO_0_CURRENCYCODE] => USD
        //            [PAYMENTINFO_0_PAYMENTSTATUS] => Completed
        //            [PAYMENTINFO_0_PENDINGREASON] => None
        //            [PAYMENTINFO_0_REASONCODE] => None
        //            [PAYMENTINFO_0_PROTECTIONELIGIBILITY] => Eligible
        //            [PAYMENTINFO_0_PROTECTIONELIGIBILITYTYPE] => ItemNotReceivedEligible,UnauthorizedPaymentEligible
        //            [PAYMENTINFO_0_SELLERPAYPALACCOUNTID] => sell2_1337294610_biz@gmail.com
        //            [PAYMENTINFO_0_PAYMENTREQUESTID] => order-18-82
        //            [PAYMENTINFO_0_ERRORCODE] => 0
        //        )
        
        // update status and payment status of order

        $currency_code = $transaction['api_DoExpressCheckoutPayment']['response']['PAYMENTINFO_0_CURRENCYCODE'];

        $order->setStatus(PROCESSED);
        $order->setPaymentStatus($transaction['api_DoExpressCheckoutPayment']['response']['PAYMENTINFO_0_PAYMENTSTATUS']);
        $order->save();
        $transaction['transaction_info']['order']['status'] = $order->getStatus();
        $transaction['transaction_info']['order']['payment_status'] = $order->getPaymentStatus();
        Log::write(INFO, 'update order status to processed');
        // update status of service_order
        $service_order->setStatus(PROCESSED);
        $service_order->save();
        Log::write(INFO, 'update service order status to processing');
        // update status of sale
        if($transaction['transaction_info']['sales']['total_commission_amt'] !== 0) {
            foreach($transaction['transaction_info']['sales']['items'] as $i => $item) {
                $sale = new Sale($account_dbobj);
                $sale->findOne('id='.$item['id']);
                if($sale->getId() !== 0) {
                    $sale->setStatus(PROCESSED);
                    $sale->save();
                    $transaction['transaction_info']['sales']['items'][$i]['status'] = PROCESSED;
                    Log::write(INFO, 'update sale status to processed');
                }
            }
        }   
        // shopinterest payment account id
        $shopinterest_paypal_account = new PaypalAccount($account_dbobj);
        $shopinterest_paypal_account->findOne("username='".$account_dbobj->escape($paypalconfig->user->email)."'");
        $shopinterest_paypal_account_id = $shopinterest_paypal_account->getId();
        $shopinterest_payment_account_id = PaypalAccountsMapper::getPaymentAccountId($shopinterest_paypal_account_id, $account_dbobj);
        // create a payment item from shopinterest to seller
        if($transaction['transaction_info']['payment_solution'] == PROVIDER_SHOPAY) {

            Log::write(INFO, 'for shopay, we need to create payment items');
            
            // seller payment account id
            $seller_paypal_account = new PaypalAccount($account_dbobj);
            $seller_paypal_account->findOne("username='".$account_dbobj->escape($this->shopper_session->merchant_paypal_username)."'");
            $seller_paypal_account_id = $seller_paypal_account->getId();
            $seller_payment_account_id = PaypalAccountsMapper::getPaymentAccountId($seller_paypal_account_id, $account_dbobj);
            
            $shopinterest_seller_payment_item = new PaymentItem($account_dbobj);
            $shopinterest_seller_payment_item->setSender($shopinterest_payment_account_id);
            $shopinterest_seller_payment_item->setReceiver($seller_payment_account_id);
            $shopinterest_seller_payment_item->setCurrencyCode($currency_code);
            $shopinterest_seller_contract = array(
                'gross' => $transaction['transaction_info']['order']['total'],
                'deductible' => array(
                    'paypal_fee' => ($transaction_fee_waived == 1)?0:round($transaction['transaction_info']['order']['total'] * $transaction_percentage + $transaction_flatfee, 2),
                    'service_fee' => $transaction['transaction_info']['service_order']['total'],
                    'sales_commission' => $transaction['transaction_info']['sales']['total_commission_amt']
                )
            );
            $shopinterest_seller_contract['net'] = $shopinterest_seller_contract['gross'] -
            ($shopinterest_seller_contract['deductible']['paypal_fee'] + $shopinterest_seller_contract['deductible']['service_fee'] +
            $shopinterest_seller_contract['deductible']['sales_commission']);
            $shopinterest_seller_payment_item->setContract(json_encode($shopinterest_seller_contract));
            $shopinterest_seller_payment_item->setAmt($shopinterest_seller_contract['net']);
            $shopinterest_seller_payment_item->save();
            Log::write(INFO, 'seller payment item info: '.json_encode($shopinterest_seller_payment_item));
            // associate the payment item with the order
            $order_payment = new OrderPayment($account_dbobj);
            $order_payment->setStoreId($store_id);
            $order_payment->setOrderId($order->getId());
            $order_payment->setPaymentItemId($shopinterest_seller_payment_item->getId());
            $order_payment->save();
            Log::write(INFO, 'order payment info: '.json_encode($order_payment));
        }
        
        // create a payment item from shopinterest to sale associate
        if($transaction['transaction_info']['sales']['total_commission_amt'] !== 0) {
            foreach($transaction['transaction_info']['sales']['items'] as $i => $item) {
                $sale = new Sale($account_dbobj);
                $sale->findOne('id='.$item['id']);
                if($sale->getId() !== 0) {
                    $associate_id = $sale->getAssociateId();
                    $associate_user_id = UsersMapper::getUserIdByAssociateId($associate_id, $account_dbobj);
                    $associate_payment_account_id = $redis->get("user:$associate_user_id:payment_account_id");
                    $associate_account = $redis->get("user:$associate_user_id:username");
                    
                    $shopinterest_associate_payment_item = new PaymentItem($account_dbobj);
                    $shopinterest_associate_payment_item->setSender($shopinterest_payment_account_id);
                    $shopinterest_associate_payment_item->setReceiver($associate_payment_account_id);
                    $shopinterest_associate_contract = array(
                        'gross' => $item['commission_amt'],
                        'deductible' => array(
                            'paypal_fee' => round($item['commission_amt'] * $transaction_percentage + $transaction_flatfee, 2)
                        )
                    );
                    $shopinterest_associate_contract['net'] = $shopinterest_associate_contract['gross'] -
                    $shopinterest_associate_contract['deductible']['paypal_fee'];
                    $shopinterest_associate_payment_item->setContract(json_encode($shopinterest_associate_contract));
                    $shopinterest_associate_payment_item->setAmt($shopinterest_associate_contract['net']);
                    $shopinterest_associate_payment_item->setCurrencyCode($currency_code);
                    $shopinterest_associate_payment_item->save();
                    
                    Log::write(INFO, 'associate payment item: '.json_encode($shopinterest_associate_payment_item));
                    
                    // associate the payment item with the sale
                    $sale_payment = new SalePayment($account_dbobj);
                    $sale_payment->setSaleId($sale->getId());
                    $sale_payment->setPaymentItemId($shopinterest_associate_payment_item->getId());
                    $sale_payment->save();
                    
                    Log::write(INFO, 'sale payment: '.json_encode($sale_payment));
                    
                    // put the association bw associate and product into the associates_products table
                    // if such association is missing
                    $associates_product = new AssociatesProduct($account_dbobj);
                    $associates_product->findOne('associate_id='.$sale->getAssociateId().' and store_id='.$sale->getStoreId().' and product_id='.$sale->getProductId());
                    if($associates_product->getId() === 0) {
                        $associates_product->setAssociateId($sale->getAssociateId());
                        $associates_product->setStoreId($sale->getStoreId());
                        $associates_product->setProductId($sale->getProductId());
                        $associates_product->save();
                        Log::write(INFO, 'associate product assoc: '.json_encode($associates_product));
                    }
                }
            }
        } 
        
        $sellout_products = array();        
        //update product quantity 
        $i = 0;        
        $dbname = $store_dbobj->getDBName();
        
        foreach ($products as $product) {
            $product_id = $product['product_id'];
            
            $product_obj = new Product($store_dbobj);
            $product_obj->findOne('id='.$product_id);
            $stock_quantity = $product_obj->getQuantity();   
            $checkout_quantity = $product['quantity'];  
            $product_obj->setQuantity($stock_quantity - $checkout_quantity);
            $product_obj->save();
            
            if($product_obj->getQuantity() === 0) {
                $sellout_products[$i]['id'] = $product_obj->getId();
                $sellout_products[$i]['name'] = $product_obj->getName();    
                $sellout_products[$i]['description'] = $product_obj->getDescription();  
                $sellout_products[$i]['price'] = $product_obj->getPrice();
                $i++;
            }
            
            $key = generate_product_quantity_key($store_id, $product['product_id'], $user_session_id);  
            $redis->del($key);
        }        
        
        // send an email to seller
        $service = new EmailService();
        $service->setMethod('create_job');
        $service->setParams(array(
            'to' => $this->shopper_session->merchant_username,
            'from' => $shopinterest_config->support->email,
            'type' => MERCHANT_SALE_NOTIFICATION,
            'data' => array(
                'site_url' => getURL(),
                'store_id' => $store_id,
                'order_id' => $order->getId(),
                'datetime' => get_current_datetime(),
                'products' => $transaction['transaction_info']['order']['products'],
                'price' => $order->getPrice(),
                'tax' => $order->getTax(),
                'shipping' => $order->getShipping(),
                'total' => $order->getTotal(),
                'currency_symbol' => currency_symbol($currency_code),
            ),
            'job_dbobj' => $this->job_dbobj
        ));
        $service->call();       
        
        //product quantity becomes zero, send a email to seller
        if(!empty($sellout_products)) {
            $service->setMethod('create_job');
            $service->setParams(array(
                'to' => $this->shopper_session->merchant_username,
                'from' => $shopinterest_config->support->email,
                'type' => MERCHANT_PRODUCT_SOLDOUT_NOTIFICATION,
                'data' => array(
                    'site_url' => getURL(),
                    'datetime' => get_current_datetime(),
                    'products' => $sellout_products             
                ),
                'job_dbobj' => $this->job_dbobj
            ));
            $service->call();
        }
        Log::write(INFO, 'sena an email to seller '.$this->shopper_session->merchant_username);
        // send an email to buyer
        $service = new EmailService();
        $service->setMethod('create_job');
        $service->setParams(array(
            'to' => $order->getToEmail(),
            'from' => $shopinterest_config->support->email,
            'type' => SHOPPER_PURCHASE_CONFIRMATION,
            'data' => array(
                'site_url' => getURL(),
                'store_id' => $store_id,
                'order_id' => $order->getId(),
                'datetime' => get_current_datetime(),
                'products' => $transaction['transaction_info']['order']['products'],
                'price' => $order->getPrice(),
                'tax' => $order->getTax(),
                'shipping' => $order->getShipping(),
                'total' => $order->getTotal(),
                'currency_symbol' => currency_symbol($currency_code),
            ),
            'job_dbobj' => $this->job_dbobj
        ));
        $service->call();
        Log::write(INFO, 'send an email to buyer '.$order->getToEmail());
        
        // send an email to sales associate
        if($transaction['transaction_info']['sales']['total_commission_amt'] !== 0 && !empty($associate_account)) {
            
            $service = new EmailService();
            $service->setMethod('create_job');
            $service->setParams(array(
                'to' => $associate_account,
                'from' => $shopinterest_config->support->email,
                'type' => ASSOCIATE_AFFILATE_CONFIRMATION,
                'data' => array(
                    'datetime' => get_current_datetime(),
                    'site_url' => getURL(),
                    'products' => $transaction['transaction_info']['sales']['items'],
                    'total' => $transaction['transaction_info']['sales']['total_commission_amt'],
                    'currency_symbol' => currency_symbol($currency_code),
                ),
                'job_dbobj' => $this->job_dbobj
            ));
            $service->call();
            Log::write(INFO, 'sena an email to sales associate '.$associate_account);
        }        
            
        //set products view here
        $this->view->available_products = $products;
        $this->view->unavailable_products = $unavailable_products;        
        // nuke cookie and reset transaction session
        unset($this->shopper_session->transaction);
        Log::write(INFO, 'unset shopper transaction session, nuke cookies, done!!!!!!');
        
    }
    
    public function cancelAction() {
        // nuke cookie and reset transaction session
        unset($this->shopper_session->transaction);
        nuke_cookie('order');
        
        redirect(getURL());
    }
    
    // input: $_REQUEST([token] => EC-5XE058319H269841U [PayerID] => CFVJDEWTXQ83L) 
    // this is the thank you page for flash deals
    // before making the payment, need to check the usage limit of the coupon
    public function congratsAction() {
        Log::write(INFO, 'SESSION: '.json_encode($_SESSION));
        Log::write(INFO, 'COOKIE: '.json_encode($_COOKIE));
        Log::write(INFO, 'REQUEST: '.json_encode($_REQUEST));
        global $paypalconfig;
        
        if(empty($this->shopper_session->order)) {
            redirect('http://'.$_SERVER['HTTP_HOST']);
        }
        
        $order = $this->shopper_session->order;
        $products = $this->shopper_session->order->products;
        $store_id = $order->coupon->getStoreId();
        $store = new Store($this->account_dbobj);
        $store->findOne('id='.$store_id);
        $store_name = $store->getName();
        $store_subdomain = $store->getSubdomain();
        $payform = $this->shopper_session->payform;
        
        if(empty($order) || empty($store_id) || empty($_REQUEST['token']) || 
            empty($_REQUEST['PayerID']) || $order->paypal->token !== $_REQUEST['token']) {
            redirect('http://'.$_SERVER['HTTP_HOST']);
        }
       
        // output
        $this->view->status = 'success';
        $this->view->retry_link = '/pay/congrats?token='.$_REQUEST['token'].'&PayerID='.$_REQUEST['PayerID'];
        
        $account_dbobj = $this->account_dbobj;
        $store_dbobj = DBObj::getStoreDBObj($store->getHost(), $store_id);
        $token = $_REQUEST['token'];
        $payer_id = $_REQUEST['PayerID'];
        $this->shopper_session->order->paypal->payer_id = $payer_id;

        $this->view->store_name = $store_name;
        $this->view->store_url = getStoreUrl($store_subdomain);
        
        $service = PaypalService::getInstance();
        $service->setMethod('get_customer_details');
        $service->setParams(array(
            'token' => $token
        ));
        $service->call();
        if($service->getStatus() === 0) {
            // customer
            $response = $service->getResponse();
            // the order id and service order id need to match the results from the get_customer_results
            if($token === $response['TOKEN'] && 
                $payform['PAYMENTREQUEST_0_PAYMENTREQUESTID'] === $response['PAYMENTREQUESTINFO_0_PAYMENTREQUESTID']) {

                // create the shopper
                $shopper = new Shopper($account_dbobj);
                $shopper->findOne("username='".$account_dbobj->escape($response['EMAIL'])."'");
                $shopper_id = $shopper->getId();
                if(empty($shopper_id)) {
                    $shopper->setUsername($response['EMAIL']);
                    $shopper->save();
                    $shopper_id = $shopper->getId();
                    Log::write(INFO, 'Created a shopper '.$shopper->getId());
                }
                Log::write(INFO, 'Get the shopper '.$shopper_id.' '.$response['EMAIL']);

                // create the paypal account for the shopper
                $paypal_account = new PaypalAccount($account_dbobj);
                $paypal_account->findOne("username='".$account_dbobj->escape($response['EMAIL'])."'");
                $paypal_account->setUsername($response['EMAIL']);
                $paypal_account->setFirstName($response['FIRSTNAME']);
                $paypal_account->setLastName($response['LASTNAME']);
                $paypal_account->setPayerId($response['PAYERID']);
                $paypal_account->setCountryCode($response['COUNTRYCODE']);
                $paypal_account->setPayerStatus($response['PAYERSTATUS']);
                $paypal_account->save();
                $this->shopper_session->order->paypal->username = $paypal_account->getUsername();
                $this->shopper_session->order->paypal->first_name = $paypal_account->getFirstName();
                $this->shopper_session->order->paypal->last_name = $paypal_account->getLastName();
                Log::write(INFO, 'Created a paypal account '.$paypal_account->getId().' '.$response['EMAIL']);

                // associate the shopper and the paypal account
                BaseMapper::saveAssociation($shopper, $paypal_account, $account_dbobj);
                Log::write(INFO, 'Created the association bw shoppers and paypal accounts '.$shopper->getId().' '.$paypal_account->getId());

                // associate the store and the shopper
                $store = new Store($account_dbobj);
                $store->setId($store_id);
                BaseMapper::saveAssociation($store, $shopper, $account_dbobj);
                Log::write(INFO, 'Created the association bw stores and shoppers '.$store_id.' '.$shopper->getId());

                // create address
                $address = new Address($account_dbobj);
                $address->setAddr1($response['SHIPTOSTREET']);
                $address->setCity($response['SHIPTOCITY']);
                $address->setState($response['SHIPTOSTATE']);
                $address->setCountry($response['SHIPTOCOUNTRYCODE']);
                $address->setZip($response['SHIPTOZIP']);
                $address->setPaypalAddressStatus($response['ADDRESSSTATUS']);
                $address->save();
                $this->shopper_session->order->address = $address;
                Log::write(INFO, 'Created an address '.$address->getId());

                // flll the note, to_name, to_address_id, currency_code to the order
                $order_obj = new Order($store_dbobj);
                $order_obj->findOne('id='.$order->order_id);
                $order_obj->setStatus(PROCESSING);
                $order_obj->setShopperId($shopper->getId());
                $order_obj->setNote(empty($response['PAYMENTREQUEST_0_NOTETEXT'])?'':$response['PAYMENTREQUEST_0_NOTETEXT']);
                $order_obj->setToName($response['SHIPTONAME']);
                $order_obj->setToAddressId($address->getId());
                $order_obj->setCurrencyCode($response['CURRENCYCODE']);
                $order_obj->setPaymentStatus($response['CHECKOUTSTATUS']);
                $order_obj->save();
                $this->shopper_session->order->note = $order_obj->getNote();
                $this->shopper_session->order->to_name = $order_obj->getToName();
                Log::write(INFO, 'Update the order '.$order_obj->getId().' set shipping address shopper id note...');
                
                /* before making the payment, we need to check the usage limit of the coupon */
                $coupon = $this->shopper_session->order->coupon;
                $coupon_id = $coupon->getId();
                $updated_coupon = new Coupon($account_dbobj);
                $updated_coupon->findOne('id='.$coupon_id);
                $current_usage_limit = intval($updated_coupon->getUsageLimit());
                if($current_usage_limit <= 0) {
                    $this->view->status = 'failure';
                    Log::write(WARN, 'Failed making the payment, no more coupon is available');
                    // update order status to failed
                    $order_obj = new Order($store_dbobj);
                    $order_obj->findOne('id='.$order->order_id);
                    $order_obj->setStatus(FAILED);
                    $order_obj->save();
                    Log::write(WARN, 'mark the order status as failed '.$order_obj->getId());
                    
                    $this->view->errnos[COUPON_EXCEED_USAGE] = 1;
                    
                    return;
                }
                
                /* make the payment now*/
                
                $service = PaypalService::getInstance();
                $service->setMethod('make_payment');
                $payform = $order->paypal->payform;
                $fields = array(
                    'TOKEN' => $token,
                    'PAYERID' => $payer_id
                );
                $fields = array_merge($fields, $payform);
                $service->setParams($fields);
                $service->call();
                $response = $service->getResponse();

                if($service->getStatus() === 0) {
                    Log::write(INFO, 'Succeeded making the payment');
                    
                    // need to decrement the usage limit
                    $current_usage_limit = $current_usage_limit - 1;
                    $updated_coupon->setUsageLimit($current_usage_limit);
                    $updated_coupon->save();
                    
                    // update order status to processed
                    $order_obj = new Order($store_dbobj);
                    $order_obj->findOne('id='.$order->order_id);
                    $order_obj->setStatus(PROCESSED);
                    $order_obj->setPaymentStatus($response['PAYMENTINFO_0_PAYMENTSTATUS']);
                    $order_obj->save();
                    Log::write(INFO, 'mark the order status as processed');

                    //decrease product quantity
                    foreach ($products as $product) {
                        $product_obj = new Product($store_dbobj);
                        $product_obj->findOne('id='.$product['product_id']);
                        if($product_obj->getId() !== 0) {
                            $product_quantity = $product_obj->getQuantity() -1;
                            $product_obj->setQuantity($product_quantity);
                            $product_obj->save();                           
                        }
                    }

                    global $shopinterest_config;
                    // send an email -- purchase confirmation
                    $service->destroy();
                    $service = EmailService::getInstance();
                    $service->setMethod('create_job');
                    $service->setParams(array(
                        'to' => $paypal_account->getUsername(),
                        'from' => $shopinterest_config->support->email,
                        'type' => SHOPPER_PURCHASE_CONFIRMATION,
                        'data' => array(
                            'site_url' => getURL(),
                            'store_id' => $store_id,
                            'order_id' => $order->order_id,
                            'datetime' => get_current_datetime(),
                            'products' => $order->products,
                            'price' => $order->price,
                            'tax' => $order->tax,
                            'shipping' => $order->shipping,
                            'total' => $order->total
                        ),
                        'job_dbobj' => $this->job_dbobj
                    ));
                    $service->call();

                    Log::write(INFO, 'Purchase confirmation email to '.$paypal_account->getUsername());
                    
                    // send an email -- sale notification
                    $service->destroy();
                    $service = EmailService::getInstance();
                    $service->setMethod('create_job');
                    $service->setParams(array(
                        'to' => $this->shopper_session->order->merchant_username,
                        'from' => $shopinterest_config->support->email,
                        'type' => MERCHANT_SALE_NOTIFICATION,
                        'data' => array(
                            'site_url' => getURL(),
                            'store_id' => $store_id,
                            'order_id' => $order->order_id,
                            'datetime' => get_current_datetime(),
                            'products' => $order->products,
                            'price' => $order->price,
                            'tax' => $order->tax,
                            'shipping' => $order->shipping,
                            'total' => $order->total
                        ),
                        'job_dbobj' => $this->job_dbobj
                    ));
                    $service->call();

                    Log::write(INFO, 'Sales Notication to '.$this->shopper_session->order->merchant_username);
                    
                    // clear order session
                    unset($this->shopper_session->order);
                    Log::write(INFO, 'nuke the order entry in the shopper session');
                    
                } else {
                    $this->view->status = 'failure';
                    Log::write(WARN, 'Failed making the payment');
                    // update order status to failed
                    $order_obj = new Order($store_dbobj);
                    $order_obj->findOne('id='.$order->order_id);
                    $order_obj->setStatus(FAILED);
                    $order_obj->save();
                    Log::write(WARN, 'mark the order status as failed '.$order_obj->getId());
                    // update service order status to failed
                    $service_order = new ServiceOrder($account_dbobj);
                    $service_order->findOne('id='.$order->service_order_id);
                    $service_order->setStatus(FAILED);
                    $service_order->save();
                    Log::write(WARN, 'mark the service order status as failed '.$service_order->getId());
                    $this->view->errnos[PAYMENT_FAILURE] = 1;
                }

            } else {
                // cant get the details of customer, then redirect to 
                Log::write(WARN, 'Failed to get the details of the customer');
                $this->view->status = 'failure';
                $this->view->errnos[PAYMENT_FAILURE] = 1;                
            }

        } else {
            // cant get the details of customer, then redirect to 
            Log::write(WARN, 'Failed to get the details of the customer');
            $this->view->status = 'failure';
            $this->view->errnos[PAYMENT_FAILURE] = 1;
        }
        
        
        
    }
    
    public function congrats2Action() {
        $this->view->status = 'success';
    }    

}

