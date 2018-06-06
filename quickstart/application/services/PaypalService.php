<?php

class PaypalService extends BaseService {
    
    public function __construct() {
        parent::__construct();
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
            'X-PAYPAL-APPLICATION-ID: '.$paypalconfig->api->adaptivepayments->application_id
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
        parse_str($this->response, $response_array);
        $api2 = 'SetPaymentOptions';
        $apiparams2 = array(
            'payKey' => $response_array['payKey'],
            'requestEnvelope.errorLanguage' => 'en_US',
            'senderOptions.requireShippingAddressSelection' => true
        );
        $this->adaptivepayments($api2, $apiparams2);
        
    }
    
    // input: 
    // payKey
    public function get_adaptivepay_details() {
        
        $payKey = $this->params['payKey'];
        
        // paypal call
        $api = 'PaymentDetails'; 
        $apiparams = array(
            'payKey' => $payKey,
            'requestEnvelope.errorLanguage' => 'en_US'
        );
        parse_str($this->adaptivepayments($api, $apiparams), $this->response['api_PaymentDetails']);
        
        $api2 = 'GetShippingAddresses';
        $apiparams2 = array(
            'key' => $payKey,
            'requestEnvelope.errorLanguage' => 'en_US'
        );
        parse_str($this->adaptivepayments($api2, $apiparams2), $this->response['api_GetShippingAddresses']);
    }
    
