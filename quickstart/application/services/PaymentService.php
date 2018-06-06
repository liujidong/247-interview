<?php

class PaymentService extends BaseService {
    
    // setup the order
    // input:
    // array(
    //     'coupon_id' => xxx,
    //     'aid' => xxx,
    //     'currency_code' => 'USD/AUD/CAD/EUR',
    //     'items' => array(
    //         'store_id',
    //         'product_id',
    //         'product_name',
    //         'product_description',
    //         'product_size',
    //         'product_quantity',
    //         'product_price',
    //         'product_shipping',
    //         'product_commission'
    //     )
    // )
    public function setup_order() {
        $coupon_id = isset($this->params['coupon_id'])?$this->params['coupon_id']:0;
        $aid = isset($this->params['aid'])?$this->params['aid']:'';
        $currency_code = isset($this->params['currency_code'])?$this->params['currency_code']:'USD';
        
        
        
    }
    
    // setup the order
    // input:
    // array(
    //     'shopper_id',
    //     'billing_first_name',
    //     'billing_last_name',
    //     'billing_email',
    //     'billing_phone',
    //     'billing_addr1',
    //     'billing_addr2',
    //     'billing_city',
    //     'billing_state',
    //     'billing_country',
    //     'billing_zip',
    //     'mailing_first_name',
    //     'mailing_last_name',
    //     'mailing_email',
    //     'mailing_phone',
    //     'mailing_addr1',
    //     'mailing_addr2',
    //     'mailing_city',
    //     'mailing_state',
    //     'mailing_country',
    //     'mailing_zip',
    // )
    public function create_order() {
        
        
        
    }
    
    // input:
    // array(
    //     'payment_method_id' => xxx
    //     'items' => array(
    //         0 => array(
    //             'sender' => xxx,
    //             'receiver' => xxx,
    //             'amt' => xxx,
    //             'depth' => xxx,
    //             'order' => xxx
    //         )
    //         ...
    //     )
    // 
    // )
    public function setup_payment() {
        
    }
    
    // input:
    // payment_id
    public function make_payment() {
        
    }
    
    
    // input: api, params
    private function adaptivepayments($api, $params) {
        global $paypalconfig;
        
        $url = $paypalconfig->api->adaptivepayments->endpoint.'/'.$api;
        
        $headers = array(
            'X-PAYPAL-SECURITY-USERID: '.$paypalconfig->user->username,
            'X-PAYPAL-SECURITY-PASSWORD: '.$paypalconfig->user->password,
            'X-PAYPAL-SECURITY-SIGNATURE: '.$paypalconfig->user->signature,
            'X-PAYPAL-DEVICE-IPADDRESS: '.$_SERVER['SERVER_ADDR'],
            'X-PAYPAL-REQUEST-DATA-FORMAT: NV',
            'X-PAYPAL-RESPONSE-DATA-FORMAT: NV',
            'X-PAYPAL-APPLICATION-ID: APP-80W284485P519543T'
        );
        $response = curl_post($url, $params, $headers);
        return $response;
    }
    
    // input: 
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
    // output:
    // payKey
    // payErrorList
    // paymentExecStatus
    public function adaptive_pay() {
        
        $api = 'Pay';
        $apiparams = $this->params['apiparams'];
        $this->response = $this->adaptivepayments($api, $apiparams);
    }
    
    // input: method, params
    private function expresscheckout($method, $params) {
        global $paypalconfig;
        
        $url = $paypalconfig->api->expresscheckout->endpoint;

        $extra_params = array(
            'METHOD' => $method,
            'VERSION' => $paypalconfig->api->expresscheckout->version,
            'USER' => $paypalconfig->user->username,
            'PWD' => $paypalconfig->user->password,
            'SIGNATURE' => $paypalconfig->user->signature
        );
        $postfields = array_merge($params, $extra_params);
        Log::write(INFO, 'POST:'.$url);
        Log::write(INFO, 'POST Data:'.json_encode($postfields));
        $post_response = curl_post($url, $postfields);
        Log::write(INFO, 'POST Response:'.$post_response);
        parse_str($post_response, $response);
        return $response;
    }
         
