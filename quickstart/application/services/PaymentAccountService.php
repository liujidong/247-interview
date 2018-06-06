<?php

class PaymentAccountService extends BaseService {

    public function __construct() {
        parent::__construct();
    }
    
    // input: array('credit_card' => 
    // array(
    // id (optional, which is credit card id) 
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
    // billing_zip,
    // user_id
    // ))
    // account_dbobj
    public function save_credit_card() {
        $credit_card = $this->params['credit_card'];
        $account_dbobj = $this->params['account_dbobj'];

        $errors = array();

        $service = PaypalRestService::getInstance();
        $service->setMethod('get_paypal_card');
        $service->setParams(array_merge($credit_card,
        array(
            'card_number' => $credit_card['card_number'],
            'account_dbobj' => $account_dbobj,
        )));
        $service->call();
        $response = $service->getResponse();

        if($service->getStatus() != 0) {
            $this->status = 1;
            $errors[] = 'Error on saving the credit card account.';
            $this->response = $errors;
        } else {
            $this->status = 0;
            $card = $response['our_card'];
            NativeCheckoutService::fillAddresses($card, $credit_card, 'billing');
            $card->save();
            DAL::delete(CacheKey::q($account_dbobj->getDBName().".user?id=" . $credit_card['user_id']));
            $this->response = $card;
        }
    }
    
    
}
