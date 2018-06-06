<?php

class CheckoutController extends BaseController {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        if($this->view->nav_cart_num < 1){
            redirect(getSiteMerchantUrl("/"));
        }
        $user_id = $this->user_session->user_id;
        $cart_id = CartsMapper::findCurrentCartForUser($this->account_dbobj, $user_id);
        $products = CartsMapper::getProductsInCart($this->account_dbobj, $cart_id);
        if($products[0]['dealer'] == 'amazon'){
            return $this->checkoutAmazonCart($products);
        }
        if(empty($user_id)){
            return redirect(getSiteMerchantUrl('/login?next=/checkout'));
        }
        $this->view->countries = CountriesMapper::getAllCountryInfo($this->account_dbobj);
        $this->view->old_form = $_REQUEST;
        $this->view->errors = array();
        $this->view->old_form["pay_method"] = "creditcard";
        if(!isset($_REQUEST['continue'])){
            // check if has active myorder, then fill old_form
            $myorder = MyorderGroupsMapper::findOrCreateOrderGroup($this->account_dbobj, $this->user_session->user_id);
            $addr_fields = array(
                "first_name", "last_name",
                "addr1", "addr2", "country", "state", "city",
                "zip", "phone", "email"
            );
            foreach($addr_fields as $f){
                setifnotset($this->view->old_form, "shipping_$f", $myorder->get("shipping_$f"));
            }
            $pay_method = empty2($myorder->getPaymentMethod()) ? "creditcard" : $myorder->getPaymentMethod();
            $this->view->old_form["pay_method"] = $pay_method;
            if($pay_method == 'creditcard'){
                $cc = new CreditCard($this->account_dbobj);
                $cc->findOne("paypal_card_id = '" . $this->account_dbobj->escape($myorder->getPaymentInfo()) . "'");
                if($cc->getId() < 1){ // no card for this order, lookup one for this user
                    $cc->findOne("user_id = " . $this->user_session->user_id);
                }
                if($cc->getId() > 0){
                    $this->view->old_form['first_name'] = $cc->getBillingFirstName();
                    $this->view->old_form['last_name'] = $cc->getBillingLastName();
                    $this->view->old_form['card_number'] = "**** **** **** " . $cc->getCardNumber();
                    $this->view->old_form['cvv2'] = "***";
                    $this->view->old_form['card_expire_month'] = $cc->getExpMonth();
                    $this->view->old_form['card_expire_year'] = $cc->getExpYear();
                    foreach($addr_fields as $f){
                        setifnotset($this->view->old_form, "billing_$f", $cc->get("billing_$f"));
                    }
                }
            }
            return;
        }

        $pay_method = $_REQUEST['pay_method'];
        $pay_info = null;
        $payment_account = null;

        if($pay_method === "creditcard") { //pay with creditcard
            $params = array();
            $params['account_dbobj'] = $this->account_dbobj;
            $params['user_id'] = $this->user_session->user_id;
            $params['billing_first_name'] = $_REQUEST['first_name'];
            $params['billing_last_name'] = $_REQUEST['last_name'];
            $params['card_number'] = $_REQUEST['card_number'];
            $params['cvv2'] = $_REQUEST['cvv2'];
            $params['exp_month'] = $_REQUEST['card_expire_month'];
            $params['exp_year'] = $_REQUEST['card_expire_year'];

            $service = PaypalRestService::getInstance();
            $service->setMethod("get_paypal_card");
            $service->setParams($params);
            $service->call();

            $status = $service->getStatus();
            $response = $service->getResponse();
            if($status != 0){
                $this->view->errnos = $service->getErrnos();
                return;
            }
            $our_card = $response['our_card'];
            $pay_info = $our_card->getPaypalCardId();
            if($response['is_new_card']){
                NativeCheckoutService::fillAddresses($our_card, $_REQUEST, "billing");
                $our_card->save();
            }
        } else if($pay_method === "paypal"){ // pay use paypal
            $pay_info = '';
        }

        $myorder_grp = MyorderGroupsMapper::createMyorderGroup(
            $this->account_dbobj, $this->user_session->user_id,
            $pay_method, $pay_info
        );
        $this->user_session->nc_order_checkouted = true;
        redirect(getSiteMerchantUrl("/checkout/confirm"));
    }

    public function confirmAction() {
        if($this->user_session->nc_order_checkouted === true){
            $this->user_session->nc_order_checkouted = false;
        } else {
            redirect(getSiteMerchantUrl("/checkout"));
        }

        $myorder_grp = MyorderGroupsMapper::findOrCreateOrderGroup($this->account_dbobj, $this->user_session->user_id);
        $service = NativeCheckoutService::getInstance();
        $service->setMethod("myorder_summary");
        $params = array(
            'account_dbobj' => $this->account_dbobj,
            'order_group' => $myorder_grp,
            'save_summary' => true,
        );
        $service->setParams($params);
        $service->call();

        $response = $service->getResponse();

        $this->view->items = $response['items_by_store'];
        $this->view->stores = $response['store_summaries'];
        $this->view->order_ids = $response['order_ids_by_store'];
        $this->view->currency_symbol = $response['currency_symbol'];
        $this->view->price_total = $response['price_total'];
        $this->view->shipping_total = $response['shipping_total'];
        $this->view->tax_total = $response['tax_total'];
        $this->view->total = $response['total'];
        $this->view->errors = $response['errors'];

        $from_paypal = isset($_REQUEST['action']) ? $_REQUEST['action'] === "paypal-return" : false;
        $this->view->next_action = "creditcard_payment";
        $this->view->order = $myorder_grp;
        $this->view->order_id = $myorder_grp->getId();
        $this->view->pay_method = $myorder_grp->getPaymentMethod();

        if($from_paypal){
            $paypal_return = isset($_REQUEST['return']) ? $_REQUEST['return'] === "true" : false;
            if(!$paypal_return){ // paypal confirm error/canceled
                $this->view->next_action = "paypal_confirm";
            }else{
                $this->view->next_action = "paypal_payment";
            }
        } else {
            if($this->view->pay_method == "paypal"){
                $this->view->next_action = "paypal_confirm";
            }
        }
    }

    public function checkoutAmazonCart($products = NULL) {
        if(empty($products)){
            $user_id = $this->user_session->user_id;
            $cart_id = CartsMapper::findCurrentCartForUser($this->account_dbobj, $user_id);
            $products = CartsMapper::getProductsInCart($this->account_dbobj, $cart_id);
        }
        if(empty($products)){
            redirect(getSiteMerchantUrl("/"));
        }
        $cart_url = AmazonSearchService::cart($products);
        redirect($cart_url);
    }
}
