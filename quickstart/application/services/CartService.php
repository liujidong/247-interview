<?php

class CartService extends BaseService {

    public function __construct() {
        parent::__construct();
    }

    public function add_product_to_cart() {
        $params = $this->params;

        $account_dbobj = $params['account_dbobj'];

        $user_id = $params['user_id'];
        $store_id = $params['store_id'];
        $product_id = $params['product_id'];
        $quantity = $params['quantity'];
        $currency = $params['currency'];
        $dealer = default2String($params['dealer']);
        $external_id = default2String($params['external_id']);
        $aid = $params['aid'];
        $custom_field = $params['custom_field'];
        $cart_id = CartsMapper::findCurrentCartForUser($account_dbobj, $user_id);
        $cart_currency = CartsMapper::getCurrencyOfCart($account_dbobj, $cart_id);
        $cart_dealer = CartsMapper::getDealerOfCart($account_dbobj, $cart_id);
        if(!empty($cart_currency) && $currency != $cart_currency){
            $this->errnos[DIFF_CURRENCY_FOR_CART] = 1;
            $this->status = 1;
            return;
        }

        if($cart_dealer !== null && $dealer != $cart_dealer){
            $this->errnos[DIFF_DEALER_FOR_CART] = 1;
            $this->status = 1;
            return;
        }

        list($cart_num, $product_num) = CartsMapper::addProductToCart(
            $account_dbobj, $cart_id,
            $store_id, $product_id, $quantity, $custom_field,
            $dealer, $external_id,
            $aid
        );

        if($dealer == 'amazon'){
            $service = AmazonSearchService::getInstance();
            $service->setParams(array(
                'ASIN' => $external_id,
                'save_to_db' => TRUE,
                'db_data' => array(),
            ));
            $service->setMethod('lookup');
            try{
                $service->call();
            } catch(Exception $e) {
                error_log($e);
                $this->errors[] = "Amazon Prodduct Lookup error";
                //$this->status = 1;
                //return;
            }
        }

        $this->status = 0;
        $this->response['cart_num'] = $cart_num;
        $this->response['product_num'] = $product_num;
        $this->response['total_prices'] = CartsMapper::getTotalPricesOfCart($account_dbobj, $cart_id);
    }

}