    // input: coupon (object), account_dbobj
    // coupon validation: validate the offer details
    
    public function setup_flashdeal_payment() {
        global $paypalconfig;
        
        $coupon = $this->params['coupon'];
        $account_dbobj = $this->params['account_dbobj'];
        
        // validate coupon: offer details
        $price_offer_type = $coupon->getPriceOfferType();
        $shipping_offer_type = $coupon->getShippingOfferType();
        $offer_details = $coupon->getOfferDetails();
        $price_percentage_off = 0;
        $shipping_percentage_off = 0;
        if($price_offer_type == PERCENTAGE_OFF) {
            $price_percentage_off = floatval($offer_details['price']['percentage_off']);
            if(empty($offer_details['price']['percentage_off']) || $price_percentage_off<=0) {
                $this->status = 1;
                $this->errnos[COUPON_PRICE_PERCENTAGE_OFF_INVALID] = 1;
                return;
            }
        }
        if($shipping_offer_type == PERCENTAGE_OFF && !empty($offer_details['shipping']['percentage_off'])) {
            $shipping_percentage_off = floatval($offer_details['shipping']['percentage_off']);
            if(empty($offer_details['shipping']['percentage_off']) || $shipping_percentage_off<=0) {
                $this->status = 1;
                $this->errnos[COUPON_SHIPPING_PERCENTAGE_OFF_INVALID] = 1;
                return;
            }
        }
      
        $store_id = $coupon->getStoreId();
        $store = new Store($account_dbobj);
        $store->findOne('id='.$store_id);
        $store_tax = $store->getTax();
        $store_name = $store->getName();
        $store_subdomin = $store->getSubdomain();
        $store_shipping = $store->getShipping();
        $store_additional_shipping = $store->getAdditionalShipping();
        $store_host = $store->getHost();
        $product_id = $coupon->getProductId();
        $store_dbobj = DbObj::getStoreDBObj($store_host, $store_id);
        
        // copy/modify the codes from setup_payment -- need to make it reusable later
        
        $product_info = StoresMapper::getProduct($product_id, $store_dbobj);
        
        $products = array(0 => array(
            'product_id' => $product_id, 
            'quantity' => 1,
            'product_name' => $product_info['name'],
            'product_price' => $product_info['price'],
            'product_shipping' => $product_info['shipping'],
            'product_url' => $product_info['board_img'],

       ));
        
        
        $account_dbobj = $this->params['account_dbobj'];

        $order_desc = 'Your Order in '.$store_name;
        
        Log::write(INFO, 'Setup payment service for products '.json_encode($products));
        
        // get merchant info
        $merchant_info = StoresMapper::getMerchantInfo($store_id, $account_dbobj);
        $merchant_username = $merchant_info['merchant_username'];
        // get paypal account
        $paypal_username = $merchant_info['merchant_paypal_username'];
        $shopinterest_paypal_username = $paypalconfig->user->email;
        Log::write(INFO, 'merchant paypal account '.$paypal_username.' shopinterest paypal account '.$shopinterest_paypal_username);
        
        
        $total = 0; // price+tax+shipping
        $price = 0; 
        $tax = 0;
        $shipping = 0;
        $total_quantity = 0;

        // create an order
        $order = new Order($store_dbobj);
        $order->save();
        Log::write(INFO, 'Created an order '.$order->getId());
        
        // payform
        $payform = array();
        
        foreach($products as $i=>$product) {
            $total_quantity = $total_quantity + $product['quantity'];
            $product_obj = new Product($store_dbobj);
            $product_obj->findOne('id='.$product['product_id']);
            $items_price = round($product_obj->getPrice()*$product['quantity']*(1-$price_percentage_off/100), 2);
            $items_shipping = round($product_obj->getShipping()*$product['quantity'], 2);
            $price = round($price + $items_price, 2);
            $shipping = round($shipping + $items_shipping, 2);        
            // creat an order item
            $order_item = new OrderItem($store_dbobj);
            $order_item->setOrderId($order->getId());
            $order_item->setProductId($product['product_id']);
            $order_item->setQuantity($product['quantity']);
            $order_item->setPrice($items_price);
            $order_item->setShipping($items_shipping);
            $order_item->save();
            
            Log::write(INFO, 'Created an order item id '.$order_item->getId().' product id '.$product['product_id'].' item price: '.$items_price.' shipping: '.$items_shipping);
            
            $payform = array_merge($payform, array(
                'L_PAYMENTREQUEST_0_NAME'.$i => $product_obj->getName(),
                'L_PAYMENTREQUEST_0_QTY'.$i => $product['quantity'],
                'L_PAYMENTREQUEST_0_AMT'.$i => $items_price
            ));
            
        }
        $shipping = round(($shipping + $store_shipping + ($total_quantity-1)*$store_additional_shipping)*(1-$shipping_percentage_off/100), 2);
        $tax = round(($price+$shipping)*$store_tax/100,2);
        $total = round($price+$tax+$shipping, 2);
        // update the order
        $order->setTotal($total);
        $order->setPrice($price);
        $order->setTax($tax);
        $order->setShipping($shipping);
        $order->save();
        Log::write(INFO, 'Saved order info '.$order->getId().' total: '.$total.' price:'.$price.' tax: '.$tax.' shipping: '.$shipping);
        
        $method = 'SetExpressCheckout';
        
        
        $payform = array_merge($payform, array(
            'NOSHIPPING' => 2,
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
            'PAYMENTREQUEST_0_AMT' => $total,
            'PAYMENTREQUEST_0_ITEMAMT' => $price,
            'PAYMENTREQUEST_0_TAXAMT' => $tax,
            'PAYMENTREQUEST_0_SHIPPINGAMT' => $shipping,
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_DESC' => $order_desc,
            'PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID' => $paypal_username,
            'PAYMENTREQUEST_0_PAYMENTREQUESTID' => getOrderPaymentRequestId($store_id, $order->getId())
        ));
        
        $fields = array(
            'RETURNURL' => getStoreUrl($store_subdomin).'/pay/congrats',
            'CANCELURL' => getURL('/flashdeals'),
            'SOLUTIONTYPE' => 'Sole',
            'ALLOWNOTE' => 1
        );
        
        $fields = array_merge($fields, $payform);

        $response = $this->expresscheckout($method, $fields);
        Log::write(INFO, 'Called paypal api '.$method.' fields: '.json_encode($fields));
        Log::write(INFO, 'Paypal api response '.json_encode($response));
        //TOKEN=EC%2d8M815023TW569412H&TIMESTAMP=2012%2d08%2d13T05%3a31%3a33Z&CORRELATIONID=27cd71438f599&ACK=Success&VERSION=65%2e0&BUILD=3435050
        if($response['ACK'] === 'Success') {
            $login_url = $paypalconfig->api->login_url;
            $fields = array(
                'cmd' => $paypalconfig->api->expresscheckout->cmd,
                'token' => $response['TOKEN']
            );
            $this->status = 0;
            $this->response['paypal_login_url'] = http_build_url2($login_url, $fields);
            $this->response['paypal_token'] = $response['TOKEN'];
            $this->response['order_id'] = $order->getId();
            $this->response['products'] = $products;
            $this->response['total'] = $total;
            $this->response['price'] = $price;
            $this->response['tax'] = $tax;
            $this->response['shipping'] = $shipping;
            $this->response['total_quantity'] = $total_quantity;
            $this->response['service_paypal_username'] = $shopinterest_paypal_username;
            $this->response['payform'] = $payform;
            $this->response['merchant_paypal_username'] = $paypal_username;
            $this->response['merchant_username'] = $merchant_username;
        } else {
            $this->status = 1;
        }
        
        // end of copy/modify the codes from setup_payment
        
    }
    
