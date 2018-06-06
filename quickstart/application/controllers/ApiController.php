<?php

class ApiController extends BaseController {
    
    
    public function init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    // native checkout add2cart
    public function add2cartAction() {
        $return = array('status'=>'failure');
        $user_id = $this->user_session->user_id;
        $action = 'add';
        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'set'){
            $action = 'set';
        }
        //TODO
        if(isset($_REQUEST['product_id'])  && default2Int($_REQUEST['product_id'])>0 &&
        isset($_REQUEST['store_id'])  && default2Int($_REQUEST['store_id'])>0 &&
        isset($_REQUEST['quantity'])  && default2Int($_REQUEST['quantity'])!=0 &&
        isset($_REQUEST['currency']))  {
            $store_id = $_REQUEST['store_id'];
            $product_id = $_REQUEST['product_id'];
            $quantity = $_REQUEST['quantity'];
            $cf = isset($_REQUEST['custom_field']) ? $_REQUEST['custom_field'] : "";

            $qty_in_cart = CartsMapper::getProductCountInCart(
                $this->account_dbobj, $user_id, $store_id, $product_id, $cf
            );
            $qty_in_stock = ProductsMapper::getProductQuantity(
                $store_id, $product_id, $cf
            );

            if($action === 'set'){
                $quantity = $quantity - $qty_in_cart;
            }

            if($qty_in_cart + $quantity > $qty_in_stock){
                $return['errors']=array(
                    'errno' => PRODUCT_SOLDOUT,
                    'msg' => $GLOBALS['errors'][PRODUCT_SOLDOUT]['msg'],
                    'qty_in_stock' => $qty_in_stock,
                    'qty_in_cart' => $qty_in_cart,
                );
                echo json_encode($return);
                return;
            }

            $service = CartService::getInstance();
            $service->setMethod("add_product_to_cart");
            $service->setParams(array(
                "account_dbobj" => $this->account_dbobj,
                "user_id" => $this->user_session->user_id,
                "store_id" => $store_id,
                "product_id" => $product_id,
                "quantity" => $quantity,
                "currency" => $_REQUEST['currency'],
                "aid" => isset($_REQUEST['aid']) ? $_REQUEST['aid'] : "",
                "custom_field" => $cf,
            ));
            $service->call();
            $errors = $service->getErrnos();
            if($service->getStatus() === 0) {
                $return['status'] = "success";
                $return['data'] = $service->getResponse();
            }else if(isset($errors[DIFF_CURRENCY_FOR_CART])){
                $return['errors']=array(
                    'errno' => DIFF_CURRENCY_FOR_CART,
                    'msg' => $GLOBALS['errors'][DIFF_CURRENCY_FOR_CART]['msg'],
                );
            }
        } else if(isset($_REQUEST['external_id'])  && !empty($_REQUEST['external_id']) &&
        isset($_REQUEST['dealer'])  && ($_REQUEST['dealer'] == 'amazon') &&
        isset($_REQUEST['quantity'])  && default2Int($_REQUEST['quantity'])!=0){
            $store_id = 0;
            $product_id = 0;
            $quantity = $_REQUEST['quantity'];
            $cf= "";
            $dealer = $_REQUEST['dealer'];
            $external_id = $_REQUEST['external_id'];
            $qty_in_cart = CartsMapper::getProductCountInCart(
                $this->account_dbobj, $user_id, $store_id, $product_id, $cf, $dealer, $external_id
            );
            if($action === 'set'){
                $quantity = $quantity - $qty_in_cart;
            }
            $service = CartService::getInstance();
            $service->setMethod("add_product_to_cart");
            $service->setParams(array(
                "account_dbobj" => $this->account_dbobj,
                "user_id" => $this->user_session->user_id,
                "store_id" => $store_id,
                "product_id" => $product_id,
                "quantity" => $quantity,
                "dealer" => $dealer,
                "external_id" => $external_id,
                "currency" => $_REQUEST['currency'],
                "aid" => isset($_REQUEST['aid']) ? $_REQUEST['aid'] : "",
                "custom_field" => $cf,
            ));
            $service->call();
            $errors = $service->getErrnos();
            if($service->getStatus() === 0) {
                $return['status'] = "success";
                $return['data'] = $service->getResponse();
            }else if(isset($errors[DIFF_DEALER_FOR_CART])){
                $return['errors']=array(
                    'errno' => DIFF_DEALER_FOR_CART,
                    'msg' => $GLOBALS['errors'][DIFF_DEALER_FOR_CART]['msg'],
                );
            }
        }
        echo json_encode($return);
    }

    // native checkout shipping
    public function setordershippingAction() {
        $return = array('status'=>'failure');
        //TODO
        if(isset($_REQUEST['order_id'])  && default2Int($_REQUEST['order_id'])>0 &&
        isset($_REQUEST['shipping_name'])) {
            $grp = MyordersMapper::getOrderGroupByOrderId($this->account_dbobj, $_REQUEST['order_id']);
            MyordersMapper::updateOrderShipping(
                $this->account_dbobj,
                $_REQUEST['order_id'],
                $_REQUEST['shipping_name'],
                $grp['shipping_country']
            );
            MyorderGroupsMapper::updateMyorderGroupShipping($this->account_dbobj, $grp['id']);
            $return['status'] = "success";
        }
        echo json_encode($return);
    }

    // native checkout paypal confirm
    public function ncpaypalconfirmAction() {
        $return = array('status'=>'failure');
        $uid = $this->user_session->user_id;
        $myorder_grp = MyorderGroupsMapper::findOrCreateOrderGroup($this->account_dbobj, $uid);
        $service = PaypalRestService::getInstance();
        $service_method = "do_paypal_confirm";
        $params = array(
            "account_dbobj" => $this->account_dbobj,
            "user_id" => $uid,
            "order_group" => $myorder_grp,
        );
        $oid = $myorder_grp->getId();
        $params['return_url'] = getSiteMerchantUrl("/checkout/confirm?action=paypal-return&order_id=$oid&return=true");
        $params['cancel_url'] = getSiteMerchantUrl("/checkout/confirm?action=paypal-return&order_id=$oid&return=false");
        $service->setMethod($service_method);
        $service->setParams($params);
        $service->call();
        $errors = $service->getErrnos();
        if($service->getStatus() === 0) {
            $this->user_session->nc_order_checkouted = true;
            $return['status'] = "success";
            $return['data'] = $service->getResponse();
        } else {
            $return['errors']=array(
                'errno' => 0,
                'msg' => 'Paypal confirm error',
            );
        }
        echo json_encode($return);
    }

    // native checkout payment
    public function ncpaymentAction() {
        $return = array('status'=>'failure');
        $user_id = $this->user_session->user_id;
        $myorder_grp = MyorderGroupsMapper::findOrCreateOrderGroup($this->account_dbobj, $user_id);
        $paymethod = $myorder_grp->getPaymentMethod();
        $oid = $myorder_grp->getId();

        // check products quantity in stock
        $bad_products = array();
        $ret = MyorderGroupsMapper::checkProductsInStockForOrders($this->account_dbobj, $oid, $bad_products);
        if(!$ret){
            CartsMapper::deleteProductsFromCart($this->account_dbobj, $user_id, $bad_products);
            $return['errors']=array(
                'errno' => PRODUCT_SOLDOUT,
                'msg' => $GLOBALS['errors'][PRODUCT_SOLDOUT]['msg'],
                'paymethod' => $paymethod,
            );
            echo json_encode($return);
            return;
        }

        $service = PaypalRestService::getInstance();
        $service_method = null;
        $params = array(
            "account_dbobj" => $this->account_dbobj,
            "user_id" => $this->user_session->user_id,
            "order_group" => $myorder_grp,
        );

        if($paymethod == "creditcard"){ // pay by credit card
            $service_method = "do_vault_payment";
        } else if($paymethod == "paypal"){
            $service_method = "do_paypal_payment";
        }else {
            $return['errors']=array(
                'errno' => NC_UNSPPORTED_PAYMETHOD,
                'msg' => $GLOBALS['errors'][NC_UNSPPORTED_PAYMETHOD]['msg'],
                'paymethod' => $paymethod,
            );
            echo json_encode($return);
            return;
        }
        $service->setMethod($service_method);
        $service->setParams($params);
        $service->call();
        $errors = $service->getErrnos();

        if($service->getStatus() === 0) {
            $return['status'] = "success";
            $return['data'] = $service->getResponse();
            // send receipt
            NativeCheckoutService::send_receipt_email(
                $this->account_dbobj, $this->job_dbobj, $myorder_grp
            );
            // mark order and cart completed
            MyorderGroupsMapper::setMyorderGroupCompleted($this->account_dbobj, $myorder_grp->getId());
            CartsMapper::setCartCompleted($this->account_dbobj, $myorder_grp->getCartId());
        }else if(isset($errors[PAYPAL_VAULT_PAY_ERROR])){
            $return['errors']=array(
                'errno' => PAYPAL_VAULT_PAY_ERROR,
                'msg' => $GLOBALS['errors'][PAYPAL_VAULT_PAY_ERROR]['msg'],
            );
        }
        echo json_encode($return);
    }

    public function ncDisburseAction() {
        global $dbconfig;
        $return = array('status'=>'failure');
        $id =$_REQUEST['id'];
        $ck = CacheKey::q($dbconfig->account->name . ".wallet_activity?id=$id");
        $wa = BaseModel::findCachedOne($ck, array('force'=>true));
        if(!isset($wa['status']) || $wa['status'] != ACTIVATED){
            echo json_encode($return);
            return;
        }
        $disb_wa = new WalletActivity($this->account_dbobj);
        $disb_wa->setStatus(COMPLETED);
        $disb_wa->setWalletId($wa['wallet_id']);
        $disb_wa->setRefId($wa['id']);
        $disb_wa->setType('disbursement');
        $disb_wa->setCurrency($wa['currency']);
        $disb_wa->setAmount(0 - $wa['available_balance']);
        $disb_wa->setCurrentBalance(0 - $wa['available_balance']);
        $disb_wa->setAvailableBalance(0 - $wa['available_balance']);
        $disb_wa->save();

        $ori_wa = new WalletActivity($this->account_dbobj, $wa['id']);
        $ori_wa->setStatus(COMPLETED);
        $ori_wa->save();

        WalletsMapper::updateWallet($this->account_dbobj, $wa['wallet_id']);

        $return['status'] = "success";
        echo json_encode($return);
    }

    public function applyCouponAction() {
        $return = array('status'=>'failure');
        $coupon_code = $_REQUEST['coupon'];
        $user_id = $this->user_session->user_id;

        $coupon = MycouponsMapper::getAvailableCoupon($coupon_code, $user_id, $this->account_dbobj);
        if(empty($coupon)){ // coupon is not available
            echo json_encode($return);
            return;
        }
        $ret = CartsMapper::applyCouponToCurrentCart($this->account_dbobj, $coupon, $user_id);
        if($ret) {
            $return['status'] = "success";
            $return['data'] = array(
                'coupon'=> $coupon,
                'info' => $ret,
            );
        }
        echo json_encode($return);
    }

    public function clearCouponAction() {
        $return = array('status'=>'failure');
        $coupon_code = $_REQUEST['coupon'];
        $user_id = $this->user_session->user_id;

        $ret = CartsMapper::clearCouponOfCurrentCart($this->account_dbobj, $coupon_code, $user_id);
        if($ret) {
            $return['status'] = "success";
        }
        echo json_encode($return);
    }

    public function fulfillOrderAction(){
        global $dbconfig, $shopinterest_config;

        $uid = $this->user_session->user_id;
        $return = array('status'=>'failure');
        $order = $_REQUEST['order'];
        $order_id = $order['order_id'];

        $ck = CacheKey::q($dbconfig->account->name . ".myorder?id=" . $order['order_id']);
        $myorder_model = BaseModel::findCachedOne($ck);
        if($myorder_model['store_id'] != $this->store['id']){
            echo json_encode($return);
            return;
        }

        $provider = $order['shipping_provider'];
        if($provider === 'other') {
            $provider = $order['extra_shipping_provider'];
        }
        $myorder = new Myorder($this->account_dbobj, $order['order_id']);
        $myorder->setShippingServiceProvider($provider);
        $myorder->setTrackingNumber($order['tracking_number']);
        $myorder->setExpectedArrivalDate($order['expected_arrival_date']);
        $myorder->setShippingDate($order['shipping_date']);
        $myorder->setPaymentStatus(ORDER_SHIPPED);
        $myorder->save();

        // update wallet for merchant
        $wa = new WalletActivity($this->account_dbobj);
        $wa->findOne(" type = 'sale' and ref_id = " . $order_id);
        if($wa->getId() > 0){
            if($wa->getCurrentBalance() < 100 || CreditCardsMapper::hasVerifiedCreditCard($uid)){
                $wa->setAvailableBalance($wa->getCurrentBalance());
                $wa->setStatus(ACTIVATED);
                $wa->save();
                WalletsMapper::updateWallet($this->account_dbobj, $wa->getWalletId());
            }
        }
        // update wallet for assoc
        $resell_was = WalletsMapper::getCommissionWalletActivitiesForOrder($this->account_dbobj, $order_id);
        foreach($resell_was as $rwa){
            $wa = new WalletActivity($this->account_dbobj, $rwa['id']);
            $wa->setStatus(ACTIVATED);
            $wa->setAvailableBalance($wa->getCurrentBalance());
            $wa->save();
            WalletsMapper::updateWallet($this->account_dbobj, $wa->getWalletId());
        }

        $user_ck = CacheKey::q($dbconfig->account->name.'.user?id='.$myorder_model['user_id']);
        $user = BaseModel::findCachedOne($user_ck);
        $store_ck = CacheKey::q($dbconfig->account->name.'.store?id='.$myorder_model['store_id']);
        $store = BaseModel::findCachedOne($store_ck);
        $service = new EmailService();
        $service->setMethod('create_job');
        $service->setParams(array(
            'to' => $user['username'],
            'from' => $shopinterest_config->support->email,
            'type' => SHOPPER_SHIPPING_NOTIFICATION,
            'data' => array(
                'site_url' => getURL(),
                'store_url' => getStoreUrl($store['subdomain']),
                'order_num' => $myorder_model['order_num'],
                'shipping_service_provider' => $myorder->getShippingServiceProvider(),
                'tracking_number' => $myorder->getTrackingNumber(),
                'expected_arrival_date' => $myorder->getExpectedArrivalDate()
            ),
            'job_dbobj' => $this->job_dbobj,
        ));
        $service->call();
        
        echo json_encode(array('status'=>'success'));
    }

    public function cancelOrderAction(){
        global $dbconfig, $shopinterest_config;

        $uid = $this->user_session->user_id;
        $return = array('status'=>'failure');
        $order = $_REQUEST['order'];
        if(!isset($order['order_id'])){
            echo json_encode($return);
            return;
        }
        $order_id = $order['order_id'];

        $service = new MyorderService();
        $service->setMethod('cancel_order');
        $service->setParams(array(
            'is_admin' => $this->is_admin(),
            'order_id' => $order_id,
            'store' => $this->store,
            'account_dbobj' => $this->account_dbobj,
            'job_dbobj' => $this->job_dbobj,
        ));
        $service->call();

        echo json_encode(array('status'=>'success'));
    }

    public function deleteProductAction() {

        $product_id = default2Int($_REQUEST['product_id']);

        if(!empty($product_id)) {
            $product = new Product($this->store_dbobj, $product_id);
            $product->setStatus(DELETED);
            $product->save();
        }

        // clear images
        $store_id = $this->store['id'];
        $prefix = cloudinary_store_product_ns($store_id, $product_id);
        try{
            $api = new \Cloudinary\Api();
            $api->delete_resources_by_prefix($prefix);
        }catch(Exception $e){
        }
        echo json_encode(array('status'=>'success'));
    }
    
    // input:
    // $_COOKIE
    // output:
    // $this->shopper_session->order->order_id
    // $this->shopper_session->order->service_order_id
    // ...
    public function checkoutAction() {
        
        global $paypalconfig;
        
        $user_id = empty($this->user_session->user_id)?0:$this->user_session->user_id;
        $user_shopper_id = empty($this->user_session->user_id)?0:$this->user_session->user_id;
        
        $order = !empty($_COOKIE['order'])?json_decode($_COOKIE['order'], true):array();

        if(empty($order['products'])) {
            echo json_encode(array(
                'status' => 'failure',
                'errors' => array(
                    'errno' => NO_PRODUCT_SELECTED,
                    'msg' => $GLOBALS['errors'][NO_PRODUCT_SELECTED]['msg']
                )
            ));
            return;
        }
       
        $products = $order['products'];

        $service = new PaypalService();
        $service->setMethod('setup_expresscheckout');
        $service->setParams(array(
            'user_id' => $user_id,
            'user_session_id' => session_id(),
            'aid' => isset($order['aid'])?$order['aid']:'',
            'products' => $products,
            'store_info' => array(
                'store_id' => $this->shopper_session->store_id,
                'store_tax' => $this->shopper_session->store_tax,
                'store_currency_code' => $this->shopper_session->store_currency,
                'store_name' => $this->shopper_session->store_name,
                'store_shipping' => $this->shopper_session->store_shipping,
                'store_additional_shipping' => $this->shopper_session->store_additional_shipping,
                'store_optin_salesnetwork' => $this->shopper_session->store_optin_salesnetwork,
                'store_payment_solution' => $this->shopper_session->store_payment_solution,
                'merchant_paypal_username' => $this->shopper_session->merchant_paypal_username,
            ),
            'store_dbobj' => $this->store_dbobj,
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();

        if($service->getStatus() === 0) {
            $response = $service->getResponse();
            $this->shopper_session->transaction = $response;
            $paypal_login_url = $paypalconfig->api->login_url.'?cmd='.$paypalconfig->api->expresscheckout->cmd.'&token='.$this->shopper_session->transaction['api_SetExpressCheckout']['response']['TOKEN'];     
            Log::write(INFO, 'SESSION: '.json_encode($_SESSION));
            echo json_encode(array(
                'status' => 'success',
                'paypal_login_url' => $paypal_login_url 
            ));
        } else {
            echo json_encode(array(
                'status' => 'failure',
                'errors' => array(
                    'errno' => INITIATE_PAYPAL_FAILURE,
                    'msg' => $GLOBALS['errors'][INITIATE_PAYPAL_FAILURE]['msg']
                )
            ));
        }
        
        // post fields:
        // name
        // price
        // quantity
        // shipping
        // tax&service
        // total
        
        
        
    }

    // input:
    // $_COOKIE
    // output:
    // $this->shopper_session->order->order_id
    // $this->shopper_session->order->service_order_id
    // ...
    public function paypalAction() {
        Log::write(INFO, 'SESSION: '.json_encode($_SESSION));
        Log::write(INFO, 'COOKIE: '.json_encode($_COOKIE));
        
        $order = !empty($_COOKIE['order'])?json_decode($_COOKIE['order'], true):array();

        if(empty($order['products'])) {
            echo json_encode(array(
                'status' => 'failure',
                'errors' => array(
                    'errno' => NO_PRODUCT_SELECTED,
                    'msg' => $GLOBALS['errors'][NO_PRODUCT_SELECTED]['msg']
                )
            ));
            Log::write(INFO, 'no product selected so just return');            
            return;
        }
       
        $products = $order['products'];
        $service_waived = $order['service_waived'];
        global $transaction_config;
        
        $service_fee = $transaction_config->service_fee;
        if(!empty($service_waived)) {
            $service_fee =  0;
        }
        $service = PaypalService::getInstance();
        $service->setMethod('setup_payment');
        $service->setParams(array(
            'products' => $products,
            'service_fee' => $service_fee,
            'shopper_session' => $this->shopper_session,
            'store_dbobj' => $this->store_dbobj,
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
       
        if($service->getStatus() === 0) {
            $response = $service->getResponse();
            $this->shopper_session->order->order_id = $response['order_id'];
            $this->shopper_session->order->service_order_id = $response['service_order_id'];
            $this->shopper_session->order->products = $response['products'];
            $this->shopper_session->order->total = $response['total'];
            $this->shopper_session->order->price = $response['price'];
            $this->shopper_session->order->tax = $response['tax'];
            $this->shopper_session->order->shipping = $response['shipping'];
            $this->shopper_session->order->total_quantity = $response['total_quantity'];
            $this->shopper_session->order->service_fee = $response['service_fee'];
            $this->shopper_session->order->paypal->token = $response['paypal_token'];
            $this->shopper_session->order->service_paypal_username = $response['service_paypal_username'];
            $this->shopper_session->order->paypal->payform = $response['payform'];
            $this->shopper_session->order->merchant_paypal_username = $response['merchant_paypal_username'];
            
            Log::write(INFO, 'SESSION: '.json_encode($_SESSION));
            
            echo json_encode(array(
                'status' => 'success',
                'paypal_login_url' => $response['paypal_login_url'] 
            ));
            Log::write(INFO, 'setup payment succeeded, paypal login_url '.$response['paypal_login_url']);
        } else {
            echo json_encode(array(
                'status' => 'failure',
                'errors' => array(
                    'errno' => INITIATE_PAYPAL_FAILURE,
                    'msg' => $GLOBALS['errors'][INITIATE_PAYPAL_FAILURE]['msg']
                )
            ));
            Log::write(INFO, 'initiate paypal payment fails');
        }
        
        // post fields:
        // name
        // price
        // quantity
        // shipping
        // tax&service
        // total
        
        
        
    }

    public function updateorderAction() {
        
        $response = array('status'=>'failure');
        if(empty($_REQUEST['order_id']) ||
                !validate($_REQUEST['arrival_date'], 'date')) {
            echo json_encode($response);
            return;
        }

        global $shopinterest_config, $redis;
        
        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        $store_subdomain = $redis->get("store:$store_id:subdomain");
        $store_url = getStoreUrl($store_subdomain);
        
        $order_id = $_REQUEST['order_id'];
        $provider = empty($_REQUEST['other_provider']) ? $_REQUEST['provider'] : $_REQUEST['other_provider'];
        $track_number = $_REQUEST['track_number'];
        $arrival_date = $_REQUEST['arrival_date'];
        
        $order = new Order($this->store_dbobj);
        $order->findOne('id='.$order_id);
        $new_order_id = $order->getId();
        if(!empty($new_order_id)) {
            $order->setStatus(SHIPPED);
            $order->setShippingServiceProvider($provider);
            $order->setTrackingNumber($track_number);
            $order->setExpectedArrivalDate($arrival_date);
            $order->save();
            Log::write(INFO, "Update the order $new_order_id status to SHIPPED");
            
            // send an email to the shopper -- feedback
            $shopper_id = $order->getUserId();
            $shopper = new User($this->account_dbobj);
            $shopper->findOne('id='.$shopper_id);
            $service = new EmailService();            
            $service->setMethod('create_job');
            $service->setParams(array(
                'to' => $shopper->getUsername(),
                'from' => $shopinterest_config->support->email,
                'type' => SHOPPER_SHIPPING_NOTIFICATION,
                'data' => array(
                    'site_url' => getURL(),
                    'store_id' => $store_id, 
                    'order_id' => $order->getId(),
                    'shipping_service_provider' => $order->getShippingServiceProvider(),
                    'tracking_number' => $order->getTrackingNumber(),
                    'expected_arrival_date' => $order->getExpectedArrivalDate(),
                    'store_url' => $store_url
                ),
                'job_dbobj' => $this->job_dbobj
            ));
            $service->call();
   
            $response['status'] = "success";
        }
        echo json_encode($response);
        return;        
        
    }
    
    // input: email 
    public function resetpassAction() {

        $return = 'failure';
        $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
        
        if(empty($email) || !validate($email, 'email')) {
            echo $return;
            return;
        }

        $service = new AccountsService();
        $service->setMethod('reset_password');
        $service->setParams(array(
            'email' => $email,
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();

        if($service->getStatus() ===0) {

            $response = $service->getResponse();
            // send an email
            global $shopinterest_config;

            $service = new EmailService();
            $service->setMethod('create_job');
            $service->setParams(array(
                'to' => $email,
                'from' => $shopinterest_config->support->email,
                'type' => USER_FORGET_PASSWORD,
                'data' => array(
                    'site_logo' => getSiteMerchantUrl(SHOPINTEREST_LOGO),
                    'site_url' => getURL(),
                    'user_password' => $response['password'],
                    'password' => $response['password'],
                    'support_email' => $shopinterest_config->support->email
                ),
                'job_dbobj' => $this->job_dbobj
            ));
            $service->call();
            $return = 'success';
        }

        echo $return;
    }
    
//    public function storesearchAction(){
//        $content = $_GET['q'];
//        $account_dbobj = $this->account_dbobj;
//        
//        $service = StoreService::getInstance();
//        $service->setMethod('get_store_by_name_or_tag');
//        $service->setParams(array(
//                'content' => $content,
//                'account_dbobj' => $this->account_dbobj
//            ));
//        $service->call();
//        
//        $res = $service->getResponse();
//        echo $this->_helper->json($res);
//    }
    
     //input page_num query
    //output status:true or false
    //       products array
    public function productsearchAction(){
        $response = array('status' => false,'products'=>array());
        $offset=0;
        
        if(isset($_REQUEST['page_num'])){
            $offset=$_REQUEST['page_num'];
        }
        $query=$_REQUEST['query'];
        
        $limit=PRODUCT_NUM_PER_PAGE;
        $service = SphinxService::getInstance();
        $service->setMethod('query');
        $service->setParams(array(
            'query'=>$query,
            'account_dbobj'=>$this->account_dbobj,
            'limit'=>$limit,
            'offset'=>$offset
            ));
        $service->call();
        $status = $service->getStatus();
        if($status!==0){
            echo json_encode($response);
            return;
        }
        $products = $service->getResponse();
        $service->destroy();
        
        $response['status'] = true;
        $response['products']=$products;
        echo json_encode($response);
    }
    
    public function savecategoryAction() {
       if(empty($_REQUEST['category']) || strlen($_REQUEST['category'])>25) {
            echo json_encode(array('status'=>'failure'));
            return;
        }
        $category = $_REQUEST['category'];
        $store_dbobj = $this->store_dbobj;
        $category_obj = new Category($store_dbobj);
        if(!$category_obj->setCategory($category)) {
            echo json_encode(array('status'=>'failure'));
            return;
        }
        $category = $category_obj->getCategory();
        $category_obj->findOne("category='".$store_dbobj->escape($category)."'");
        if($category_obj->getId() !== 0) {
            echo json_encode(array('status'=>'failure'));
            return;
        }
        $category_obj->setCategory($category);
        $category_obj->setDescription($_REQUEST['category']);
        $category_obj->save();
        echo json_encode(array('status'=>'success', 'id'=>$category_obj->getId()));
    }
    
    public function deletecategoryAction() {
        $status = 'failure';
        if(empty($_REQUEST['category'])) {
            echo $status;
            return;
        }
        
        $category = sanitize_string($_REQUEST['category']);

        if(!empty($category)){
             StoresMapper::deleteCategory($category, $this->store_dbobj);
             $status = 'success';
        }
        echo $status;
    }
    
    public function updatepasswordAction() {
        global $redis;
        $user_id = $this->user_session->user_id;
        
        $response = 'failure';
        
        if(!empty($_REQUEST) && !empty($_REQUEST['current_password']) && !empty($_REQUEST['new_password']) &&
                !empty($_REQUEST['confirm_password'])) {
            $current_password = $_REQUEST['current_password'];
            $new_password = $_REQUEST['new_password'];
            $confirm_password = $_REQUEST['confirm_password'];            
           
            //input : current_password, new_password, confirm_password, account_dbobj, role, identity_id            
            $service = AccountsService::getInstance();
            $service->setMethod('update_password');
            $service->setParams(array(
                'current_password' => $current_password,
                'new_password' => $new_password,
                'confirm_password' =>$confirm_password,
                'account_dbobj' => $this->account_dbobj,
                'user_id' => $user_id
            ));
            $service->call();           
            $status = $service->getStatus();
            
            if($status === 0) {
                $response = 'success';            
            }
        }       
        echo $response;
    }

    // usage: http://host/api/combo?f=file1;file2;file3
    public function comboAction() {
        $comboJS = '';
        $cwd = getcwd();
        $files = explode(';', $_REQUEST['f']);
        foreach($files as $file)
        {
            if(strpos("..", $file)) continue;
            if(!preg_match("/\.(js|css|html)$/", $file)) continue;
            $abs_path = $cwd . "/" . $file;
            if(!file_exists($abs_path))continue;
            $comboJS .= file_get_contents($abs_path);
        }
        echo $comboJS;
    }

    public function testAction() {
        
        //$access_token = $_REQUEST['access_token'];
        $access_token = 'AAADA9xz5kzUBABlEnMcGwdtED2hHtWxUQ2qhSanwBXR6GBgjzwoB4UpcnmEV7RZCS78lVGbu1ZBIE1K0LwWFsMMfbcZBW2VZC70hdDuKQFlhpfHYePEJ';
        $this->facebook->setAccessToken($access_token);
        try {

            $user_profile = $this->facebook->api('/me','GET');
            echo "Name: " . $user_profile['name'];

        } catch(FacebookApiException $e) {
            echo "exception";
            dddd($e);
        }
//        dddd("https://graph.facebook.com/oauth/access_token?  
//            grant_type=fb_exchange_token&           
//            client_id={$shopinterest_config->facebook->app_id}&
//            client_secret={$shopinterest_config->facebook->secret}&
//            fb_exchange_token=$access_token");
        //dddd(file_get_contents("https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id={$shopinterest_config->facebook->app_id}&client_secret={$shopinterest_config->facebook->secret}&fb_exchange_token=$access_token")); 
    }

    // input: pinterest_email, pinterest_password
    // return:
    // - status: true/false
    // - data: array('account'=>array('id'=>...,), 'boards'=>)
    public function ploginAction() {
        $response = array('status' => false, 'data' => array());
        
        global $pinterest_config;
        $max_allowed_job = $pinterest_config->max_allowed_jobs;
        $store_dbobj = $this->store_dbobj;
        if(StoresMapper::getCurrentScheduledJobsCount($store_dbobj, PIN_STORE_PRODUCTS) >= $max_allowed_job) {
            $response['data']['error_msg'] = 'Currently you can only upload once each day';
            echo json_encode($response);
            return;
        }

        if(empty($_REQUEST['pinterest_email']) || empty($_REQUEST['pinterest_password']) ||
                (!empty($_REQUEST['pinterest_email']) && !validate($_REQUEST['pinterest_email'], 'email'))) {
            $response['data']['error_msg'] = 'Login Failed';
            echo json_encode($response);
            return;
        }

        $pinterest_email = trim($_REQUEST['pinterest_email']);
        $pinterest_password = trim($_REQUEST['pinterest_password']);
        Log::write(INFO, "pinterest email: $pinterest_email");
        
        // input: pinterest_email, pinterest_password, account_dbobj, job_dbobj
        $service = new PinstoreService();
        $service->setMethod('login');
        $service->setParams(array(
            'pinterest_email' => $pinterest_email,
            'pinterest_password' => $pinterest_password,
            'account_dbobj' => $this->account_dbobj,
            'job_dbobj' => $this->job_dbobj
        ));
        $service->call();

        if($service->getStatus() !== 0) {
            $response['data']['error_msg'] = 'Login Failed';
            echo json_encode($response);
            return;
        } else {
            // save logged in info into the session
            $response['status'] = true;
            $response['data'] = $service->getResponse();
            $this->user_session->pinterest_email = $pinterest_email;
            $this->user_session->pinterest_password = $pinterest_password;
            $this->user_session->pinterest_account_id = $response['data']['account']['id'];
            $response_encoded = json_encode($response);
            Log::write(INFO, 'response: '.$response_encoded);
            echo $response_encoded;
            return;
        }
    }
    
    // input: pinterest_boardname or pinterest_board_id
    // return:
    // - status: true/false
    public function uploadpinsAction() {
        
        global $redis;
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        
        $response = array('status' => false);
        
        // authenticate the request first
        if(empty($this->user_session->pinterest_email) ||
        empty($this->user_session->pinterest_password) ||
        empty($this->user_session->pinterest_account_id) ||
        (empty($_REQUEST['pinterest_boardname']) && empty($_REQUEST['pinterest_board_id']))) {
            echo json_encode($response);
            return;
        }
        
        Log::write(INFO, 'authenticated uploadpins');
        
        // call the PinstoreService::upload_pins
        $service = new PinstoreService();
        $service->setMethod('upload_pins');
        $service->setParams(array(
            'pinterest_email' => $this->user_session->pinterest_email,
            'pinterest_password' => $this->user_session->pinterest_password,
            'pinterest_account_id' => $this->user_session->pinterest_account_id,
            'pinterest_boardname' => empty($_REQUEST['pinterest_boardname'])?'':$_REQUEST['pinterest_boardname'],
            'pinterest_board_id' => empty($_REQUEST['pinterest_board_id'])?'':$_REQUEST['pinterest_board_id'],
            'store_id' => $store_id,
            'store_dbobj' => $this->store_dbobj,
            'account_dbobj' => $this->account_dbobj,
            'job_dbobj' => $this->job_dbobj
        ));
        $service->call();
        if($service->getStatus() === 0) {
            $response = array('status' => true);
        }
        echo json_encode($response);
    }
    
    // input: toemail, subject, content [replyto]
    // output:
    // - status: success/failure
    // - data: {errors: [{errno:xxx, msg:'xxx'}, ...], ...}
    public function sendemailAction() {
        
        $user_id = $this->user_session->user_id;
        $username = $this->user['username'];
        $first_name = $this->user['first_name'];
        $last_name = $this->user['last_name'];
        $store_name = $this->store['name'];
        
        $response = array('status'=>'failure', 'data' => array('errors' => array()));
        
        $toemail_array = explode(',', $_REQUEST['toemail']); 
        $toname_array = explode(',', $_REQUEST['toname']);     
        $num_emails = sizeof($toemail_array);
        
        foreach ($toemail_array as $toemail) {
            if(empty($toemail) || !validate($toemail, 'email')) {
                array_push(
                    $response['data']['errors'],
                    array('errno' => INVALID_EMAIL, 'msg' => $this->view->errors[INVALID_EMAIL]['msg'])
                );
                echo json_encode($response);
                return;
            }
        }
        
        if(empty($_REQUEST['subject'])) {
            array_push(
                $response['data']['errors'],
                array('errno' => INVALID_EMAIL_SUBJECT, 'msg' => $this->view->errors[INVALID_EMAIL_SUBJECT]['msg'])
            );
            echo json_encode($response);
            return;
        }
        
        if(empty($_REQUEST['text'])) {
            array_push(
                $response['data']['errors'],
                array('errno' => INVALID_EMAIL_CONTENT, 'msg' => $this->view->errors[INVALID_EMAIL_CONTENT]['msg'])
            );
            echo json_encode($response);
            return;
        }

        $store_dbobj = $this->visit_store_dbobj;
        if(empty($store_dbobj)){
            $store_dbobj = $this->store_dbobj;
        }
        if(empty($store_dbobj) && !empty($this->user_session->visit_store_id)){
            $store_dbobj = DBObj::getStoreDBObjById($this->user_session->visit_store_id);
        }
        // send email for shopper
        if(isset($_REQUEST['replyto'])){
            $fromname = 'Shopper';
            $replyto=$_REQUEST['replyto'];            
            if(!validate($replyto, "email")) {
                array_push(
                    $response['data']['errors'],
                    array('errno' => INVALID_EMAIL, 'msg' => $this->view->errors[INVALID_EMAIL]['msg'])
                );
                echo json_encode($response);
                return;
            }
        } else {
            global $shopinterest_config;
            $max_allowed_customer_emails = $shopinterest_config->store->max_allowed_customer_emails;
            if(!empty($store_dbobj) &&
            StoresMapper::getCurrentScheduledJobsCount($store_dbobj, EMAIL_SENDER) + $num_emails >= $max_allowed_customer_emails) {
                array_push(
                    $response['data']['errors'],
                    array(
                        'errno' => EXCEED_MAX_ALLOWED_CUSTOMER_EMAILS,
                        'msg' => $this->view->errors[EXCEED_MAX_ALLOWED_CUSTOMER_EMAILS]['msg']
                    )
                );
                echo json_encode($response);
                return;
            }
            $fullname = trim($first_name.' '.$last_name);
            $fromname = empty($fullname)?$store_name:$fullname;
            $replyto = $username;
        }
        
        // input: to, toname, from, fromname, replyto, subject, text, type, data, job_dbobj
        // create an email job
        $subject = $_REQUEST['subject'];
        $text = nl2br($_REQUEST['text']);
        $from = 'xxx@shopinterest.co';
        
        $service = new EmailService();
        $service->setMethod('create_job');   
        
        foreach($toemail_array as $i => $toemail) { 
            $service->setParams(array(
                'to' => $toemail,
                'from' => $from,
                'subject' => $subject,
                'text' => $text,
                'toname' => $toname_array[$i],
                'fromname' => $fromname,
                'replyto' => $replyto,        
                'job_dbobj' => $this->job_dbobj
            ));
            $service->call();
            $service_response = $service->getResponse();

            // create a record in scheduled_jobs
            if(!empty($store_dbobj)){
                $scheduled_job = new ScheduledJob($store_dbobj);
                $scheduled_job->setType(EMAIL_SENDER);
                $scheduled_job->setJobId($service_response['job_id']);
                $scheduled_job->save();
            }
        }

        $response['status'] = 'success';
        echo json_encode($response);
    }

    public function sendEmailToUsAction() {
        $return = array('status'=>'failure');

        $from_email = $_REQUEST['email'];
        $from_name =  $_REQUEST['name'];
        $subject = $_REQUEST['subject'];
        $text = $_REQUEST['text'];
        $type =  $_REQUEST['type'];

        if($type == 'shopbuilder'){
            $subject = "[ShopBuilder Request] " . $subject;
        } else {
            echo json_encode($return);
            return;
        }

        $service = new EmailService();
        $service->setMethod('create_job');

        $service->setParams(array(
            'from' => $from_email,
            'fromname' => $from_name,
            'replyto' => $from_email,
            'to' => 'xxx@shopinterest.co',
            'toname' => "ShopIntoit Team",
            'subject' => $subject,
            'text' => $text,
            'job_dbobj' => $this->job_dbobj
        ));
        $service->call();
        $service_response = $service->getResponse();

        $return['status'] = "success";
        echo json_encode($return);
    }

    // input:
    // output:
    // - status: success/failure
    // - data: {errors: [0=>{errno: xxx; msg: ""}, ...], ...}
    public function addcustomerAction() {
        
        $response = array('status'=>'failure', 'data' => array('errors' => array()));
        
        if(isset($_REQUEST['contacts'])){
            $contacts = json_decode($_REQUEST['contacts'], true);
            if(!is_array($contacts)) {
                $response['data']['errors'][0]['errno'] = CONTACTS_IMPORT_ERROR;
                $response['data']['errors'][0]['msg'] = $this->error[CONTACTS_IMPORT_ERROR]['msg'];
                echo json_encode($response);
                return;
            }
            $service = new MerchantService();
            $service->setMethod('save_contacts');
            $service->setParams(array(
                'contacts' => $contacts,
                'store_dbobj' => $this->store_dbobj
            ));
            $service->call();
            $response['status'] = 'success';
            $response['data'] = $service->getResponse();
        }
        echo json_encode($response);
        
    }
    
    // input: customer_id
    // ouput:
    // - status: success/failure
    // - data: {errors: [0=>{errno: xxx; msg: ""}, ...], ...}
    public function deletecustomerAction() {
        
        $response = array('status'=>'failure', 'data' => array('errors' => array()));
        
        if(!empty2($customer_id = $_REQUEST['customer_id'])) {            
            $customer = new Customer($this->store_dbobj, $customer_id);
            $customer->setStatus(DELETED);
            $customer->save();
            $response['status'] = 'success';
        }
        echo json_encode($response);
    }
    
    // input:
    //    Array
    //    (
    //        [0] => stdClass Object
    //            (
    //                [address] => Array
    //                    (
    //                    )
    //
    //                [last_name] => Auerbach
    //                [phone] => Array
    //                    (
    //                    )
    //
    //                [first_name] => Steve
    //                [email] => Array
    //                    (
    //                        [0] => stdClass Object
    //                            (
    //                                [address] => sauerbach@peakhosting.com
    //                                [type] => 
    //                                [selected] => 1
    //                            )
    //
    //                    )
    //
    //            )
    //
    //
    //    )
    // ouput:
    // - status: success/failure
    // - data: {errors: [0=>{errno: xxx; msg: ""}, ...], ...}
    public function importcontactsAction() {
        $response = array('status'=>'failure', 'data' => array('errors' => array()));
        
        if(isset($_REQUEST['contacts'])){
            
            $contacts = json_decode($_REQUEST['contacts'], true);
            
            if(!empty($_REQUEST['manual'])) {
                // check if it is an valid email
                $contact_email = $contacts[0]['email'][0]['address'];
                if(!validate($contact_email, 'email')) {
                    $response['data']['errors'][0]['errno'] = INVALID_EMAIL;
                    $response['data']['errors'][0]['msg'] = $this->view->errors[INVALID_EMAIL]['msg'];
                    echo json_encode($response);
                    return;
                }
            }
            
            if(!is_array($contacts)) {
                $response['data']['errors'][0]['errno'] = CONTACTS_IMPORT_ERROR;
                $response['data']['errors'][0]['msg'] = $this->error[CONTACTS_IMPORT_ERROR]['msg'];
                echo json_encode($response);
                return;
            }
            $service = new MerchantService();
            $service->setMethod('save_contacts');
            $service->setParams(array(
                'contacts' => $contacts,
                'store_dbobj' => $this->store_dbobj
            ));
            $service->call();
            $response['status'] = 'success';
            $response['data'] = $service->getResponse();
        }
        echo json_encode($response);
    }
    
    function adminsavecategoryAction(){
        
        if(empty($_REQUEST['category']) || !GlobalCategoriesMapper::saveCategory($_REQUEST['category'], $this->account_dbobj)) {
            $response = array('status'=>'failure');
        } else {
            $response = array('status'=>'success');
        }
        echo json_encode($response);
    }
    
    
    public function admindeletecategoryAction(){
        
        $account_dbobj = $this->account_dbobj;
        if(empty($_REQUEST['id']) || !GlobalCategoriesMapper::deleteCategory($_REQUEST['id'], $account_dbobj)) {
            $response = array('status'=>'failure');
        } else {
            $response = array('status'=>'success');
        } 
        echo json_encode($response);
    }

    public function adminexchangecategoryrankAction() {
        $return = array('status'=>'failure');

        if(empty($_REQUEST['categories'])) {
            echo json_encode($return);
            return;
        }
        $categories = json_decode($_REQUEST['categories'], true);
        $ret = GlobalCategoriesMapper::exchangeCategoryRank($categories, $this->account_dbobj);
        if($ret) $return['status'] = 'success';

        echo json_encode($return);
    }

    function adminsavetagAction(){
        
        $account_dbobj = $this->account_dbobj;
        $response = array('status'=>'failure');
        if(empty($_REQUEST['category'])) {
            echo json_encode($response);
            return;
        }
        $category = $_REQUEST['category'];
        $category_obj = new ProductCategory($account_dbobj);
        if(!$category_obj->setCategory($category)) {
            echo json_encode($response);
            return;
        }
        $category = $category_obj->getCategory();
        $category_obj->findOne("category='".$account_dbobj->escape($category)."'");
        if($category_obj->getId() !== 0) {
            echo json_encode($response);
            return;
        }
        $category_obj->setCategory($category);
        $category_obj->setDescription($_REQUEST['category']);
        $category_obj->save();
        $response['id']=$category_obj->getId();
        $response['status']='success';
        echo json_encode($response);
    }
    
    
    public function admindeletetagAction(){
        
        $status = 'failure';
        $account_dbobj = $this->account_dbobj;
        if(empty($_REQUEST['category']) || strlen($_REQUEST['category']>25)) {
            echo $status;
            return;
        }

        $category = sanitize_string($_REQUEST['category']);
        if(!empty($category)&&  ProductCategoryMapper::deleteCategory($category, $account_dbobj)){
            $status = 'success';
        }
        echo $status;
    }
     
    public function optinsalesnetworkAction(){
        global $redis;
        $merchant_id = $this->user_session->merchant_id;        
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        $response = array('status'=>'failure');

        $account_dbobj = $this->account_dbobj;
        $store = new Store($account_dbobj);
        $store->findOne('id='.$store_id);
        $store->setOptinSalesnetwork(ACTIVATED);
        $store->setPaymentSolution(PROVIDER_SHOPAY);       
        $store->save();         
        $redis->set("store:$store_id:optin_salesnetwork", ACTIVATED);
        $redis->set("store:$store_id:payment_solution", PROVIDER_SHOPAY);
        $response['status'] = 'success';   
        
        echo json_encode($response);
    }
        
       

    public function optoutsalesnetworkAction(){
        
        global $redis;
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        
        $account_dbobj = $this->account_dbobj;
        $response = array('status'=>'failure');

        $store=new Store($account_dbobj);
        $store->findOne('id='.$store_id);
        $store->setOptinSalesnetwork(CREATED);
        $store->save();
        
        if($store->getOptinSalesnetwork()===CREATED){
            $response['status']='success';
            $redis->set("store:$store_id:optin_salesnetwork", CREATED);           
        }
        echo json_encode($response);  
    }  

//  add product to salesnetwork
    public function add2snAction(){
        
        $response = array('status'=>'failure');
        
        if(!empty($_REQUEST['store_id']) && !empty($_REQUEST['product_id'])) {
            $store_id = $_REQUEST['store_id'];
            $product_id = $_REQUEST['product_id'];
            $account_dbobj = $this->account_dbobj;
            $associate_id = $this->user_session->associate_id;

            $associate_product = new AssociatesProduct($account_dbobj);
            $associate_product->findOne('associate_id='.$associate_id.' and store_id='.$store_id.' and product_id='.$product_id);
            $associate_product->setAssociateId($associate_id);
            $associate_product->setStatus(CREATED);
            $associate_product->setStoreId($store_id);
            $associate_product->setProductId($product_id);
            $associate_product->save();
            
            $associate_product_id = $associate_product->getId();
            if(!empty($associate_product_id)) {
                $response['status'] = 'success';                
            }
        }
        
        echo json_encode($response);  
    } 

//  remove product from salesnetwork    
    public function removesnAction(){
        
        $response = array('status'=>'failure');
        
        if(!empty($_REQUEST['store_id']) && !empty($_REQUEST['product_id'])) {
            $store_id = $_REQUEST['store_id'];
            $product_id = $_REQUEST['product_id'];
            $account_dbobj = $this->account_dbobj;
            $associate_id = $this->user_session->associate_id;
            
            $associate_product = new AssociatesProduct($account_dbobj);
            $associate_product->findOne('associate_id='.$associate_id.' and store_id='.$store_id.' and product_id='.$product_id);

            $associate_product_id = $associate_product->getId();
            if(!empty($associate_product_id)) {
                $associate_product->setStatus(PENDING);
                $associate_product->save();
                $response['status'] = 'success';   
            }             
        }
        
        echo json_encode($response);  
    }
    
    // input:
    // username, password
    public function loginAction() {
        global $dbconfig;
        $account_dbname = $dbconfig->account->name;

        $return = array('status'=>'failure', 'data' => array('errors' => array()));

        if(!empty($_REQUEST['username']) && !empty($_REQUEST['password'])) {
            $service = AccountsService::getInstance();
            $service->setMethod('login');
            $service->setParams(array('username'=>trim($_REQUEST['username']), 'password'=>$_REQUEST['password'], 
                'account_dbobj'=>$this->account_dbobj));
            $service->call();
            $status = $service->getStatus();

            if($status === 0) {
                $response = $service->getResponse();

                if($response['logged_in'] === 1) {
                    $user = $response['user'];
                    $this->user_session->user_id = $user->getId();
                    $this->user_session->merchant_id = $user->getMerchantId();
                    $user= BaseModel::findCachedOne(CacheKey::q($account_dbname.'.user?id='.$this->user_session->user_id));
                    $store = NULL;
                    if(!empty($this->user_session->merchant_id)){
                        $store = BaseModel::findCachedOne(CacheKey::q($account_dbname.'.store?id='.$user['store_id']));
                    }
                    $return['user'] = $user;
                    $return['store'] = $store;
                    $return['status'] = 'success';
                } else {
                    global $errors;
                    $error_msg = array();
                    foreach ($service->getErrnos() as $key => $value) { 
                        $error_msg[] = $errors[$key]['msg'];
                    }
                    $return['data'] = $error_msg;
                }  
            }
        }
        
        echo json_encode($return);
        
    }
    
    // input:
    // store_id, payment_solution
    public function setpaymentAction() {
        
        $return = array('status'=>'failure');
        if(!empty($_REQUEST['store_id']) && isset($_REQUEST['payment_solution'])) {
            $store_id = $_REQUEST['store_id'];
            $payment_solution = (int) $_REQUEST['payment_solution'];
            $account_dbobj = $this->account_dbobj;

            $store = new Store($account_dbobj);
            $store->findOne("id=$store_id");
            $store->setPaymentSolution($payment_solution);
            if($payment_solution === PROVIDER_PAYPAL) {
                $store->setTransactionFeeWaived(NOWAIVE);
            }                   
            $store->save();
            $redis->set("store:$store_id:payment_solution", $store->getPaymentSolution());
            $redis->set("store:$store_id:transaction_fee_waived", $store->getTransactionFeeWaived());     
            $return['status'] = 'success';                
        }       
        
        echo json_encode($return);
        
    }    

    // input:
    // store_id, payment_solution
    public function settransactionfeeAction() {    
        $return = array('status'=>'failure');
        if(!empty($_REQUEST['store_id']) && isset($_REQUEST['transaction_fee_waived'])) {
            $store_id = $_REQUEST['store_id'];
            $is_waved = (int) $_REQUEST['transaction_fee_waived'];
            $account_dbobj = $this->account_dbobj;

            $store = new Store($account_dbobj);
            $store->findOne("id=$store_id");
            $payment_solution = $store->getPaymentSolution();
            if($payment_solution != PROVIDER_SHOPAY && $is_waved) {
                echo json_encode($return);
                return;
            }   
            $store->setTransactionFeeWaived($is_waved);
            $store->save();
            $return['status'] = 'success';   
            $redis->set("store:$store_id:transaction_fee_waived", $is_waved);
        }      
        
        echo json_encode($return);
        
    }     
        
    public function updatepaymentitemstatusAction() {
        $return = array('status'=>'failure');    
        $account_dbobj = $this->account_dbobj;
        if(!empty($_REQUEST['payment_item_id']) && isset($_REQUEST['status']) && in_array((int)$_REQUEST['status'], array(CREATED,PROCESSING,PROCESSED))) {   
            $payment_item_id = $_REQUEST['payment_item_id'];
            $status = $_REQUEST['status'];

            $payment_item = new PaymentItem($account_dbobj);
            $payment_item->findOne('id='.$payment_item_id);
            $payment_item->setStatus($status);
            $payment_item->save();               
            $return['status'] = 'success'; 
        }
        echo json_encode($return);        
    }
    
    public function createmasspayAction() {
 
        $account_dbobj = $this->account_dbobj; 

        $account_dbobj = $this->account_dbobj;

        $service = new AdminService();
        $service->setMethod('createMassPay');
        $service->setParams(array(
            'account_dbobj' => $account_dbobj
        ));
        $service->call();         
    }
    
    public function updatepaymentstatusAction() {
        $return = array('status'=>'failure');    
        $account_dbobj = $this->account_dbobj;
        if(!empty($_REQUEST['payment_id']) && isset($_REQUEST['status']) && in_array((int)$_REQUEST['status'], array(DELETED,CREATED,PROCESSING,PROCESSED))) {   
            $payment_id = $_REQUEST['payment_id'];
            $status = $_REQUEST['status'];
            $payment = new Payment($account_dbobj);
            $payment->findOne('id='.$payment_id);
            $payment->setStatus($status);
            $payment->save();          
            $return['status'] = 'success'; 
        }
        echo json_encode($return);        
    } 
    
    public function downloadstoreinfoAction() {

        $account_dbobj = $this->account_dbobj;

        $service = new AdminService();
        $service->setMethod('downloadStoreInfo');
        $service->setParams(array(
            'account_dbobj' => $account_dbobj
        ));
        $service->call();        
    }     

    public function downloaduserinfoAction() {

        $service = new AdminService();
        $service->setMethod('downloadUserInfo');
        $service->call();        
    }     
    
    public function getflashdealdetailsAction() {
        
        $return = array('status'=>'failure', 'data' => array('errors' => array())); 
            
        $account_dbobj = $this->account_dbobj;    

        if(isset($_REQUEST['search_url'])) {

            $parts = parse_url(urldecode($_REQUEST['search_url']));
            $subdomain = array_shift(explode('.', $parts['host']));
            $http_query = array();
            $product_id = 0;
            if(isset($parts['query'])) {
                parse_str($parts['query'], $http_query);
            }
            if(!empty($http_query['id'])) {
                $product_id = $http_query['id'];
            }    

            $store = new Store($account_dbobj);
            $store->findOne("subdomain='".$account_dbobj->escape($subdomain)."'");
            $store_id = $store->getId();

            //Initially, we only support store and product deals
            if(!empty($store_id)) {
                $deal_info = CouponsMapper::getDeal($store_id, $product_id,$account_dbobj);
            }

            $deal_info['store_id'] = $store_id;
            $deal_info['product_id'] = $product_id;
            if(empty($deal_info['code'])) {
                $deal_info['code'] = uniqid();
            }    
            $return['status'] = 'success';
            $return['data'] = $deal_info;

        } else if(isset($_REQUEST['coupon_code'])) {

            $coupon_code = $_REQUEST['coupon_code'];
            $coupon_obj = new Coupon($account_dbobj);
            $coupon_obj->findOne("code='".$coupon_code."'");
            $deal_info = array();
            $exist_id = $coupon_obj->getId();
            if(!empty($exist_id)) {
                $deal_info = CouponsMapper::getDealByCode($coupon_code,$account_dbobj);
            }
            if(empty($deal_info['code'])) {
                $deal_info['code'] = uniqid();
            }    
            $return['status'] = 'success';
            $return['data'] = $deal_info;                
        }
        echo json_encode($return); 
    }
    
    public function updateflashdealstatusAction() {
        $account_dbobj = $this->account_dbobj;            
        $return = array('status'=>'failure');
        if(isset($_REQUEST['store_id']) && isset($_REQUEST['product_id']) && isset($_REQUEST['status'])) {
            $store_id = $_REQUEST['store_id'];
            $product_id = $_REQUEST['product_id'];
            $status = $_REQUEST['status'];

            CouponsMapper::updateStatus($account_dbobj, $store_id, $product_id, $status);
            $return['status'] = 'success';
        }
        echo json_encode($return);
    }
    
    public function getAbtestsAction() {
        $abtests = AbtestsMapper::getAllAbtests($this->account_dbobj);
        echo json_encode($abtests);
        
    }
    
    // params: name, num_shards
    public function addAbtestAction() {
        $response = array('status'=>'failure', 'data' => array('errors' => array()));
        
        if(!empty($_REQUEST['name']) && !empty($_REQUEST['num_shards'])) {
            
            $name = $_REQUEST['name'];
            $num_shards = $_REQUEST['num_shards'];
            $abtest = new Abtest($this->account_dbobj);
            $abtest->findOne("name='".$this->account_dbobj->escape($name)."'");
            if($abtest->getId() === 0) {
                $abtest->setName($name);
                $abtest->setNumShards($num_shards);
                $abtest->save();
                $response['status'] = 'success';
            }
        }
        
        echo json_encode($response);
    }
    
    // params: name
    public function deleteAbtestAction() {
        $response = array('status'=>'failure', 'data' => array('errors' => array()));
        
        if(!empty($_REQUEST['name'])) {
            
            $name = $_REQUEST['name'];
            $abtest = new Abtest($this->account_dbobj);
            $abtest->findOne("name='".$this->account_dbobj->escape($name)."'");
            if($abtest->getId() !== 0) {
                $abtest->setName($name);
                $abtest->setStatus(DELETED);
                $abtest->save();
                $response['status'] = 'success';
            }
        }
        
        echo json_encode($response);
    }
    
    public function uploadstoreavaterAction() {
        global $fileuploader_config, $redis; 
        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        $store_logo = $redis->get("store:$store_id:logo");

        $return = array('status'=>'failure', 'data' => array());      

        $service = FileUploaderService::getInstance ();
        $service->setMethod ('handleUpload');
        $service->setParams (array(
            'account_dbobj'=>$this->account_dbobj, 
            'store_id'=>$store_id, 
            'filename'=>'stores_'.$store_id.'_logo', 
            'allowedExtensions' => explode(',', $fileuploader_config->allowedExtensions), 
            'sizeLimit' => $fileuploader_config->sizeLimit, 
            'uploadDirectory' => $fileuploader_config->uploadDirectory, 
            'replaceOldFile' => true )
        );
        $service->call();
        $error = $service->getErrnos();
        if(empty($error)) {
            $response = $service->getResponse();
            $store_logo = $response['s3_upload_return'];           
            $redis->set("store:$store_id:logo", $store_logo); 
            $return['status'] = 'success'; 
        }
        $return['data']['logo'] = $store_logo;
        echo json_encode($return);
    }
    
    public function uploadAction() {
        
    }
    
    public function updatefeaturedproductAction() {
        
        $response = array('status'=>'failure');
        
        if(empty($_REQUEST['store_id']) || empty($_REQUEST['product_id']) 
                || (!isset($_REQUEST['score']) && !isset($_REQUEST['featured']))) {
            echo json_encode($response);
            return;
        }
        
        $account_dbobj = $this->account_dbobj;
        $store_id = $_REQUEST['store_id'];
        $product_id = $_REQUEST['product_id'];
        $search_product = new SearchProduct($account_dbobj);
        $search_product->findOne("store_id = $store_id and product_id = $product_id");        
        
        if($search_product->getId() !== 0) {
            
            $featured = isset($_REQUEST['featured']) ? $_REQUEST['featured'] : $search_product->getFeatured(); 
            $score = isset($_REQUEST['score']) ? $_REQUEST['score'] : $search_product->getScore(); 
            
            $search_product->setScore($score);               
            $search_product->setFeatured($featured);                  
            $search_product->save();
            $response['status'] = 'success';
        }
        
        echo json_encode($response);        
    }
    
    public function importproductsfromcsvAction() {
        global $redis;  
        $response = array('status'=>'failure');

        if(!empty($_REQUEST['csv_file_url'])) {

            $csv_path = $_REQUEST['csv_file_url'];
            $store_id = $this->store['id'];
            $job_dbobj = $this->job_dbobj;

            // save csv file to s3
            $dst = get_store_csv_file_upload_dst($store_id, uniqid());
            upload_image($dst, $csv_path);
                    
            $job = new Job($job_dbobj);
            $job->setType(PRODUCT_CSV_IMPORTER);
            $job->setData(array(
                'store_id' => $store_id,
                'csv_file_path' => $csv_path
            ));
            $job->setPriority(9);
            $job->save();     

            $response['status'] = 'success';                      
        }
        
        echo json_encode($response);        
    }
    
    public function updatepinterestusernameAction() {
        global $redis;
        
        $response = array('status'=>'failure');
        
        if(!empty($_REQUEST['pinterest_username'])) {
            $account_dbobj = $this->account_dbobj;
            $merchant_id = $this->user_session->merchant_id;
            
            // get the previous pinterest account
            $prev_pinterest_account_id = $redis->get("merchant:$merchant_id:pinterest_account_id");
            if($prev_pinterest_account_id) {
                $prev_pinterest_account = new PinterestAccount($account_dbobj);
                $prev_pinterest_account->findOne('id='.$prev_pinterest_account_id);
            }
            
            $pinterest_username = $_REQUEST['pinterest_username'];
            $pinterest_account = new PinterestAccount($account_dbobj);
            $pinterest_account->findOne('username='.$account_dbobj->escape($pinterest_username));
            if($pinterest_account->getId() === 0) {
                if($pinterest_account->setUsername($pinterest_username)) {
                    $pinterest_account->setExternalId(uuid());
                    $pinterest_account->save();

                    $merchant = new Merchant($account_dbobj);
                    $merchant->findOne('id='.$merchant_id);
                    if($merchant->getId() !== 0) {
                        if($prev_pinterest_account_id) {
                            BaseMapper::deleteAssociation($merchant, $prev_pinterest_account, $account_dbobj);
                        }
                        BaseMapper::saveAssociation($merchant, $pinterest_account, $account_dbobj);
                        $redis->set('merchant:$merchant_id:pinterest_account_id', $pinterest_account->getId());
                        $response = array('status'=>'success');
                    }

                }
            } else {
                if($prev_pinterest_account_id) {
                    BaseMapper::deleteAssociation($merchant, $prev_pinterest_account, $account_dbobj);
                }
                BaseMapper::saveAssociation($merchant, $pinterest_account, $account_dbobj);
                $redis->set('merchant:$merchant_id:pinterest_account_id', $pinterest_account->getId());
                $response = array('status'=>'success');
            }
            
        }
        
        echo json_encode($response);
    }
    
    // input:
    // pinterest_username
    public function getboardsAction() {
        
        $info = array(
            'user' => array(),
            'boards' => array()
        );
        
        if(!empty($_REQUEST['pinterest_username'])) {
            $info = get_pinterest_account_info($_REQUEST['pinterest_username']);
        }
        
        echo json_encode($info);
        
    }
    
    // input:
    // board_id
    // next_page_url (optional)
    public function getpinsAction() {
        $return = array(
            'pins' => array(),
            'next_page_url' => ''
        );
        if(!empty($_REQUEST['board_id'])) {
            $board_id = $_REQUEST['board_id'];
            $next_page_url = empty($_REQUEST['next_page_url'])?'':$_REQUEST['next_page_url'];
            $return = get_pinterest_board_info($board_id, $next_page_url);
        }
        
        echo json_encode($return);
        
    }
    
    // input: array('credit_card' => 
    // array(
    // card_number
    // exp_month
    // exp_year
    // billing_first_name
    // billing_last_name
    // billing_addr1
    // billing_addr2
    // billing_city
    // billing_state
    // billing_country
    // billing_zip
    // ))
    public function saveCreditCardAction() {
        $return = array('status'=>'failure', 'data' => array());

        if(!isset($_REQUEST['credit_card']) || !is_array($_REQUEST['credit_card'])) {
            echo json_encode($return);
            return;
        }
        
        //$_REQUEST['credit_card']['id'] = !empty($this->user['credit_card_ids'])?$this->user['credit_card_ids']:0;
        $_REQUEST['credit_card']['user_id']= $this->user_session->user_id;
        
        $service = PaymentAccountService::getInstance();
        $service->setMethod('save_credit_card');
        $service->setParams(array(
            'credit_card' => $_REQUEST['credit_card'],
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
        
        if($service->getStatus() === 0) {
            $card = $service->getResponse();
            $return['data'] = array(
                'id' => $card->getId(),
                'verified' => $card->getVerified(),
            );
            $return['status'] = 'success';
        }
        echo json_encode($return);
    }
    
    // input: params (an InkBlob)
    // Array
    //        (
    //            [url] => https://www.filepicker.io/api/file/VYTAaWwjTdK0L0QO10dY
    //            [filename] => 1bcgf.png
    //            [mimetype] => image/png
    //            [size] => 13105
    //            [key] => S2LKu6wRjiBmsy5TTRg0_1bcgf.png
    //            [container] => shopinterest_stage
    //            [isWriteable] => true
    //        )
    public function setStoreAvatarAction() {
        
        $return = array('status' => 'failure', 'data' => array());

        if(!checkIsSet($_REQUEST, 'params') || !checkIsSet($_REQUEST['params'], 'url')) {
            echo json_encode($return);
            return;
        }
        
        global $dbconfig;
        $account_dbname = $dbconfig->account->name;
        $params = $_REQUEST['params'];
        
        $user_id = $this->user_session->user_id;

        $user = BaseModel::findCachedOne(CacheKey::q($account_dbname.'.user?id='.$user_id));

        $service = StoreService::getInstance();
        $service->setMethod('save_avatar');
        $service->setParams(array(
            'url' => $params['url'],
            'store_id' => $user['store_id'],
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
        
        if($service->getStatus() === 0) {
            $return['status'] = 'success';
            $response = $service->getResponse();
            $return['data'] = array('logo_url' => $response['logo_url']);
        }
        echo json_encode($return);
        
    }
    
    
    // input:
    // {
    //  user: {
    //      password: password,
    //      first_name: first_name,
    //      last_name: last_name,
    //      addr1: addr1
    //      addr2: addr2
    //      city: city,
    //      state: state,
    //      country: country,
    //      zip: zip,
    //      phone: phone,
    //      paypal_email: paypal_email,
    //      bank_name: bank_name,
    //      bank_routing_number: bank_routing_number,
    //      bank_account_number: bank_account_number
    //  }
    // }
    public function saveUserSettingsAction() {
        $return = array('status'=>'failure', 'data' => array());

        if(!isset($_REQUEST['user']) || !is_array($_REQUEST['user'])) {
            echo json_encode($return);
            return;
        }
        $user = $_REQUEST['user'];
        $user['id'] = $this->user_session->user_id;
        $service = AccountsService::getInstance();
        $service->setMethod('save_settings');
        $service->setParams(array(
            'user' => $user,
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
        
        if($service->getStatus() === 0) {
            $return['status'] = 'success';
        }
        echo json_encode($return);
    }
    
    // input:
    // {
    //  store: {
    //      country: "US"
    //      currency: "USD"
    //      description: "ssdafas"
    //      external_website: ""
    //      name: "Liangs test store"
    //      return_policy: "never return"
    //      subdomain: "3279"
    //      tags: ""
    //      tax: "0"
    //  }
    // }
    public function saveSellingSettingsAction() {
        $return = array('status'=>'failure', 'data' => array());

        if(!isset($_REQUEST['store']) || !is_array($_REQUEST['store'])) {
            echo json_encode($return);
            return;
        }
        
        global $dbconfig;
        
        $store = $_REQUEST['store'];
        $account_dbname = $dbconfig->account->name;
        $user_id = $this->user_session->user_id;
        $user = BaseModel::findCachedOne(CacheKey::q($account_dbname.'.user?id='.$user_id));
        $store_id = $user['store_id'];
        $store['id'] = $store_id;
        $service = StoreService::getInstance();
        $service->setMethod('save_settings');
        $service->setParams(array(
            'store' => $store,
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
        
        if($service->getStatus() === 0) {
            $return['status'] = 'success';
        }
        echo json_encode($return);
    }

    public function saveEmailTemplateAction() {
        $return = array('status'=>'failure', 'data' => array());

        if(!isset($_REQUEST['template']) || !is_array($_REQUEST['template'])) {
            echo json_encode($return);
            return;
        }

        $tpl = array($_REQUEST['template']);
        $ret = BaseModel::saveObjects($this->account_dbobj, $tpl, "email_templates");
        $return['status'] = 'success';
        $return['data'] = array('id'=>$ret[0]);
        echo json_encode($return);
    }

    public function getEmailTemplateAction(){
        global $dbconfig;
        $return = array('status'=>'failure', 'data' => array());
        if(!isset($_REQUEST['type'])) {
            echo json_encode($return);
            return;
        }
        $type = $_REQUEST['type'];
        $tpl = BaseModel::findCachedOne($dbconfig->account->name . ".email_template?type=$type");
        if(empty($tpl)){
            echo json_encode($return);
            return;
        }
        $return['status'] = 'success';
        $return['data'] = $tpl;
        echo json_encode($return);
        return;
    }

    public function createproductsAction() {

        global $redis, $dbconfig;
        $store_dbname = $dbconfig->store->name;
        $account_dbname = $dbconfig->account->name;

        $return = array('status'=>'failure', 'data' => array());
        if(!isset($_REQUEST['products'])) {
            echo json_encode($return);       
            return;
        }  

        $products = $_REQUEST['products'];
        $store_id = $this->store['id'];
        $store_dbobj = $this->store_dbobj;    
        $job_dbobj = $this->job_dbobj;

        $service = new StoreService();
        $service->setMethod('create_products');
        $service->setParams(array(
            'products' => json_decode($products, true), 
            'store_dbobj' => $store_dbobj
        ));
        $service->call(); 
        
        if($service->getStatus() === 0) {
            $response = $service->getResponse();         

            foreach ($response as $product) {

                $product_id = $product['id'];
                if(isset($product['pictures']) && isset($product['pictures'][0]['converted_pictures'])) {
                    $job = new Job($job_dbobj);
                    $job->setType(UPLOAD_CONVERTED_PICTURES);
                    $job->setData(array(
                        'store_id' => $store_id,
                        'product_id' => $product_id
                    ));
                    $job->setPriority(9);
                    $job->save();        
                }
            }
            
            $return['status'] = 'success';
            $return['data'] = $response;            
        }

        echo json_encode($return);       
    }      
    
    public function deleteproductcategoryAction() {
        
        $return = array('status'=>'failure');        
        $store_dbobj = $this->store_dbobj;
        $product_id = $_REQUEST['product_id'];
        $category = $_REQUEST['category'];
       
        if(empty($product_id) || empty($category)) {
            echo json_encode($return);       
            return;            
        }

        $category = sanitize_string($category);
        ProductsMapper::deleteProductCategory($product_id, $category, $store_dbobj);
        
        $return['status'] = 'success';
        echo json_encode($return);              
    }
    
    public function deleteproductpictureAction() {
        
        $return = array('status'=>'failure');        
        $product_id = $_REQUEST['product_id'];
        $picture_id = $_REQUEST['picture_id'];
        $store_dbobj = $this->store_dbobj;
        
        if(empty($product_id) || empty($picture_id)) {
            echo json_encode($return);       
            return;            
        }
        
        ProductsMapper::deleteProductPicture($product_id, $picture_id, $store_dbobj);

        $return['status'] = 'success';
        echo json_encode($return);            
    }
    
    public function updateaccountstatusAction() {

        $return = array('status'=>'failure');    
        
        if(empty($_REQUEST['user_id'])) {
            echo json_encode($return);       
            return;            
        }    
        
        $user_id = $_REQUEST['user_id'];
        $status = empty($_REQUEST['status']) ? ACTIVATED : BLOCKED;
        $account_dbobj = $this->account_dbobj;

        $service = new UserService();
        $service->setMethod('update_account');
        $service->setParams(array(
            'user_id' => $user_id,
            'status' => $status,
            'account_dbobj' => $account_dbobj
        ));
        $service->call();
        
        if($service->getStatus() === 0) {
            $return['status'] = 'success';
        }
        echo json_encode($return);     
    }
    
    public function categorizingAction() {
        
        $return = array('status'=>'failure'); 
        if(!isset($_REQUEST['store_id']) || !isset($_REQUEST['product_id']) || !isset($_REQUEST['global_category_id'])) {
            echo json_encode($return); 
            return;
        }
        
        $store_id = $_REQUEST['store_id'];
        $product_id = $_REQUEST['product_id'];
        $global_category_id = $_REQUEST['global_category_id'];
        $account_dbobj = $this->account_dbobj;
        
        $search_product_obj = new SearchProduct($account_dbobj);
        $search_product_obj->findOne("store_id = $store_id and product_id = $product_id");
        if($search_product_obj->getId() !== 0) {
            $search_product_obj->setGlobalCategoryId($global_category_id);
            $search_product_obj->save();
        }

        $store_obj = new Store($account_dbobj, $store_id);
        $store_dbobj = DBObj::getStoreDBObj($store_obj->getHost(), $store_obj->getId());
        if($store_dbobj->is_db_existed()) {
            $product_obj = new Product($store_dbobj, $product_id);
            $product_obj->setGlobalCategoryId($global_category_id);
            $product_obj->save();
        }
        
        $return['status'] = 'success';
        echo json_encode($return);  
    }
    
    public function excludeproductAction() {
        
        $return = array('status'=>'failure'); 
        if(!isset($_REQUEST['store_id'])  || !isset($_REQUEST['exclude_in_search'])) {
            echo json_encode($return); 
            return;
        }        
        
        $store_id = $_REQUEST['store_id'];
        $excluded = $_REQUEST['exclude_in_search'];
        $account_dbobj = $this->account_dbobj;
        $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : 0;        
        
        // excluded record in search_products table
        if(!empty($product_id)) {
            $search_product_obj = new SearchProduct($account_dbobj);
            $search_product_obj->findOne("store_id = $store_id and product_id = $product_id");
            if($search_product_obj->getId() !== 0) {
                $search_product_obj->setExcludedInSearch($excluded);
                $search_product_obj->save();
                $return['status'] = 'success';
            }
        } else {
            $store_obj = new Store($account_dbobj, $store_id);
            if($store_obj->getId() !== 0) {
                $store_obj->setExcludedInSearch($excluded);
                $store_obj->save();
                $return['status'] = 'success';
            }            
        }
        echo json_encode($return);          
    }
    
    public function allowresellAction() {
        
        $return = array('status'=>'failure'); 
        if(!isset($_REQUEST['store_id'])  || !isset($_REQUEST['allow_resell'])) {
            echo json_encode($return); 
            return;
        }        
        
        $store_id = $_REQUEST['store_id'];
        $allow_resell = $_REQUEST['allow_resell'];
        $account_dbobj = $this->account_dbobj;       
        
        
        $store_obj = new Store($account_dbobj, $store_id);
        if($store_obj->getId() !== 0) {
            $store_obj->setAllowResell($allow_resell);
            $store_obj->save();
            $return['status'] = 'success';
        }
        echo json_encode($return);          
    }
    
    // input:
    // agree: 0/1 
    public function registerMerchantAction() {
        $return = array('status'=>'failure', 'data'=>array());
        
        if($this->view->is_merchant || !isset($_REQUEST['agree']) || $_REQUEST['agree'] != 1) {
            echo json_encode($return);
            return;
        }
        
        $user_id = $this->user_session->user_id;
        
        $service = AccountsService::getInstance();
        $service->setMethod('merchant_signup');
        $service->setParams(array(
            'user_id' => $user_id,
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
        $response = $service->getResponse();
        
        if($service->getStatus() === 0) {
            $return['status'] = 'success';
            $user = $response['user'];
            $merchant_id = $user->getMerchantId();
            $this->user_session->merchant_id = $merchant_id;
        }
        
        echo json_encode($return);
        
    }
    
    public function registerAction() {
        
        global $shopinterest_config;
        
        $return = array('status'=>'failure', 'data'=>array());

        $account_dbobj = $this->account_dbobj;
        $job_dbobj = $this->job_dbobj;
        
        if(!empty($_REQUEST['access_token'])) {
            //facebook login
            // initialize facebook api
            $this->facebook = new Facebook(array(
                'appId' => $shopinterest_config->facebook->app_id,
                'secret' => $shopinterest_config->facebook->secret,
            ));
            $this->facebook->setAccessToken($_REQUEST['access_token']);
            try {
                $fb_user = $this->facebook->api('/me','GET');
                $email_username = get_email_username($fb_user['email']);
                // params for the account signup service             
                $username = $fb_user['email'];
                $password = uniqid();
                $name = $fb_user['name'];
                $first_name = default2String($fb_user['first_name'], !empty($name)?:$email_username);
                $last_name = default2String($fb_user['last_name'], !empty($name)?:$email_username);;
                $open_store = false;
                $is_facebook_login = true;
            } catch(FacebookApiException $e) {
                $this->view->errnos[FACEBOOKAPIEXCEPTION] = 1;
                return;
            }
        } else {
            $username = default2String($_REQUEST['username']);
            $password = default2String($_REQUEST['password']);
            $first_name = default2String($_REQUEST['first_name']);
            $last_name = default2String($_REQUEST['last_name']);
            $open_store = default2Bool($_REQUEST['open_store']);
            $is_facebook_login = false;
        }
        list($_,$domain) = split('@', $username);
        global $temporary_email_address_domains;
        if(APPLICATION_ENV == 'production' && in_array($domain, $temporary_email_address_domains)){
            $return['data'] = array("Bad Email");
            echo json_encode($return);
            return;
        }
        $service = AccountsService::getInstance();
        $service->setMethod('signup');
        $service->setParams(array(
            'account_dbobj' => $account_dbobj,
            'username' => $username,
            'password' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'open_store' => $open_store,
            'is_facebook_login' => $is_facebook_login
        ));
        $service->call();
        
        if($service->getStatus() === 0) {
            
            $response = $service->getResponse();
            $user = $response['user'];
            $user_id = $user->getId();
            $merchant_id = $user->getMerchantId();
            $username = $user->getUsername();
            // session
            $this->user_session->user_id = $user_id;
            $this->user_session->merchant_id = $merchant_id;
            
            if(!empty($response['new_user_signup'])) {
                EmailsMapper::update_email($account_dbobj, array(
                    'email' => $username,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'source' => 'shopintoit_user',
                    'tags' => $open_store ? array("shopintoit_merchant", "shopintoit_user") : array("shopintoit_shopper", "shopintoit_user"),
                ));
                // send an email
                $user_verification_link = get_verification_url($user_id, $username);
                global $shopinterest_config;
                $service = EmailService::getInstance();
                $service->setMethod('create_job');
                $service->setParams(array(
                    'to' => $username,
                    'from' => $shopinterest_config->support->email,
                    'type' => $open_store ? USER_REGISTER_WITH_STORE : USER_REGISTER,
                    'data' => array(
                        'site_logo' => getSiteMerchantUrl(SHOPINTEREST_LOGO),
                        'site_url' => getURL(),
                        'user_verification_link' => $user_verification_link,
                        'link' => $user_verification_link
                    ),
                    'job_dbobj' => $job_dbobj
                ));
                $service->call();
            } else if($is_facebook_login) {
                redirect($this->user_session->referrer);
                return;
            }
            
         
            $return['status'] = 'success';
            $return['data'] = array(
                'user_id' => $user_id,
                'merchant_id' => $merchant_id
            );
        } else {
            global $errors;
            $error_msg = array();
            foreach ($service->getErrnos() as $key => $value) { 
                $error_msg[] = $errors[$key]['msg'];
            }
            $return['data'] = $error_msg;
        }  
        
        echo json_encode($return);
    }

    public function updateauctionstatusAction() {
        $return = array('status'=>'failure'); 
        if(!isset($_REQUEST['auction_id'])  || !isset($_REQUEST['status'])) {
            echo json_encode($return); 
            return;
        }
        $auction_id = $_REQUEST['auction_id'];
        $status = $_REQUEST['status'];

        if(!empty($auction_id)) {
            $auction = new Auction($this->account_dbobj);
            $auction->findOne("id=$auction_id");
            if($auction->getId() !== 0) {
                $auction->setStatus($status);
                $auction->save();
                $return['status'] = 'success';
            }
        }
        echo json_encode($return);
    }

    public function bidauctionAction() {
        global $redis;

        $return = array('status'=>'failure', 'err' => null);
        if(!isset($_REQUEST['auction_id'])  || !isset($_REQUEST['current_bid_price']) ||
        !isset($_REQUEST['my_bid_price'])) {
            echo json_encode($return); 
            return;
        }
        $auction_id = $_REQUEST['auction_id'];
        $current_bid_price = $_REQUEST['current_bid_price'];
        $my_bid_price = $_REQUEST['my_bid_price'];
        
        if(!empty($auction_id)) {
            $auction = new Auction($this->account_dbobj);
            $auction->findOne("id=$auction_id");
            //TODO: here maybe a bug, still a time window there between bids
            if($auction->getId() !== 0 && $auction->getCurrentBidPrice() == $current_bid_price &&
            $current_bid_price + $auction->getMinBidIncrement() <= $my_bid_price) {
                $key = "merchant:{$this->user_session->merchant_id}:store_id";
                if( $redis->get($key) === $auction->getStoreId()){
                    $return['err'] = "selfbid";
                    echo json_encode($return);
                    return;
                }
                $auction_service = AuctionService::getInstance();
                $auction_service->setMethod("create_bid");
                $auction_service-> setParams(array(
                    "auction_id" => $auction_id,
                    "user_id" => $this->user_session->user_id,
                    "bid_price" => $my_bid_price,
                    "account_dbobj" => $this->account_dbobj,
                    "job_dbobj" => $this->job_dbobj
                ));
                $auction_service->call();
                if($auction_service->getStatus() === 0){
                    $auction->setCurrentBidPrice($my_bid_price);
                    $auction->setBidTimes($auction->getBidTimes()+1);
                    $auction->save();
                    $return['status'] = 'success';
                }
            }
        }
        echo json_encode($return);
    }


    public function auctioncurrentbidpricesAction() {
        $return = array('status'=>'failure');
        if(!isset($_REQUEST['ids'])) {
            echo json_encode($return);
            return;
        }
        $ids = $_REQUEST['ids'];
        if(!preg_match('/^[0-9,]+$/', $ids)) {
            echo json_encode($return);
            return;
        }
        $return['data'] = AuctionsMapper::getCurrentBidPrices($this->account_dbobj, $ids);
        $return['status'] = 'success';
        echo json_encode($return);
    }
    
    public function deletecouponAction() { // this function is for merchant
         
        $return = array('status'=>'failure'); 
                
        if($code = default2String($_REQUEST['code'])) {
            
            global $redis;            
            $merchant_id = $this->user_session->merchant_id;

            $store_id = $redis->get("merchant:$merchant_id:store_id");        
            $account_dbobj = $this->account_dbobj;         
            
            $coupon_obj = new Mycoupon($account_dbobj);
            $coupon_obj->findOne("code ='".$account_dbobj->escape($code)."' and store_id = $store_id");     
            if($coupon_obj->getId() !== 0) {
                $coupon_obj->setStatus(DELETED);
                $coupon_obj->save();
            }
            $return['status'] = 'success';
        }
        
        echo json_encode($return);
    }
    
    public function admindeletecouponAction() { // this function is for admin
         
        $return = array('status'=>'failure'); 
                
        if($code = default2String($_REQUEST['code'])) {
                 
            $account_dbobj = $this->account_dbobj;         
            
            $coupon_obj = new Mycoupon($account_dbobj);
            $coupon_obj->findOne("code ='".$account_dbobj->escape($code)."'");  
            if($coupon_obj->getId() !== 0) {
                $coupon_obj->setStatus(DELETED);
                $coupon_obj->save();
            }
            $return['status'] = 'success';
        }
        
        echo json_encode($return);
    }    

    public function updatemerchantemailAction() {
        $return = array('status'=>'failure');
        
        $current_email = default2String($_REQUEST['current_email']);
        $new_email = default2String($_REQUEST['new_email']);
        if(!empty($current_email) && !empty($new_email) && validate($new_email, 'email')){
            
            global $redis;
            $account_dbobj = $this->account_dbobj;
                
            $user_obj = new User($account_dbobj);
            $user_obj->findOne("username='".$account_dbobj->escape($new_email)."'");
            if($user_obj->getId() == 0) { // check if new_email has used or not 
                
                UsersMapper::updateEmail($account_dbobj, $current_email, $new_email); 
                
                $user_id = $redis->get("user:username=$new_email:id");                     
                $redis->del("user:username=$current_email:id");
                $merhant_id = $redis->get("user:$user_id:merchant_id");               
                $redis->del("merchant:$merhant_id:username");
                $return['status'] = 'success';
            }            
        }
                
        echo json_encode($return);
    }
    
    // input: resell = 0/1, store_id, product_id
    public function resellproductAction() {
        $return = array('status'=>'failure'); 
        if(!isset($_REQUEST['store_id'])  || !isset($_REQUEST['resell']) || !isset($_REQUEST['product_id'])) {
            echo json_encode($return); 
            return;
        }        
        
        $store_id = $_REQUEST['store_id'];
        $product_id = $_REQUEST['product_id'];
        $resell = $_REQUEST['resell'];
        $store_dbobj = DBObj::getStoreDBObjById($store_id);
        
        $product = new Product($store_dbobj, $product_id);
        if($product->getId() !== 0) {
            $product->setResell($resell);
            $product->save();
            $return['status'] = 'success';
        }
        echo json_encode($return);
    }

    public function saveshippingoptAction() {
        $return = array('status'=>'failure', 'data' => array());        
        
        $store_dbobj = $this->store_dbobj;
        $data = json_decode($_REQUEST['shipping'], true);
        $opt_id = default2Int($data['id']);
        
        if(!empty($opt_id)){
            $opt_obj = new ShippingOption($store_dbobj, $opt_id);
            if($opt_obj->getName() === 'Standard') {
                $data['name'] = "Standard";
            }
            
            BaseModel::saveObjects($store_dbobj, $data, 'shipping_option');
            $return['status'] = 'success';
            $return['data'] = $data;
        }
        echo json_encode($return);
    }

    public function addshippingoptAction() {
        $return = array('status'=>'failure', 'data' => array());        
        $option_name = default2String($_REQUEST['option_name']);
        if(in_array($option_name, array("Priority", "Express", "Custom"))) {
            $store_dbobj = $this->store_dbobj;
            if($option_name === "Custom") $option_name = "";

            $opt = array(
                "name" => $option_name,
                "shipping_destinations" => array(
                    array(
                        "name" => "Domestic",
                        "fromdays" => "2",
                        "todays" => "5",
                        "base" => "0",
                        "additional" => "0"
                    ),
                    array(
                        "name" => "International",
                        "fromdays" => "5",
                        "todays" => "10",
                        "base" => "0",
                        "additional" => "0"
                    )
                )
            );
            BaseModel::saveObjects($store_dbobj, $opt, 'shipping_option');
            $return['status'] = 'success';
            $return['data'] = $opt;
        }
        echo json_encode($return);
    }
    
    public function deleteshippingoptAction() {
        $return = array('status' => 'failure');
        $opt_id = default2Int($_REQUEST['id']);

        if(!empty($opt_id)) {
            $store_dbobj = $this->store_dbobj;
            $opt_obj = new ShippingOption($store_dbobj, $opt_id);
            if($opt_obj->getName() !== 'Standard') {
                $opt_obj->setStatus(DELETED);
                $opt_obj->save();
                $return['status'] = 'success';
            }
        }
        echo json_encode($return);
    }

    public function saveshippingdestAction() {
        $return = array('status'=>'failure', 'data' => array());

        $store_dbobj = $this->store_dbobj;
        $data = json_decode($_REQUEST['shipping'], true);
        $dest_id = isset($data['id']) ? $data['id'] : 0;

        $dest_obj = new ShippingDestination($store_dbobj);
        $dest_obj->findOne(
            "name='".$store_dbobj->escape($data['name']) . "'
             and shipping_option_id = " . $data['shipping_option_id'] . "
             and status != " . DELETED);
        $old_id = $dest_obj->getId();
        if(!empty($old_id) && $old_id != $dest_id) { // check name
            // cant create an shipping with name alreay exist
            echo json_encode($return);
            return;
        }
        if(!empty2($dest_id)){// modify
            $dest_obj->findOne("id = $dest_id and status != " . DELETED);
            if($dest_obj->getId()>0 && $dest_obj->getName() === ''){ // -ALL-
                $opt_obj = new ShippingOption($store_dbobj);
                $opt_obj->findOne("id = " . $dest_obj->getShippingOptionId());
                if($opt_obj->getName() === 'Standard'){
                    // can not modify Standard/ALL
                    echo json_encode($return);
                    return;
                }
            }
        }

        $ret = BaseModel::saveObjects($store_dbobj, $data, 'shipping_destination');
        $data[0]['id'] = $ret[0];
        $return['status'] = 'success';
        $return['data'] = $data;
        echo json_encode($return);
    }

    public function deleteshippingdestAction() {
        $return = array('status'=>'failure');

        $dest_id = default2Int($_REQUEST['id']);
        if(!empty($dest_id)) {
            $store_dbobj = $this->store_dbobj;
            $dest_obj = new ShippingDestination($store_dbobj, $dest_id);
            $opt_id = $dest_obj->getShippingOptionId();

            if(ShippingDestinationsMapper::can_delete($store_dbobj, $opt_id)){
                $opt_ck = CacheKey::q($store_dbobj->getDBName().".shipping_option?id=".$opt_id);
                $old_data = DAL::get($opt_ck);
                $dest_obj->setStatus(DELETED);
                $dest_obj->save();
                DAL::s($opt_ck, $old_data);
                
                $return['status'] = 'success';
            }
        }
        echo json_encode($return);
    }


    public function saveproductshippingoptAction() {
        $return = array('status' => 'failure');
        $store_dbobj = $this->store_dbobj;

        $product_id = default2Int($_REQUEST['product_id']);
        $shipping_id = default2Int($_REQUEST['shipping_option_id']);

        if(!empty($product_id) && !empty($shipping_id)) {
            $product_obj = new Product($store_dbobj);
            $product_obj->setId($product_id);
            $shipping_obj = new ShippingOption($store_dbobj);
            $shipping_obj->setId($shipping_id);
            BaseMapper::saveAssociation($product_obj, $shipping_obj, $store_dbobj);
            $return['status'] = 'success';
        }
        echo json_encode($return);
    }

    public function deleteproductshippingoptAction() {
        $return = array('status' => 'failure');
        $store_dbobj = $this->store_dbobj;
        
        $product_id = default2Int($_REQUEST['product_id']);
        $shipping_id = default2Int($_REQUEST['shipping_option_id']);

        if(!empty($product_id) && !empty($shipping_id)) {
            $product_obj = new Product($store_dbobj);
            $product_obj->setId($product_id);
            $shipping_obj = new ShippingOption($store_dbobj);
            $shipping_obj->setId($shipping_id);
            BaseMapper::deleteAssociation($product_obj, $shipping_obj, $store_dbobj);
            $return['status'] = 'success';
        }

        echo json_encode($return);
    }

    public function getdestinationAction() {
        $return = array('status'=>'failure', 'data'=>array());
        $shipping_id = default2Int($_REQUEST['id']);

        if(!empty($shipping_id)) {
            $store_dbobj = $this->store_dbobj;
            $return['data'] = ShippingOptionsMapper::getDestination($store_dbobj, $shipping_id);
            $return['status'] = 'success';
        }
        echo json_encode($return);        
    }

    // Custom Fields
    public function savecustomfieldAction() {
        $return = array('status' => 'failure');
        $store_dbobj = $this->store_dbobj;
        $product_id = default2Int($_REQUEST['product_id']);
        $field_id = default2Int($_REQUEST['field_id']);
        $field_name = $_REQUEST['field_name'];
        $field_quantity = default2Int($_REQUEST['quantity']);

        $f_obj = new Field($store_dbobj);
        $f_obj->findOne(
            "name='".$store_dbobj->escape($field_name). "' and product_id = $product_id" .
            " and status != " . DELETED);
        $old_id = $f_obj->getId();

        if(!empty($old_id) && !empty($field_id) && $old_id != $field_id) {
            // cant create an field with name alreay exist
            echo json_encode($return);
            return;
        }
        if(!empty($product_id)) {

            $p = array(
                'id' => $product_id,
                'fields' => array(
                    array(
                        "id" => $field_id,
                        "name" => $field_name,
                        "quantity" => $field_quantity,
                    ),
                ),
            );
            BaseModel::saveObjects($store_dbobj, $p, 'product');

            $product_obj = new Product($store_dbobj, $product_id);
            $available_quantity = FieldsMapper::getAvailableQuantity($store_dbobj, $product_id);
            $product_obj->setQuantity($available_quantity);
            $product_obj->save();

            if(empty($field_id)){
                $f_obj->findOne(
                    "name='".$store_dbobj->escape($field_name). "' and product_id = $product_id" .
                    " and status != " . DELETED);
                $return['data']['field_id'] = $f_obj->getId();
            } else {
                $return['data']['field_id'] = $field_id;
            }

            $return['data']['field_id'] = $f_obj->getId();
            $return['data']['product_quantity'] = $product_obj->getQuantity();
            $return['status'] = 'success';
        }
        echo json_encode($return);
    }

    public function deletecustomfieldAction() {
        $return = array('status' => 'failure');
        $product_id = default2Int($_REQUEST['product_id']);
        $field_id = default2Int($_REQUEST['field_id']);

        if(!empty($product_id) && !empty($field_id)) {
            $store_dbobj = $this->store_dbobj;
            $f_obj = new Field($store_dbobj, $field_id);
            if($f_obj->getId()>0){
                $f_obj->setStatus(DELETED);
                $f_obj->save();

                $product_obj = new Product($store_dbobj, $product_id);
                $available_quantity = FieldsMapper::getAvailableQuantity($store_dbobj, $product_id);
                $product_obj->setQuantity($available_quantity);
                $product_obj->save();
                
                $return['data']['product_quantity'] = $product_obj->getQuantity();
                $return['status'] = 'success';
            }
        }
        echo json_encode($return);
    }
    
    // input:
    // table_object: users/slider_featured_products...
    // action: create, update, read
    // action_params: 
    // for create & update {id: xxx, status: xxx, score: xxx ...}
    // for read: 
    // {conditions: {}, condition_string: '(name=shoes&price>0)&(description=hello|category=boot)&sort[updated]',
    
    public function datatableAction() {

        if(!isset($_REQUEST['table_object']) || need_admin(default2String($_REQUEST['table_object']))) {
            if(!$this->is_admin()) return;
        }
        
        if(!empty($this->store['id']) && (isset($_REQUEST['table_object']) && $_REQUEST['table_object'] === 'store_coupon')) {
            $_REQUEST['action_params']['condition_string'] = "store_id=".$this->store['id'];
            $_REQUEST['action_params']['store_id'] = $this->store['id'];        
        }
        $this->datatablePreprocess();
    }

    public function authAction() {
        $return = array('status'=>'failure', 'data' => array());

        $params = array_merge($_REQUEST, array(
            'account_dbobj' => $this->account_dbobj,
            'job_dbobj' => $this->job_dbobj
        ));

        $service = AccountsService::getInstance();
        $service->setMethod('signin');
        $service->setParams($params);
        $service->call();

        if($service->getStatus() === 0) {
            $response = $service->getResponse();
            $user = $response['user'];
            
            $this->user_session->user_id = $user->getId();
            $this->user_session->merchant_id = $user->getMerchantId();
            $return['status'] = 'success';
        } else {
            // out put error
            $error = $service->getErrnos();
            $return['data'] = $error;
        }
        echo json_encode($return);
    }

    public function existeduserAction() {
        $return = array('status'=>'failure');
        
        $username = $_REQUEST['email'];
        $password = $_REQUEST['password'];
        $account_dbobj = $this->account_dbobj;
        $user = new User($account_dbobj);
        $user->findOne("username='{$account_dbobj->escape($username)}' and password = md5('{$account_dbobj->escape($password)}')");
        
        if(!empty2($user->getId())) {
            $return['status'] = 'success';
        }

        echo json_encode($return);        
    }

    public function ticketAction() {
        $return = array('status'=>'failure');

        if(checkIsSet($_REQUEST, 'email', 'subject', 'description')){

            $data = $_REQUEST;
            
            global $shopinterest_config;
            $to_emails = array('xxx@shopinterest.co');
            $service = EmailService::getInstance();
            
            $service->setMethod('create_job');
            foreach($to_emails as $to){
                $service->setParams(array(
                    'to' => $to,
                    'from' => $shopinterest_config->support->email,
                    'type' => MERCHANT_TICKET,
                    'subject'=> $data['subject'],
                    'text' => $data['description'],
                    'replyto' => $data['email'],
                    'job_dbobj' => $this->job_dbobj
                ));
                $service->call();
            }

            $return['status'] = 'success';

        }

        echo json_encode($return);
    }

    public function launchStoreAction(){
        global $dbconfig;
        $return = array('status'=>'failure');

        $store_id = $this->store['id'];
        $action = $_REQUEST['launch'];
        if($action !== 'true'){
            $store = new Store($this->account_dbobj, $store_id);
            $store->setStatus(PENDING);
            $store->save();
            GlobalProductsMapper::deleteProductsInStore($account_dbobj, $store_id);
            $return['status'] = 'success';
            echo json_encode($return);
            return;
        }

        $launch_cond = Store::canLaunch($this->store, TRUE, $this->user);
        if(!$launch_cond){
            echo json_encode($return);
            return;
        }
        $store = new Store($this->account_dbobj, $store_id);
        $store->setStatus(ACTIVATED);
        $store->save();
        $return['status'] = 'success';
        echo json_encode($return);
    }

    public function walletWithdrawRequestAction(){
        global $shopinterest_config, $dbconfig;
        $return = array('status'=>'failure');
        $wa_id = default2Int($_REQUEST['wallet_activity_id']);
        $wa = new WalletActivity($this->account_dbobj, $wa_id);
        if($wa->getId()<1 || $wa->getStatus() != ACTIVATED){
            echo json_encode($return);
            return;
        }
        $wallet = new Wallet($this->account_dbobj, $wa->getWalletId());
        $user = BaseModel::findCachedOne($dbconfig->account->name . ".user?id=" . $wallet->getUserId());

        $service = new EmailService();
        $service->setMethod('create_job');
        $service->setParams(array(
            'to' => $shopinterest_config->support->email,
            //'to' => 'xxx@shopinterest.co',
            'from' => $shopinterest_config->support->email,
            'replyto' => $user['username'],
            'from' => $user['username'],
            'type' => WALLET_WITHDRAW_REQUEST,
            'data' => array(
                'site_url' => getURL(),
                'wa_url' => getSiteMerchantUrl("/admin/payment-detail?id=" . $wa->getId()),
                'wa' => $wa->data(),
                'wallet' => $wallet->data(),
                'user' => $user,
            ),
            'job_dbobj' => $this->job_dbobj,
        ));
        $service->call();
        $return['status'] = 'success';
        echo json_encode($return);
    }
    
    public function ipnAction() {
        error_log('/api/ipn endpoint is hit');
        if(!empty($_REQUEST['ipn_track_id'])) {
            
            global $shopinterest_config;
            
            header("HTTP/1.1 200 OK");
            error_log('start parsing the paypal ipn notification');
            global $paypalconfig;
            $paypal_url = $paypalconfig->api->login_url;
            $query = 'cmd=_notify-validate&'.http_build_query($_REQUEST);
            $request_url = $paypal_url.'?'.$query;

            $response = file_get_contents($request_url);
            error_log('request:'.json_encode($_REQUEST));
            error_log('response:'.$response);
            
            if($response == 'VERIFIED' && !empty($_REQUEST['subscr_id']) && !empty($_REQUEST['custom'])) {
                error_log('This is a subscription ipn');
                $store_id = $_REQUEST['custom'];
                // save the ipn into the subscription_ipns table
                $ipn = new SubscriptionIpn($this->account_dbobj);
                $ipn->setStoreId($store_id);
                $ipn->setTxnType(default2String($_REQUEST['txn_type']));
                $ipn->setSubscrId($_REQUEST['subscr_id']);
                $ipn->setFirstName($_REQUEST['first_name']);
                $ipn->setLastName($_REQUEST['last_name']);
                $ipn->setMcCurrency($_REQUEST['mc_currency']);
                $ipn->setItemName($_REQUEST['item_name']);
                $ipn->setBusiness($_REQUEST['business']);
                $ipn->setVerifySign($_REQUEST['verify_sign']);
                $ipn->setPayerStatus($_REQUEST['payer_status']);
                $ipn->setPayerEmail($_REQUEST['payer_email']);
                $ipn->setReceiverEmail(default2String($_REQUEST['receiver_email']));
                $ipn->setPayerId($_REQUEST['payer_id']);
                $ipn->setOther(json_encode($_REQUEST));
                $ipn->save();
                error_log('ipn information saved');
                global $dbconfig;
                $store = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name.'.store?id='.$store_id));
                $subscribed = $store['subscribed'];
                $subscr_id = $store['subscr_id'];
                error_log('store info:'.json_encode($store));
                if($_REQUEST['txn_type'] === SubscriptionTxnTypes::subscr_signup) {
                    // make sure this user hasn't subscribed the service
                    if($subscribed !== '0000-00-00 00:00:00' || !empty($subscr_id)) {
                        // the potential risk of double subscription
                        // we need to send an email to xxx@shopinterest.co to
                        // get our attention
                        $store_url = getStoreUrl($store['subdomain']);
                        $service = new EmailService();            
                        $service->setMethod('create_job');
                        $service->setParams(array(
                            'to' => $shopinterest_config->support->email,
                            'from' => $shopinterest_config->support->email,
                            'type' => ALERT_EMAIL,
                            'data' => array(
                                'subject' => 'POSSIBLE DUPLICATE SUBSCRIPTION',
                                'content' => "possible duplicate subscription detected for the store $store_id: $store_url"
                            ),
                            'job_dbobj' => $this->job_dbobj
                        ));
                        $service->call();
                        error_log('POSSIBLE DUPLICATE SUBSCRIPTION, send an alert email, abort...');
                        return;
                    }
                    
                    // ready to mark this store as a subscriber
                    $store_obj = new Store($this->account_dbobj, $store_id);
                    $store_obj->setSubscribed(get_current_datetime());
                    $store_obj->setSubscrId($_REQUEST['subscr_id']);
                    $store_obj->save();
                    error_log('subscription signup, updated the store.');
                } else if($_REQUEST['txn_type'] === SubscriptionTxnTypes::subscr_cancel) {
                    // when the user cancels the subscription, we need to do nothing
                    // because the subscription is till valid during for the current month
                    error_log('subscription cancelled, do nothing');
                } else if ($_REQUEST['txn_type'] === SubscriptionTxnTypes::subscr_eot) {
                    $store_obj = new Store($this->account_dbobj, $store_id);
                    $store_obj->setSubscribed('0000-00-00 00:00:00');
                    $store_obj->setSubcrId('');
                    $store_obj->save();
                    error_log('subscription term ends, update the store');
                } else if($_REQUEST['txn_type'] === SubscriptionTxnTypes::subscr_payment) {
                    if(empty($subscr_id) || $subscribed === '0000-00-00 00:00:00') {
                        $store_url = getStoreUrl($store['subdomain']);
                        $service = new EmailService();            
                        $service->setMethod('create_job');
                        $service->setParams(array(
                            'to' => $shopinterest_config->support->email,
                            'from' => $shopinterest_config->support->email,
                            'type' => ALERT_EMAIL,
                            'data' => array(
                                'subject' => 'A NON SUBSCRIBE MADE A PAYMENT',
                                'content' => "A non subscriber made a payment for the store $store_id: $store_url"
                            ),
                            'job_dbobj' => $this->job_dbobj
                        ));
                        $service->call();
                        error_log('A NON SUBSCRIBE MADE A PAYMENT, send an alert email, abort...');
                        return;
                    } else if($subscr_id !== $_REQUEST['subscr_id']) {
                        $store_url = getStoreUrl($store['subdomain']);
                        $service = new EmailService();            
                        $service->setMethod('create_job');
                        $service->setParams(array(
                            'to' => $shopinterest_config->support->email,
                            'from' => $shopinterest_config->support->email,
                            'type' => ALERT_EMAIL,
                            'data' => array(
                                'subject' => 'SUBSCRIPTION PAYMENT HAS A DIFFERENT SUBSCRIBER ID',
                                'content' => "The subscription payment has a different subscription id for the store $store_id: $store_url"
                            ),
                            'job_dbobj' => $this->job_dbobj
                        ));
                        $service->call();
                        error_log('SUBSCRIPTION PAYMENT HAS A DIFFERENT SUBSCRIBER ID, send an alert email, abort...');
                        return;
                    }
                    // update the subscribed field only
                    $store_obj = new Store($this->account_dbobj, $store_id);
                    $store_obj->setSubscribed(get_current_datetime());
                    $store_obj->save();
                    error_log('subscriber made a payment, update the store');
                } else if($_REQUEST['txn_type'] === SubscriptionTxnTypes::subscr_failed) {
                    $store_url = getStoreUrl($store['subdomain']);
                    $service = new EmailService();            
                    $service->setMethod('create_job');
                    $service->setParams(array(
                        'to' => $shopinterest_config->support->email,
                        'from' => $shopinterest_config->support->email,
                        'type' => ALERT_EMAIL,
                        'data' => array(
                            'subject' => 'SUBSCRIPTION PAYMENT FAILED',
                            'content' => "A subscription payment failed for the store $store_id: $store_url"
                        ),
                        'job_dbobj' => $this->job_dbobj
                    ));
                    error_log('SUBSCRIPTION PAYMENT FAILED, send an alert email, abort...');
                }
                
                return;
            }
            error_log('Subscription IPN not pass validation from paypal, abort...');
            return;
        }
        error_log('Not a valid subscription ipn');
    }
    
    // input: event -- subscription
    // output: no output
    // use ignore_user_abort(true);
    // intially, lets only cache the key for 150s
    public function inprocessAction() {
        ignore_user_abort(true);
        
        if(!empty($_REQUEST['event'])) {
            
            
            global $redis;
            
            $timeout = 150;
            $user_id = $this->user_session->user_id;
            $key = $_REQUEST['event'].'_'.$user_id;
            
            $redis->set($key, $timeout, $timeout);
                   
        }
        
    }
    
    public function subscribedAction() {
        
        global $redis;
        $store = $this->store;
        
        $is_subscriber = !empty($store['subscribed'])&&!empty($store['subscr_id']);
        $in_process = $redis->get('subscription_'.$this->user_session->user_id); 
        
        if(!$is_subscriber && !$in_process) {
            echo "non-subscriber";
        } else if(!$is_subscriber && $in_process) {
            echo 'in-process';
        } else if($is_subscriber) {
            echo 'subscriber';
        } else {
            echo "wth";
        }
        
    }
    
    public function isLoginAction() {
        echo "success";
    }

    public function manageStoreAction(){
        $return = array('status'=>'failure');
        $data = $_REQUEST['data'];
        $action = $data['action'];
        if($action == 'product-delete-all'){
            $params = array(
                'target' => 'all',
                'store_id' => $data['data']['store_id'],
            );

            $service = StoreService::getInstance();
            $service->setMethod("delete_products");
            $service->setParams($params);
            $service->call();
            if($service->getStatus() === 0) {
                $return['status'] = "success";
                $return['data'] = $service->getResponse();
            }
        }
        echo json_encode($return);
    }

    public function closeStoreAction() {
        $return = array('status'=>'success');
        $store = $this->store;
        StoresMapper::forceDeleteStore($this->account_dbobj, $store['id']);
        $this->user_session->merchant_id = 0;
        echo json_encode($return);
    }

}
