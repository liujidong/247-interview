<?php

class PaymentsController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */

    }

    public function indexAction() {

        global $redis;
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");

        if($this->is_merchant() && $this->subdomain_type === 'merchant') {
            $this->view->future_payments = StoresMapper::getFuturePayments($store_id, $this->account_dbobj);
            $this->view->payments = StoresMapper::getPayments($store_id, $this->account_dbobj);
        } else if($this->is_associate() && $this->subdomain_type === 'salesnetwork') {
            $this->view->future_payments = AssociatesMapper::getFuturePayments($this->user_session->associate_id, $this->account_dbobj);
            $this->view->payments = AssociatesMapper::getPayments($this->user_session->associate_id, $this->account_dbobj);
        }

    }


    public function addAction() {

        global $redis;
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        $store_name = $redis->get("store:$store_id:name");

        $account_dbobj = $this->account_dbobj;
        $store_dbobj = $this->store_dbobj;

        if(isset($_REQUEST['submit'])) {

            $service = MerchantService::getInstance();
            $service->setMethod('add_payments');
            $service->setParams(array('merchant_id'=>$merchant_id, 'paypal_username'=>$_REQUEST['paypal_username'],
                'account_dbobj'=>$account_dbobj));
            $service->call();

            // redirect to the store home page
            redirect('/store/'.$store_name);

        } else {
            $payment_account_ids = MerchantsMapper::getPaymentAccountIds($merchant_id, $account_dbobj);
            $paypal_account_id = $payment_account_ids['paypal_account_id'];

            $this->view->paypal = '';

            if(!empty($paypal_account_id)) {
                $paypal = new PaypalAccount($account_dbobj);
                $paypal->findOne('id='.$paypal_account_id);
                $this->view->paypal = $paypal->getUsername();
            }


        }
    }

}