    // input: products, service_fee, store_dbobj, account_dbobj
    public function setup_payment2() {
        
        global $paypalconfig;
        
        $products = $this->params['products'];
        $service_fee = $this->params['service_fee'];
        $shopper_session = $this->params['shopper_session'];
        $store_dbobj = $this->params['store_dbobj'];
        $account_dbobj = $this->params['account_dbobj'];
        
        $store_id = $shopper_session->store_id;
        $store_tax = $shopper_session->store_tax;
        $store_name = $shopper_session->store_name;
        $store_shipping = $shopper_session->store_shipping;
        $store_additional_shipping = $shopper_session->store_additional_shipping;
        $order_desc = 'Your Order in '.$store_name;
        
        Log::write(INFO, 'Setup payment service for products '.$products);
        
        // get paypal account
        $paypal_username = $shopper_session->merchant_paypal_username;
        $shopinterest_paypal_username = $paypalconfig->user->email;
        Log::write(INFO, 'merchant paypal account '.$paypal_username.' shopinterest paypal account '.$shopinterest_paypal_username);
        
        
        $total = 0; // price+tax+shipping
        $price = 0; 
        $tax = 0;
        $shipping = 0;
        $total_quantity = 0;

        // create an order
        $order = new Order($store_dbobj);
        $order->save();
        Log::write(INFO, 'Created an order '.$order->getId());
        // create a service order
        $service_order = new ServiceOrder($account_dbobj);
        $service_order->setStoreId($store_id);
        $service_order->setOrderId($order->getId());
        $service_order->setTotal($service_fee);
        $service_order->save();
        Log::write(INFO, 'Created a service order '.$service_order->getId());
        
        // payform
        $payform = array();
        
        foreach($products as $i=>$product) {
            $total_quantity = $total_quantity + $product['quantity'];
            $product_obj = new Product($store_dbobj);
            $product_obj->findOne('id='.$product['product_id']);
            $items_price = $product_obj->getPrice()*$product['quantity'];
            $items_shipping = $product_obj->getShipping()*$product['quantity'];
            $price = $price + $items_price;
            $shipping = $shipping + $items_shipping;        
            // creat an order item
            $order_item = new OrderItem($store_dbobj);
            $order_item->setOrderId($order->getId());
            $order_item->setProductId($product['product_id']);
            $order_item->setQuantity($product['quantity']);
            $order_item->setPrice($items_price);
            $order_item->setShipping($items_shipping);
            $order_item->save();
            
            Log::write(INFO, 'Created an order item id '.$order_item->getId().' product id '.$product['product_id'].' item price: '.$items_price.' shipping: '.$items_shipping);
            
            $payform = array_merge($payform, array(
                'L_PAYMENTREQUEST_0_NAME'.$i => $product_obj->getName(),
                'L_PAYMENTREQUEST_0_QTY'.$i => $product['quantity'],
                'L_PAYMENTREQUEST_0_AMT'.$i => $product_obj->getPrice()
            ));
            
        }
        $shipping = round($shipping + $store_shipping + ($total_quantity-1)*$store_additional_shipping, 2);
        $tax = round(($price+$shipping)*$store_tax/100,2);
        $total = round($price+$tax+$shipping, 2);
        // update the order
        $order->setTotal($total);
        $order->setPrice($price);
        $order->setTax($tax);
        $order->setShipping($shipping);
        $order->save();
        Log::write(INFO, 'Saved order info '.$order->getId().' total: '.$total.' price:'.$price.' tax: '.$tax.' shipping: '.$shipping);
        
        $method = 'SetExpressCheckout';
        
        
        $payform = array_merge($payform, array(
            'NOSHIPPING' => 2,
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
            'PAYMENTREQUEST_0_AMT' => $total,
            'PAYMENTREQUEST_0_ITEMAMT' => $price,
            'PAYMENTREQUEST_0_TAXAMT' => $tax,
            'PAYMENTREQUEST_0_SHIPPINGAMT' => $shipping,
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_DESC' => $order_desc,
            'PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID' => $paypal_username,
            'PAYMENTREQUEST_0_PAYMENTREQUESTID' => getOrderPaymentRequestId($store_id, $order->getId())
        ));

        if($service_fee !== 0) {
            $payform = array_merge($payform, array(
                'PAYMENTREQUEST_1_CURRENCYCODE' => 'USD',
                'PAYMENTREQUEST_1_AMT' => $service_order->getTotal(),
                'PAYMENTREQUEST_1_PAYMENTACTION' => 'Sale',
                'PAYMENTREQUEST_1_DESC' => 'service fee',
                'PAYMENTREQUEST_1_SELLERPAYPALACCOUNTID' => $shopinterest_paypal_username,
                'PAYMENTREQUEST_1_PAYMENTREQUESTID' => getServiceOrderPaymentRequestId($service_order->getId())
            ));
            Log::write(INFO, 'Since service fee is not zero, make it a parallel payment');
        } else {
            Log::write(INFO, 'Since service fee is zero, this will be a single payment');
        }
        
        
        $fields = array(
            'RETURNURL' => getURL('/pay/thankyou'),
            'CANCELURL' => getURL(''),
            'SOLUTIONTYPE' => 'Sole',
            'ALLOWNOTE' => 1
        );
        
        $fields = array_merge($fields, $payform);
        
        $response = $this->expresscheckout($method, $fields);
        Log::write(INFO, 'Called paypal api '.$method.' fields: '.json_encode($fields));
        Log::write(INFO, 'Paypal api response '.json_encode($response));
        //TOKEN=EC%2d8M815023TW569412H&TIMESTAMP=2012%2d08%2d13T05%3a31%3a33Z&CORRELATIONID=27cd71438f599&ACK=Success&VERSION=65%2e0&BUILD=3435050
        if($response['ACK'] === 'Success') {
            $login_url = $paypalconfig->api->login_url;
            $fields = array(
                'cmd' => $paypalconfig->api->expresscheckout->cmd,
                'token' => $response['TOKEN']
            );
            $this->status = 0;
            $this->response['paypal_login_url'] = http_build_url2($login_url, $fields);
            $this->response['paypal_token'] = $response['TOKEN'];
            $this->response['order_id'] = $order->getId();
            $this->response['service_order_id'] = $service_order->getId();
            $this->response['products'] = $products;
            $this->response['total'] = $total;
            $this->response['price'] = $price;
            $this->response['tax'] = $tax;
            $this->response['shipping'] = $shipping;
            $this->response['total_quantity'] = $total_quantity;
            $this->response['service_fee'] = $service_fee;
            $this->response['service_paypal_username'] = $shopinterest_paypal_username;
            $this->response['payform'] = $payform;
            $this->response['merchant_paypal_username'] = $paypal_username;
        } else {
            $this->status = 1;
        }
    }
    