    // input:
    // user_id => xxx,
    // aid => xxx,
    // products => array(
    //     'product_id' => xxx,
    //     'quantity' => xxx,
    //     'product_url' => xxx,
    //     'product_name => xxx,
    //     'product_price' => xxx,
    //     'product_shipping' => xxx,
    // ),
    // store_info => array(
    //     'store_id' => xxx,
    //     'store_tax' => xxx,
    //     'store_name' => xxx,
    //     'store_shipping' => xxx,
    //     'store_additional_shipping' => xxx,
    //     'store_payment_solution' => xxx,
    //     'store_optin_salesnetwork' => xxx,
    //     'merchant_paypal_username' => xxx,
    // ), 
    // store_dbobj, 
    // account_dbobj
    public function setup_expresscheckout() {
        
        global $redis;
        Log::write(INFO, 'PaypalService::setup_expresscheckout '.json_encode($this->params));
        
        global $paypalconfig, $shopinterest_config;
        $percentage_transaction_fee = $shopinterest_config->percentage_transaction_fee;
        $min_transaction_fee = $shopinterest_config->min_transaction_fee;
        $paypal_shopinterest = $paypalconfig->user->email;
   
        $user_id = $this->params['user_id'];
        $user_session_id = $this->params['user_session_id'];
        $aid = !empty($this->params['aid'])?$this->params['aid']:'';
        $products = $this->params['products'];
        $store_info = $this->params['store_info'];
        $store_dbobj = $this->params['store_dbobj'];
        $account_dbobj = $this->params['account_dbobj'];
        $store_id = $store_info['store_id'];
        $store_tax = $store_info['store_tax'];
        $store_name = $store_info['store_name'];
        $store_currency_code = $store_info['store_currency_code'];
        $store_shipping = $store_info['store_shipping'];
        $store_additional_shipping = $store_info['store_additional_shipping'];
        $store_payment_solution = $store_info['store_payment_solution'];
        $store_optin_salesnetwork = $store_info['store_optin_salesnetwork'];
        $merchant_paypal_username = $store_info['merchant_paypal_username'];
        // associate
        $associate = '';
        
        if(!empty($aid) && $store_optin_salesnetwork == ACTIVATED) {
            $associate = new Associate($account_dbobj);
            $associate->findOne("aid='".$aid."' and status=".ACTIVATED);
            if($associate->getId() === 0) {
                $associate = '';
                Log::write(INFO, "The Sales Associate is not an active reseller");
            } else {
                $paypal_info = AssociatesMapper::getPaypalAccount($associate->getId(), $account_dbobj);
                $associate_paypal_username = $paypal_info['username'];
                Log::write(INFO, "The paypal account of the sales associate is $associate_paypal_username");
            }
        } else {
            Log::write(INFO, "Either there is no sales associate id or the store doesnt join the sales network");
        }

        // initialize the transaction info
        $transaction_info = array(
            'order' => array(
                'id' => 0,
                'status' => 0,
                'payment_status' => '',
                'shopper_id' => 0,
                'total' => 0,
                'price' => 0,
                'tax' => 0,
                'shipping' => 0,
                'note' => '',
                'to_name' => '',
                'to_address_id' => 0,
                'user_id' => $user_id,
                'to_email' => '',
                'to_first_name' => '',
                'to_last_name' => '',
                'currency_code' => $store_currency_code,
                'shipping_quantity' => 0,
                'products' => $products,
                'order_desc' => 'Your order in '.$store_name,
                'items' => array()
            ),
            'service_order' => array(
                'id' => 0,
                'status' => 0,
                'store_id' => $store_id,
                'order_id' => 0,
                'total' => 0
            ),
            'sales' => array(
                'total_commission_amt' => 0,
                'items' => array()
            ),
            'unavailable_products' => array(),
            'payment_solution' => $store_payment_solution,
            'user_session_id' => $user_session_id
        );
        
        $n = 0;
        $j = 0;
        $i = 0;
        Log::write(INFO, "Loop through each product and gather the info about order items & sale items");
        foreach($products as $product) {
            
            // get the product info
            $product_obj = new Product($store_dbobj);
            $product_obj->findOne('id='.$product['product_id']);
            $key = generate_product_quantity_key($store_id, $product['product_id'], $user_session_id);  
            
            $stock_quantity = $product_obj->getQuantity();     
            $product_keys = $redis->keys(get_product_quantity_keys($store_id, $product['product_id']));
            //unset the key of self, get the cached product quantity of other shopper
            foreach ($product_keys as $k => $product_key) {
                if($product_key === $key) {
                    unset($product_keys[$k]);
                }
            }            
            $holds_nums = !$redis->mget($product_keys) ? array() : $redis->mget($product_keys);
            $cache_qunatity = 0;
            foreach ($holds_nums as $value) {
                $cache_qunatity += $value;
            }
            $order_quantity = $product['quantity'];
            $available_quantity = ($stock_quantity - $cache_qunatity) > 0 ? ($stock_quantity - $cache_qunatity) : 0;

            // need to concern about product on checkout
            $checkout_quantity = $available_quantity > $order_quantity ? $order_quantity : $available_quantity;
            if($product_obj->getId() === 0 || $checkout_quantity === 0) {
                //get unavailable product info here
                if($product_obj->getId() !== 0) {
                    $transaction_info['unavailable_products'][$i] = array(
                        'product_id' => $product['product_id'],
                        'product_price' => $product_obj->getPrice(),
                        'product_name' => $product['product_name'],
                        'product_image' => $product['product_url'],
                        'product_url' => $product['product_item_url']
                    ); 
                    $i++;
                }
                continue;
            }
            $redis->set($key, $checkout_quantity);
            $redis->expire($key, ORDER_TIMEOUT);
            $transaction_info['order']['items'][$n] = array(
                'id' => 0,
                'status' => 0,
                'order_id' => 0,
                'product_id' => $product['product_id'],
                'quantity' => $checkout_quantity,
                'price' => 0,
                'shipping' => 0,
                'commission' => 0,
                'product_name' => $product['product_name'],
                'product_image' => $product['product_url'],
                'product_url' => $product['product_item_url']
            );

            Log::write(INFO, "Product info: ".  json_encode($product_obj));
            $transaction_info['order']['items'][$n]['price'] = $product_obj->getPrice();
            $transaction_info['order']['items'][$n]['shipping'] = $product_obj->getShipping();
            $transaction_info['order']['items'][$n]['commission'] = $product_obj->getCommission();

            $transaction_info['order']['shipping_quantity'] = $transaction_info['order']['shipping_quantity'] + 
            $transaction_info['order']['items'][$n]['quantity'];
            $transaction_info['order']['price'] = $transaction_info['order']['price'] + 
            $transaction_info['order']['items'][$n]['price'] * 
            $transaction_info['order']['items'][$n]['quantity'];
            $transaction_info['order']['shipping'] = $transaction_info['order']['shipping'] + 
            $transaction_info['order']['items'][$n]['shipping'] * 
            $transaction_info['order']['items'][$n]['quantity'];

            if(!empty($associate) && !empty($transaction_info['order']['items'][$n]['commission']) 
                    /*&& !empty($associate_paypal_username)*/) {
                // initialize the sales items
                $transaction_info['sales']['items'][$j] = array(
                    'id' => 0,
                    'status' => 0,
                    'associate_id' => $associate->getId(),
                    'order_id' => 0,
                    'store_id' => $store_id,
                    'product_id' => $transaction_info['order']['items'][$n]['product_id'],
                    'product_price' => $transaction_info['order']['items'][$n]['price'],
                    'product_quantity' => $transaction_info['order']['items'][$n]['quantity'],
                    'product_commission' => $transaction_info['order']['items'][$n]['commission'],
                    'product_name' => $transaction_info['order']['items'][$n]['product_name'],
                    'commission_amt' => 0
                );
                $transaction_info['sales']['items'][$j]['commission_amt'] = round(
                $transaction_info['sales']['items'][$j]['product_price'] * $transaction_info['sales']['items'][$j]['product_quantity'] *
                ($transaction_info['sales']['items'][$j]['product_commission']/100), 2);
                
                $transaction_info['sales']['total_commission_amt'] = round($transaction_info['sales']['total_commission_amt'] +
                $transaction_info['sales']['items'][$j]['commission_amt'], 2);

                $j++;
            }
            $n++;            
        }

        // calculate the tax, shipping, total of the order
        if(!empty($transaction_info['order']['shipping_quantity'])) {
            $transaction_info['order']['shipping'] = round($transaction_info['order']['shipping'] + $store_shipping + 
            ($transaction_info['order']['shipping_quantity'] - 1) * $store_additional_shipping, 2);
        }
                    
        $transaction_info['order']['tax'] = round($store_tax/100 * ($transaction_info['order']['price'] + 
                $transaction_info['order']['shipping']), 2);
        $transaction_info['order']['total'] = $transaction_info['order']['price'] +
        $transaction_info['order']['shipping'] +
        $transaction_info['order']['tax'];  
        
        // service order total
        $transaction_fee = $transaction_info['order']['total'] * ($percentage_transaction_fee/100);
        $transaction_info['service_order']['total'] = $transaction_fee > $min_transaction_fee ? round($transaction_fee, 2) : $min_transaction_fee;

        // check if this transaction brings negative income to seller
        if($transaction_info['order']['price'] <= 
                ($transaction_info['service_order']['total'] + $transaction_info['sales']['total_commission_amt'])) {
            $this->errnos[NEGATIVE_INCOME] = 1;
            $this->status = 1;
            return;
        }

        // create order
        $order = new Order($store_dbobj);
        $order->setStatus($transaction_info['order']['status']);
        $order->setPaymentStatus($transaction_info['order']['payment_status']);
        $order->setShopperId($transaction_info['order']['shopper_id']);
        $order->setTotal($transaction_info['order']['total']);
        $order->setPrice($transaction_info['order']['price']);
        $order->setTax($transaction_info['order']['tax']);
        $order->setShipping($transaction_info['order']['shipping']);
        $order->setNote($transaction_info['order']['note']);
        $order->setToName($transaction_info['order']['to_name']);
        $order->setToAddressId($transaction_info['order']['to_address_id']);
        $order->setUserId($transaction_info['order']['user_id']);
        $order->setToEmail($transaction_info['order']['to_email']);
        $order->setToFirstName($transaction_info['order']['to_first_name']);
        $order->setToLastName($transaction_info['order']['to_last_name']);
        $order->setCurrencyCode($transaction_info['order']['currency_code']);
        $order->save();
        $transaction_info['order']['id'] = $order->getId();
        Log::write(INFO, 'Order info: '.json_encode($order));
        
        // pack the payform to paypal express checkout api call
        $payform = array(
            'RETURNURL' => getSiteMerchantUrl('/pay/return'),
            'CANCELURL' => getSiteMerchantUrl('/pay/cancel'),
            'SOLUTIONTYPE' => 'Sole',
            'ALLOWNOTE' => 1,
            'NOSHIPPING' => 2
        );
        
        // create order items
        foreach($transaction_info['order']['items'] as $k => $item) {
            $order_item = new OrderItem($store_dbobj);
            $order_item->setStatus($item['status']);
            $order_item->setOrderId($transaction_info['order']['id']);
            $order_item->setProductId($item['product_id']);
            $order_item->setQuantity($item['quantity']);
            $order_item->setPrice($item['price']);
            $order_item->setShipping($item['shipping']);
            $order_item->setCommission($item['commission']);
            $order_item->save();
            $transaction_info['order']['items'][$k]['order_id'] = $order_item->getOrderId();
            $transaction_info['order']['items'][$k]['id'] = $order_item->getId();
            
            Log::write(INFO, 'order item info: '.json_encode($order_item));
            
            $payform = array_merge($payform, array(
                'L_PAYMENTREQUEST_0_NAME'.$k => $item['product_name'],
                'L_PAYMENTREQUEST_0_QTY'.$k => $item['quantity'],
                'L_PAYMENTREQUEST_0_AMT'.$k => $item['price']
            ));
            
        }
        
        // create service order
        $service_order = new ServiceOrder($account_dbobj);
        $service_order->setStatus($transaction_info['service_order']['status']);
        $service_order->setStoreId($transaction_info['service_order']['store_id']);
        $service_order->setOrderId($transaction_info['order']['id']);
        $service_order->setTotal($transaction_info['service_order']['total']);
        $service_order->save();
        $transaction_info['service_order']['id'] = $service_order->getId();
        $transaction_info['service_order']['order_id'] = $service_order->getOrderId();
        Log::write(INFO, 'Service order info: '.  json_encode($service_order));
        // create sales
        foreach($transaction_info['sales']['items'] as $m => $item) {
            $sale = new Sale($account_dbobj);
            $sale->setStatus($item['status']);
            $sale->setAssociateId($item['associate_id']);
            $sale->setOrderId($transaction_info['order']['id']);
            $sale->setStoreId($item['store_id']);
            $sale->setProductId($item['product_id']);
            $sale->setProductPrice($item['product_price']);
            $sale->setProductQuantity($item['product_quantity']);
            $sale->setProductCommission($item['product_commission']);
            $sale->setCommissionAmt($item['commission_amt']);
            $sale->save();
            $transaction_info['sales']['items'][$m]['order_id'] = $sale->getOrderId();
            $transaction_info['sales']['items'][$m]['id'] = $sale->getId();
            Log::write(INFO, 'Sale info: '.json_encode($sale));
        }
        
        $this->response['transaction_info'] = $transaction_info;

        // paypal call
        if($store_payment_solution == PROVIDER_SHOPAY) {
            $payform = array_merge($payform, array(
                    'PAYMENTREQUEST_0_CURRENCYCODE' => $store_currency_code,
                    'PAYMENTREQUEST_0_AMT' => $transaction_info['order']['total'],
                    'PAYMENTREQUEST_0_ITEMAMT' => $transaction_info['order']['price'],
                    'PAYMENTREQUEST_0_TAXAMT' => $transaction_info['order']['tax'],
                    'PAYMENTREQUEST_0_SHIPPINGAMT' => $transaction_info['order']['shipping'],
                    'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                    'PAYMENTREQUEST_0_DESC' => $transaction_info['order']['order_desc'],
                    'PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID' => $paypal_shopinterest,
                    'PAYMENTREQUEST_0_PAYMENTREQUESTID' => getOrderPaymentRequestId($store_id, $order->getId()),

                )
            );
        } else if($store_payment_solution == PROVIDER_PAYPAL) {
            $total_num_items = sizeof($transaction_info['order']['items']);
            if(empty($transaction_info['sales']['total_commission_amt'])) {
                $transaction_info['sales']['total_commission_amt'] = 0;
            }
            $payform = array_merge($payform, array(
                /* discount item to merchant */
                'L_PAYMENTREQUEST_0_NAME'.$total_num_items => 'store service fee',
                'L_PAYMENTREQUEST_0_QTY'.$total_num_items => 1,
                'L_PAYMENTREQUEST_0_AMT'.$total_num_items => (-1)*$transaction_info['sales']['total_commission_amt'] + (-1)*$transaction_info['service_order']['total'],
                /* payment to merchant */
                'PAYMENTREQUEST_0_CURRENCYCODE' => $store_currency_code,
                'PAYMENTREQUEST_0_AMT' => $transaction_info['order']['total'] + (-1)*$transaction_info['sales']['total_commission_amt'] + (-1)*$transaction_info['service_order']['total'],
                'PAYMENTREQUEST_0_ITEMAMT' => $transaction_info['order']['price'] + (-1)*$transaction_info['sales']['total_commission_amt'] + (-1)*$transaction_info['service_order']['total'],
                'PAYMENTREQUEST_0_TAXAMT' => $transaction_info['order']['tax'],
                'PAYMENTREQUEST_0_SHIPPINGAMT' => $transaction_info['order']['shipping'],
                'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                'PAYMENTREQUEST_0_DESC' => $transaction_info['order']['order_desc'],
                'PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID' => $merchant_paypal_username,
                'PAYMENTREQUEST_0_PAYMENTREQUESTID' => getOrderPaymentRequestId($store_id, $order->getId()),
                /* payment to shopinterest */
                'PAYMENTREQUEST_1_CURRENCYCODE' => $store_currency_code,
                'PAYMENTREQUEST_1_AMT' => $transaction_info['sales']['total_commission_amt'] + $transaction_info['service_order']['total'],
                'PAYMENTREQUEST_1_ITEMAMT' => $transaction_info['sales']['total_commission_amt'] + $transaction_info['service_order']['total'],
                'PAYMENTREQUEST_1_TAXAMT' => 0,
                'PAYMENTREQUEST_1_SHIPPINGAMT' => 0,
                'PAYMENTREQUEST_1_PAYMENTACTION' => 'Sale',
                'PAYMENTREQUEST_1_DESC' => 'store service fee',
                'PAYMENTREQUEST_1_SELLERPAYPALACCOUNTID' => $paypal_shopinterest,
                'PAYMENTREQUEST_1_PAYMENTREQUESTID' => getOrderPaymentRequestId($store_id, $service_order->getId()),
            ));
        }
        Log::write(INFO, 'Pay form: '.json_encode($payform));
        Log::write(INFO, 'SetExpressCheckout call');
        $this->response['api_SetExpressCheckout']['request'] = $payform;
        $response = $this->expresscheckout('SetExpressCheckout', $payform);
        Log::write(INFO, 'Express checkout response: '.json_encode($response));
        //TOKEN=EC%2d8M815023TW569412H&TIMESTAMP=2012%2d08%2d13T05%3a31%3a33Z&CORRELATIONID=27cd71438f599&ACK=Success&VERSION=65%2e0&BUILD=3435050
        if($response['ACK'] === 'Success') {
            $this->status = 0;
        } else {
            $this->status = 1;
        }
        $this->response['api_SetExpressCheckout']['response'] = $response;
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
        $store_currency = $store->getCurrency();
        if(empty($store_currency))$store_currency = "USD";
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
            'PAYMENTREQUEST_0_CURRENCYCODE' => $store_currency,
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
            'RETURNURL' => getSiteMerchantUrl('/pay/congrats'),
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
    public function setup_payment() {
        
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
    public function make_payment() {
        $response = $this->expresscheckout('DoExpressCheckoutPayment', $this->params);
        Log::write(INFO, 'Paypal API Call DoExpressCheckoutPayment '.  json_encode($this->params));
        Log::write(INFO, 'Paypal API Call response '.json_encode($response));
        $this->response = $response;

        if($response['ACK'] === 'Success' /*&& $response['PAYMENTINFO_0_PAYMENTSTATUS'] === 'Completed'*/) {
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
    
    // input: payload, test_mode, account_dbobj, job_dobj
    // log to /tmp/paypal.log using paypal_logger
    // output:
    // $this->status   -- SUCCESS/FAILURE
    // $this->response -- array
    // $this->errnos   -- array
    public function process_ipn() {
        
        $this->status = SUCCESS;
        $payload = $this->params['payload'];
        $test_mode = !empty($this->params['test_mode']);
        $account_dbobj = $this->params['account_dbobj'];
        $job_dbobj = $this->params['job_dbobj'];
        
        paypal_logger('ipn payload:'.json_encode($payload));

        if(empty($payload['ipn_track_id'])) {
            $this->status = FAILURE;
            $this->errnos[NO_IPN_TRACK_ID] = 1;
            paypal_logger('The IPN does not have the ipn_track_id. Invalid IPN.');
            return;
        }
        
        global $shopinterest_config, $paypalconfig, $dbconfig;

        paypal_logger('verifying the source of the IPN is paypal');

        if(!$test_mode) {
            $paypal_url = $paypalconfig->api->login_url;
            $cmd = $paypalconfig->api->ipn->cmd;
            $query = "cmd=$cmd&".http_build_query($payload);
            $request_url = $paypal_url.'?'.$query;
            $response = file_get_contents($request_url);
        } else {
            paypal_logger('In test mode');
            $response = 'VERIFIED';
        }
        
        paypal_logger('verification response from paypal:'.$response);
        
        if($response !== 'VERIFIED') {
            $this->status = 1;
            $this->errnos[IPN_NOT_FROM_PAYPAL] = 1;
            paypal_logger('The IPN message is not from Paypal');
            return;
        }
        
        if(empty($payload['subscr_id'])) {
            $this->status = FAILURE;
            $this->errnos[NOT_SUBSCRIPTION_IPN] = 1;
            paypal_logger('This is not a subscription IPN.');
            return;
        }

        if(empty($payload['custom'])) {
            $this->status = FAILURE;
            $this->errnos[IPN_HAS_NO_STORE_ID] = 1;
            paypal_logger('This IPN contains no store id.');
            return;
        }
        
        $store_id = $payload['custom'];
        $store = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name.'.store?id='.$store_id));
        
        paypal_logger('store info:'.json_encode($store));
        
        if(empty($store)) {
            $this->status = FAILURE;
            $this->errnos[IPN_STORE_ID_INVALID] = 1;
            paypal_logger('The store id is invalid in the IPN.');
            return;
        }
        
        paypal_logger('The source of the IPN is verified and this is a subscription IPN and has the store id '.$payload['custom']);

        // save the ipn into the subscription_ipns table
        $ipn = new SubscriptionIpn($account_dbobj);
        $ipn->setStoreId($store_id);
        $ipn->setTxnType(default2String($payload['txn_type']));
        $ipn->setSubscrId($payload['subscr_id']);
        $ipn->setFirstName($payload['first_name']);
        $ipn->setLastName($payload['last_name']);
        $ipn->setMcCurrency($payload['mc_currency']);
        $ipn->setItemName($payload['item_name']);
        $ipn->setBusiness($payload['business']);
        $ipn->setVerifySign($payload['verify_sign']);
        $ipn->setPayerStatus($payload['payer_status']);
        $ipn->setPayerEmail($payload['payer_email']);
        $ipn->setReceiverEmail(default2String($payload['receiver_email']));
        $ipn->setPayerId($payload['payer_id']);
        $ipn->setOther(json_encode($payload));
        $ipn->setTestMode($test_mode?1:0);
        $ipn->save();
        paypal_logger('IPN saved to the table subscription_ipn');

        $subscribed = $store['subscribed'];
        $subscr_id = $store['subscr_id'];
        
        $store_url = getStoreUrl($store['subdomain']);
        $service = new EmailService();            
        $service->setMethod('create_job');
        
        if($payload['txn_type'] === SubscriptionTxnTypes::subscr_signup) {
            // make sure this user hasn't subscribed the service
            if($subscribed !== '0000-00-00 00:00:00' || !empty($subscr_id)) {
                // the potential risk of double subscription
                $service->setParams(array(
                    'to' => $shopinterest_config->support->email,
                    'from' => $shopinterest_config->support->email,
                    'type' => ALERT_EMAIL,
                    'data' => array(
                        'subject' => 'POSSIBLE DUPLICATE SUBSCRIPTION',
                        'content' => "possible duplicate subscription detected for the store $store_id: $store_url"
                    ),
                    'job_dbobj' => $job_dbobj
                ));
                $service->call();
                $this->status = FAILURE;
                $this->errnos[DUPLICATE_SUBSCRIPTION] = 1;
                paypal_logger('POSSIBLE DUPLICATE SUBSCRIPTION, sent an alert email.');
                return;
            }

            // ready to mark this store as a subscriber
            $store_obj = new Store($account_dbobj, $store_id);
            $store_obj->setSubscribed(get_current_datetime());
            $store_obj->setSubscrId($payload['subscr_id']);
            $store_obj->save();
            
            $service->setParams(array(
                'to' => $shopinterest_config->support->email,
                'from' => $shopinterest_config->support->email,
                'type' => ALERT_EMAIL,
                'data' => array(
                    'subject' => 'SUBSCRIPTION SIGNUP',
                    'content' => "subscription signup for the store $store_id: $store_url"
                ),
                'job_dbobj' => $job_dbobj
            ));
            $service->call();
            
            paypal_logger('subscription signup, updated the store.');
            return;
        } else if($payload['txn_type'] === SubscriptionTxnTypes::subscr_cancel) {
            // when the user cancels the subscription, we need to do nothing
            // because the subscription is till valid for the current month
            $service->setParams(array(
                'to' => $shopinterest_config->support->email,
                'from' => $shopinterest_config->support->email,
                'type' => ALERT_EMAIL,
                'data' => array(
                    'subject' => 'SUBSCRIPTION CANCELLED',
                    'content' => "subscription cancelled for the store $store_id: $store_url"
                ),
                'job_dbobj' => $job_dbobj
            ));
            $service->call();
            paypal_logger('subscription cancelled.');
        } else if ($payload['txn_type'] === SubscriptionTxnTypes::subscr_eot) {
            $store_obj = new Store($account_dbobj, $store_id);
            $store_obj->setSubscribed('0000-00-00 00:00:00');
            $store_obj->setSubcrId('');
            $store_obj->save();
            
            $service->setParams(array(
                'to' => $shopinterest_config->support->email,
                'from' => $shopinterest_config->support->email,
                'type' => ALERT_EMAIL,
                'data' => array(
                    'subject' => 'SUBSCRIPTION TERM ENDS',
                    'content' => "subscription term ends for the store $store_id: $store_url"
                ),
                'job_dbobj' => $job_dbobj
            ));
            $service->call();
            
            paypal_logger('subscription term ends, update the store');
        } else if($payload['txn_type'] === SubscriptionTxnTypes::subscr_payment) {
            if(empty($subscr_id) || $subscribed === '0000-00-00 00:00:00') {
                $service->setParams(array(
                    'to' => $shopinterest_config->support->email,
                    'from' => $shopinterest_config->support->email,
                    'type' => ALERT_EMAIL,
                    'data' => array(
                        'subject' => 'A NON SUBSCRIBER MADE A PAYMENT',
                        'content' => "A non subscriber made a payment for the store $store_id: $store_url"
                    ),
                    'job_dbobj' => $job_dbobj
                ));
                $service->call();
                
                $this->status = FAILURE;
                $this->errnos[NON_SUBSCRIBER_MADE_PAYMENT] = 1;
                paypal_logger('A NON SUBSCRIBER MADE A PAYMENT, send an alert email');
                return;
            } else if($subscr_id !== $payload['subscr_id']) {
                $service->setParams(array(
                    'to' => $shopinterest_config->support->email,
                    'from' => $shopinterest_config->support->email,
                    'type' => ALERT_EMAIL,
                    'data' => array(
                        'subject' => 'SUBSCRIPTION PAYMENT HAS A DIFFERENT SUBSCRIBER ID',
                        'content' => "The subscription payment has a different subscription id for the store $store_id: $store_url"
                    ),
                    'job_dbobj' => $job_dbobj
                ));
                $service->call();
                
                $this->status = FAILURE;
                $this->errnos[SUBSCRIBER_ID_NOT_MATCH] = 1;
                paypal_logger('SUBSCRIPTION PAYMENT HAS A DIFFERENT SUBSCRIBER ID, send an alert email.');
                return;
            }
            // update the subscribed field only
            $store_obj = new Store($account_dbobj, $store_id);
            $store_obj->setSubscribed(get_current_datetime());
            $store_obj->save();
            
            $service->setParams(array(
                'to' => $shopinterest_config->support->email,
                'from' => $shopinterest_config->support->email,
                'type' => ALERT_EMAIL,
                'data' => array(
                    'subject' => 'SUBSCRIPTION PAYMENT SUCCEEDS',
                    'content' => "The subscription payment succeeds for the store $store_id: $store_url"
                ),
                'job_dbobj' => $job_dbobj
            ));
            $service->call();
            
            paypal_logger('subscriber made a payment, update the store');
            return;
        } else if($payload['txn_type'] === SubscriptionTxnTypes::subscr_failed) {
            $service->setParams(array(
                'to' => $shopinterest_config->support->email,
                'from' => $shopinterest_config->support->email,
                'type' => ALERT_EMAIL,
                'data' => array(
                    'subject' => 'SUBSCRIPTION PAYMENT FAILED',
                    'content' => "A subscription payment failed for the store $store_id: $store_url"
                ),
                'job_dbobj' => $job_dbobj
            ));
            
            $this->status = FAILURE;
            $this->errnos[SUBSCRIBER_PAYMENT_FAILED] = 1;
            paypal_logger('SUBSCRIPTION PAYMENT FAILED, send an alert email.');
            return;
        }
        
    }
    
}


