<?php

class MycouponsDatatable extends BaseDatatable{

    protected function _create() {
        $params = $this->action_params;

        if(isset($params['label']) && $params['label'] === "admin") { // admin coupon tool
            global $dbconfig;
            $params['operator'] = 'admin';
            if($params['scope'] == SITE){
                // pass
            }else if(!empty($params['store_url']) && $params['scope'] == STORE) {

                if($val = parse_store_url($params['store_url'])) {
                    $subdomain = $val['subdomain'];
                    $store_model = BaseModel::findCachedOne($dbconfig->account->name.'.store?subdomain='.$subdomain);

                    $params['store_id'] = $store_model['id'];
                    unset($params['store_url']);
                }
            } else if(!empty($params['product_url']) && $params['scope'] == PRODUCT) {

                if($val = parseProductUrl($params['product_url'])) {

                    $subdomain = $val['subdomain'];
                    $store_model = BaseModel::findCachedOne($dbconfig->account->name.'.store?subdomain='.$subdomain);                    

                    $params['store_id'] = $store_model['id'];
                    $params['product_id'] = $val['product_id'];
                    unset($params['url']);
                }
            } else if(!empty($params['amazon_product_url']) && $params['scope'] == AMAZON_PRODUCT) {
                if($asin = AmazonProductsDatatable::url2asin($params['amazon_product_url'])) {
                    if(empty($asin)) {
                        // error
                        $this->errors[] = "Bad ASIN";
                        return false;
                    }

                    $service = AmazonSearchService::getInstance();
                    $service->setParams(array(
                        'ASIN' => $asin,
                        'save_to_db' => TRUE,
                        'db_data' => array(),
                    ));
                    $service->setMethod('lookup');
                    try{
                        $service->call();
                    } catch(Exception $e) {
                        error_log($e);
                        $this->errors[] = "Amazon Prodduct Lookup error";
                        return;
                    }
                    if($service->getStatus() != 0){
                        $this->errors[] = "Amazon Prodduct Lookup error";
                        return;
                    }
                    $product = $service->getResponse();
                    $params['store_id'] = 0;
                    $params['product_id'] = $product['id'];
                    unset($params['url']);
                }
            } else {
                // error
                $this->errors[] = "Bad Arguments";
                return;
            }
        } else {// merchant coupon tool
            $params['operator'] = 'merchant';
            if(!empty($params['product_url'])) {
                if($val = parseProductUrl($params['product_url'])) {
                    global $dbconfig;
                    $subdomain = $val['store_subdomain'];
                    $store_id = $params['store_id'];
                    $store_model = BaseModel::findCachedOne($dbconfig->account->name.'.store?id='.$store_id);
                    if($store_model['subdomain'] === $subdomain) {
                        $params['product_id'] = $val['product_id'];
                        unset($params['product_url']);
                    } else {
                        $this->errors = $GLOBALS['errors'][INVALID_PRODUCT_URL];
                        return;
                    }
                }
            }
        }

        if($params['scope'] != SITE && empty($params['store_id']) && empty($params['product_id'])) {
            $this->errors = $GLOBALS['errors'][COUPON_STORE_ID_ERROR];
            $this->errors = $GLOBALS['errors'][COUPON_PRODUCT_ID_ERROR];
            return;
        }
        
        $base_list_key = $this->table_object_configs['base_list_key'];
        $account_dbobj = DAL::getDBObj($base_list_key->getDBName());
        $params['account_dbobj'] = $account_dbobj;

        $service = CouponService::getInstance();
        $service->setMethod('add_coupon');
        $service->setParams($params);
        $service->call();

        if($service->getStatus() === 1) {
            $errnos = $service->getErrnos();

            foreach($errnos as $key => $n) {
                $this->errors[] = $GLOBALS['errors'][$key];                
            }
        }
    }
    
    protected function _update() {
        $params = $this->action_params;

        $base_list_key = $this->table_object_configs['base_list_key'];
        $account_dbobj = DAL::getDBObj($base_list_key->getDBName());
        $params['account_dbobj'] = $account_dbobj;

        $row_id = $this->action_params['row_id'];
        $object_key = CacheKey::q($row_id);
        $parameters = $object_key->getParameters();
        $coupon_model = BaseModel::findCachedOne($object_key);
        
        $params['id'] = $parameters['id'];
        $params['scope'] = $coupon_model['scope'];           
        $params['store_id'] = $coupon_model['store_id'];
        $params['product_id'] = $coupon_model['product_id'];        
        $params['code'] = $coupon_model['code'];

        $service = CouponService::getInstance();
        $service->setMethod('add_coupon');
        $service->setParams($params);
        $service->call();

        if($service->getStatus() === 1) {
            $errnos = $service->getErrnos();

            foreach($errnos as $key => $n) {
                $this->errors[] = $GLOBALS['errors'][$key];                
            }
        }
        
    }

    protected function _delete() {
        $row_id = $this->action_params['row_id'];
        $object_key = CacheKey::q($row_id);
        $parameters = $object_key->getParameters();
        $dbname = $object_key->getDBName();
        $coupon_id = $parameters['id'];
        $coupon_obj = new Mycoupon(DAL::getDBObj($dbname), $coupon_id);
        $coupon_obj->setStatus(DELETED);
        $coupon_obj->save();
    }
}