    // input: apiparams[]
    // RETURNURL
    // CANCELURL
    // SOLUTIONTYPE
    // LOGOIMG
    // ALLOWNOTE
    // NOSHIPPING: 2
    // PAYMENTREQUEST_0_CURRENCYCODE=USD
    // PAYMENTREQUEST_0_AMT=300
    // PAYMENTREQUEST_0_ITEMAMT=200 
    // PAYMENTREQUEST_0_TAXAMT=100 
    // PAYMENTREQUEST_0_SHIPPINGAMT
    // PAYMENTREQUEST_0_PAYMENTACTION=Order 
    // PAYMENTREQUEST_0_DESC=Summer Vacation trip 
    // PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID=seller-139@paypal.com 
    // PAYMENTREQUEST_0_PAYMENTREQUESTID=CART26488-PAYMENT0 
    // 
    // output: paypal login url with cmd and access token
    
    private function setExpressCheckout() {
        
        $apiparams = $this->params['apiparams'];
        
        $response = $this->expresscheckout('SetExpressCheckout', $apiparams);
        $this->response = $response;
        
        if($response['ACK'] === 'Success') {
            $login_url = $paypalconfig->api->login_url;
            $fields = array(
                'cmd' => $paypalconfig->api->expresscheckout->cmd,
                'token' => $response['TOKEN']
            );
            $this->status = 0;
            $this->response['paypal_login_url'] = http_build_url2($login_url, $fields);
        } else {
            $this->status = 1;
        }
        
        
    }
    
