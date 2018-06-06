<?php

class AdminService extends BaseService{
    
    // input:
    // page
    // account_dbobj
    public function createMassPay() {
        
        global $paypalconfig;
        $masspay_percentage = $paypalconfig->masspay_percentage;
        $masspay_flatfee = $paypalconfig->masspay_flatfee;

        $account_dbobj = $this->params['account_dbobj'];
        
        $filename = 'masspayments-'.get_current_datetime_filename();
        $payments = PaymentItemsMapper::getPayments($account_dbobj);
        $fields = array();        

        foreach($payments as $key => $payment) {

            // create a payment record
            $payment_obj = new Payment($account_dbobj);
            $payment_obj->setSender($payment['sender']);
            $payment_obj->setReceiver($payment['receiver']);

            $payment_contract = array(
                'gross' => $payment['amt'],
                'deductible' => array(
                    'paypal_fee' => round($payment['amt'] * $masspay_percentage > $masspay_flatfee ? $masspay_flatfee : $payment['amt'] * $masspay_percentage, 2)
                )                
            );
            $payment_contract['net'] = $payment_contract['gross'] - $payment_contract['deductible']['paypal_fee'];
            $payment_obj->setCurrencyCode($payment['currency_code']);
            $payment_obj->setAmt($payment_contract['net']);
            $payment_obj->setContract(json_encode($payment_contract));
            $payment_obj->save();

            // update the payment item id 
            $payment_item_ids = explode(',', $payment['payment_item_ids']);
            foreach($payment_item_ids as $payment_item_id) {
                $payment_item = new PaymentItem($account_dbobj);
                $payment_item->findOne('id='.$payment_item_id);
                $payment_item->setPaymentId($payment_obj->getId());
                $payment_item->save();
            }
            
            $fields[$key] = array(
                $payment['email'], 
                $payment_contract['net'], 
                $payment['currency_code'], 
                'payment-'.$payment_obj->getId(), 
                'Thank you for your business.'
            );
        }
        exportCSV($fields, $filename);
    }
    
    public function downloadStoreInfo() {
        $account_dbobj = $this->params['account_dbobj'];
        $filename = 'stores_info-'.get_current_datetime_filename();
        $fields = array();
        $col_headers = array('Email', 'Store Status', 'Store Name', 'Account Created Time', 
                'Store Updated Time', 'Store Created Time', 'Store Subdomain', 'Pinterest URL',
                'Paypal(Y/N)', 'Active Products Cnt', 'Transactions Cnt', 'SalesNetwork(Y/N)', 'Shopay',
                'Transaction Fee Waived');
        
        $service = new StoreService();
        $service->setMethod('get_all_stores_info');
        $service->setParams(array('account_dbobj' => $account_dbobj)); 
        $service->call();
        $store_infos = $service->getResponse();
        foreach ($store_infos as $i => $store_info) {
            
            $fields[$i]['username'] = $store_info['username']; 
            $fields[$i]['status'] = $store_info['status']; 
            $fields[$i]['name'] = $store_info['name']; 
            $fields[$i]['merchant_created_time'] = $store_info['merchant_created_time'];     
            $fields[$i]['store_updated_time'] = $store_info['store_updated_time'];   
            $fields[$i]['created_time'] = $store_info['created_time'];
            $fields[$i]['subdomain'] = $store_info['subdomain'];
            $fields[$i]['pinterest_url'] = empty($store_info['pinterest_url']) ? '' : $store_info['pinterest_url'];
            $fields[$i]['paypal'] = $store_info['paypal'];
            $fields[$i]['product_cnt'] = $store_info['product_cnt'];
            $fields[$i]['transaction_cnt'] = $store_info['transaction_cnt'];
            $fields[$i]['optin_salesnetwork'] = $store_info['optin_salesnetwork'];
            
            if ($store_info['payment_solution'] == PROVIDER_SHOPAY){
                $fields[$i]['shopay'] = 'Y';
            } else {
                $fields[$i]['shopay'] = 'N';
            }
            
            if ($store_info['transaction_fee_waived'] == WAIVED){
                $fields[$i]['fee_waived'] = 'Y';           
            } else {
                $fields[$i]['fee_waived'] = 'N';
            }                           
        }

        exportCSV($fields, $filename, $col_headers);
    }

    public function downloadUserInfo() {

        $filename = 'users_info-'.get_current_datetime_filename();
        $fields = array();
        $col_headers = array(
            'Email', 'First Name', 'Last Name', 'Phone', 'Aid', 'Last Login', 'Last Activity', 'Credit card on File', 'Pinterest URL',
            'Store', 'Store Status', 'Store Created Time', 'Country', 'Currency', 'Tax', 'Paypal Fee Waived',
            'Active Products CNT', 'Inactive Products CNT', 'Total Sale Transactions ', 'Total Sale Amount', 'Sales',
        );
        $config = DatatableService::getTableConfigs();
        $base_list_key = $config['all_users']['base_list_key'];
        $cache_opt = $config['all_users']['cache_opt'];
        $response = BaseMapper::getCachedObjects($base_list_key, 0, PHP_INT_MAX, $cache_opt);
        foreach($response['data'] as $i => $model) {
            $fields[$i]['username'] = $model['username'];
            $fields[$i]['first_name'] = $model['first_name'];
            $fields[$i]['last_name'] = $model['last_name'];
            $fields[$i]['phone'] = $model['phone'];
            $fields[$i]['aid'] = $model['aid'];
            $fields[$i]['last_login'] = $model['last_login'];
            $fields[$i]['last_activity'] = $model['last_activity'];
            $fields[$i]['credit_card_on_file'] = $model['credit_card_on_file'];
            $fields[$i]['pinterest_url'] = $model['pinterest_username'];
            $fields[$i]['store'] = $model['store']['url'];
            $fields[$i]['formatted_store_status'] = $model['store']['literal_status'];
            $fields[$i]['store_created'] = $model['store']['created'];
            $fields[$i]['country'] = $model['store']['country'];
            $fields[$i]['store_currency'] = $model['store']['currency'];
            $fields[$i]['tax'] = $model['store']['tax'];
            $fields[$i]['formatted_transaction_fee_waived'] = $model['store']['literal_transaction_fee_waived'];
            $fields[$i]['activte_product_cnt'] = $model['store']['active_product_cnt'];
            $fields[$i]['inactivte_product_cnt'] = $model['store']['inactive_product_cnt'];
            $fields[$i]['total_sale_transactions'] = $model['store']['total_sale_transactions'];
            $fields[$i]['total_sale_amount'] = $model['store']['total_sale_amount'];
            $fields[$i]['sales'] = $model['store']['product_sales'];
        }

        exportCSV($fields, $filename, $col_headers);
    }
    
}

?>