    // input: token
    public function get_customer_details() {
        $token = $this->params['token'];
        $response = $this->expresscheckout('GetExpressCheckoutDetails', array(
            'TOKEN'=>$token
        ));
        Log::write(INFO, 'Paypal api call '.'GetExpressCheckoutDetails'.' token: '.$token);
        Log::write(INFO, 'Paypal api call response: '.json_encode($response));
        $this->response = $response;
        if($response['ACK'] === 'Success') {
            $this->status = 0;
        } else {
            $this->status = 1;
        }
        
    }
    
    // input: apiparams
    private function getExpressCheckoutDetails($token) {
        
        $apiparams = $this->params['apiparams'];
        $response = $this->expresscheckout('GetExpressCheckoutDetails', $apiparams);
        $this->response = $response;
        
        if($response['ACK'] === 'Success') {
            $this->status = 0;
        } else {
            $this->status = 1;
        }
    }
    
    // input:
    // token
    // payer_id
    // shopper_paypal_account
    // order_payment_request_id
    // service_paypal_username
    // serviceorder_payment_request_id
    public function make_payment2() {
        $response = $this->expresscheckout('DoExpressCheckoutPayment', $this->params);
        Log::write(INFO, 'Paypal API Call DoExpressCheckoutPayment '.  json_encode($this->params));
        Log::write(INFO, 'Paypal API Call response '.json_encode($response));
        $this->response = $response;
        if($response['ACK'] === 'Success') {
            $this->status = 0;
        } else {
            $this->status = 1;
        }
        
        
    }
    
    // input: apiparams[]
    // token
    // payer_id
    // PAYMENTREQUEST_0_PAYMENTACTION=Order 
    // PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID=seller-139@paypal.com 
    // PAYMENTREQUEST_0_PAYMENTREQUESTID=ORDER-1-2
    public function doExpressCheckoutPayment($params) {
        $apiparams = $this->params['apiparams'];
        $response = $this->expresscheckout('DoExpressCheckoutPayment', $apiparams);
        $this->response = $response;
        
        if($response['ACK'] === 'Success') {
            $this->status = 0;
        } else {
            $this->status = 1;
        }
        
    }
    
    private function permissionsapi($method, $params) {
        
        global $paypalconfig;
        
        $url = $paypalconfig->api->permissions->url.'/'.$method;    
        $extra_params = array(
            'requestEnvelope.errorLanguage' => 'en_US'
        );
        $postfields = array_merge($params, $extra_params);
        
        $headers = array();
        array_push($headers, "X-PAYPAL-SECURITY-USERID: ".$paypalconfig->user->username);
        array_push($headers, "X-PAYPAL-SECURITY-PASSWORD: ".$paypalconfig->user->password);
        array_push($headers, "X-PAYPAL-SECURITY-SIGNATURE: ".$paypalconfig->user->signature);
        array_push($headers, "X-PAYPAL-REQUEST-DATA-FORMAT: NV");
        array_push($headers, "X-PAYPAL-RESPONSE-DATA-FORMAT: NV");
        array_push($headers, "X-PAYPAL-APPLICATION-ID: ".$paypalconfig->application_id);
    
        return curl_post($url, $postfields, $headers);
    }
    
     // input: apiparams
    public function requestPermissions() {
        
        $apiparams = $this->params['apiparams'];        
        $method = 'RequestPermissions';
        
        $this->response = $this->permissionsapi($method, $apiparams);
        if($this->response['ACK'] === 'Success') {
            $this->status = 0;
        } else {
            $this->status = 1;
        }
    }
    
    public function getAccessToken($request_token, $verification_code) {
        $apiparams = $this->params['apiparams'];        
        $method = 'GetAccessToken';
        
        $this->response = $this->permissionsapi($method, $apiparams);
        if($this->response['ACK'] === 'Success') {
            $this->status = 0;
        } else {
            $this->status = 1;
        }
    }

}


