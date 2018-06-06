<?php 
class CouponService extends BaseService {
    
    public function saveDeal(){
        $data = $this->params;
        $account_dbobj = $this->params['account_dbobj'];
        $offer_details = array();
        if(empty($data['store_id'])) {
            $this->errnos[DEAL_STORE_ID_ERROR] = 1;
            $this->status = 1;
        }    
        if(empty($data['product_id'])) {
            $data['product_id'] = 0;
            $this->status = 1;
        }
        if(empty($data['scope'])) {
            $this->errnos[DEAL_SCOPE_ERROR] = 1;
            $this->status = 1;
        }    
        if(empty($data['start_time'])) {
            $this->errnos[DEAL_START_TIME_ERROR] = 1;
            $this->status = 1;
        }    
        if(empty($data['end_time'])) {
            $this->errnos[DEAL_END_TIME_ERROR] = 1;
            $this->status = 1;
        }
        if(empty($data['price_offer_type']) && empty($data['shipping_offer_type'])) {
            $this->errnos[DEAL_OFFER_TYPE_ERROR] = 1;
            $this->status = 1;
        } else {
            $data['price_offer_type'] = empty($data['price_offer_type'])?0:$data['price_offer_type'];
            $data['shipping_offer_type'] = empty($data['shipping_offer_type'])?0:$data['shipping_offer_type'];
        }
        if(empty($data['price_offer_value']) && empty($data['shipping_offer_value'])) {
            $this->errnos[DEAL_OFFER_VALUE_ERROR] = 1;
            $this->status = 1;
        }
        if(empty($data['quantity'])) {
             $this->errnos[DEAL_USAGE_LIMIT_ERROR] = 1;
             $this->status = 1;
        }       
        if( ( empty($data['product_id']) && $data['scope']>2 ) || (!empty($data['product_id']) && $data['scope']<3) ) {
            $this->errnos[DEAL_SCOPE_ERROR] = 1;
            $this->status = 1;
        }    
        if(!empty($data['price_offer_type']) && !empty($data['price_offer_value']) && $data['price_offer_type'] == 1) {    
            // pecentage off offer
            if($data['price_offer_value'] > 100) {
                $this->errnos[DEAL_OFFER_VALUE_ERROR] = 1;
                $this->status = 1;
            } else {
                $offer_details['price']['percentage_off'] = $data['price_offer_value'];
            }    
        }
        if(!empty($data['shipping_offer_type']) && !empty($data['shipping_offer_value']) && $data['shipping_offer_type'] == 1) {    
            if($data['shipping_offer_value'] > 100 ){
                $this->errnos[DEAL_OFFER_VALUE_ERROR] = 1;
                $this->status = 1;
            } else {
                $offer_details['shipping']['percentage_off'] = $data['shipping_offer_value'];
            }    
        }
        if(!empty($data['price_offer_type']) && !empty($data['price_offer_value']) && $data['price_offer_type'] == 2 ) {
            $offer_details['price']['flat_value_off'] = $data['price_offer_value'];            
        }
        if(!empty($data['shipping_offer_type']) && !empty($data['shipping_offer_value']) && $data['shipping_offer_type'] == 2 ) {
            $offer_details['shipping']['flat_value_off'] = $data['shipping_offer_value'];
        }    
        if(empty($offer_details)) {
            $this->errnos[DEAL_OFFER_VALUE_ERROR] = 1;
            $this->status = 1;
        }    
        else {
            $data['offer_details'] = $offer_details;
        }    
        if(empty($this->errnos)) {
            $coupon = new Coupon($account_dbobj);
            $coupon->findOne('code='.$data['coupon_code']);
            $coupon->setStatus($data['status']);
            $coupon->setCode($data['coupon_code']);
            $coupon->setScope($data['scope']);
            $coupon->setStartTime($data['start_time']);
            $coupon->setEndTime($data['end_time']);
            $coupon->setPriceOfferType($data['price_offer_type']);
            $coupon->setShippingOfferType($data['shipping_offer_type']);
            $coupon->setOfferDetails($data['offer_details']);
            $coupon->setOfferDescription($data['offer_description']);
            $coupon->setUsageLimit($data['quantity']);
            $coupon->setStoreId($data['store_id']);
            $coupon->setProductId($data['product_id']);
            $coupon->save();
            $this->response = $data;
        }       
    }
    
    
    public function add_coupon() {

        $account_dbobj = $this->params['account_dbobj'];
        if(isset($this->params['id'])){
            $id = $this->params['id'];
        }
        $code = default2String($this->params['code'], uniqid());
        $offer_name = default2String($this->params['offer_name']);
        $offer_description = default2String($this->params['offer_description']);
        $scope = default2Int($this->params['scope']);
        $category = default2String($this->params['category']);
        $price_offer_type = default2Int($this->params['price_offer_type']);
        $price_off = default2Int($this->params['price_off']);
        $free_shipping = set2bool($this->params['free_shipping']);
        if($scope != PRODUCT){
            $free_shipping = FALSE;
        }
        $usage_limit = default2Int($this->params['usage_limit']);
        $usage_restriction = default2Int($this->params['usage_restriction']);
        $store_id = default2Int($this->params['store_id']);
        $product_id = default2Int($this->params['product_id']);
        $start_time = default2String($this->params['start_time']);
        $end_time = default2String($this->params['end_time']);
        $is_sale = set2bool($this->params['is_sale']);
        $is_deal = set2bool($this->params['is_deal']);
        $operator = default2String($this->params['operator'], 'merchant');

        $conpon_obj = New Mycoupon($account_dbobj);
        $conpon_obj->findOne("code = '".$account_dbobj->escape($code)."'");
                
        if(empty($scope) || !in_array($scope, array(SITE, STORE, PRODUCT, AMAZON_PRODUCT))) {
            $this->errnos[COUPON_SCOPE_ERROR] = 1;
        }

        if(($scope == PRODUCT || $scope == STORE) && empty($store_id)){
            $this->errnos[COUPON_STORE_ID_ERROR] = 1;          
        }
        
        if(($scope == PRODUCT || $scope == AMAZON_PRODUCT) && empty($product_id)) {
            $this->errnos[COUPON_PRODUCT_ID_ERROR] = 1;
        }  
        
        if($scope == PRODUCT && 
            !isset($this->errnos[COUPON_STORE_ID_ERROR]) && 
            !isset($this->errnos[COUPON_PRODUCT_ID_ERROR])) {
            
            $store_obj = new Store($account_dbobj, $store_id);
            $store_host = $store_obj->getHost();
            $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);
            if(!ProductsMapper::isActiveProduct($store_dbobj, $product_id)) {
                $this->errnos[COUPON_PRODUCT_ID_ERROR] = 1;
            }
        }

        if(empty($price_offer_type) || !in_array($price_offer_type, array(PERCENTAGE_OFF, FLAT_VALUE_OFF))) {
            $this->errnos[COUPON_OFFER_TYPE_ERROR] = 1;
        }
        
        if(empty($price_off) || $price_off<=0 || ($price_offer_type == PERCENTAGE_OFF && ($price_off>=100))) {
            $this->errnos[COUPON_OFFER_VALUE_ERROR] = 1;
        }
        
        if(empty($start_time)) {
            $this->errnos[COUPON_START_TIME_ERROR] = 1;
        }

        if(
            empty($end_time)
            || (strtotime2($end_time) < strtotime2($start_time))
            || (strtotime2($end_time) < strtotime2(get_current_datetime()))
        ) {
            $this->errnos[COUPON_END_TIME_ERROR] = 1;
        }
        
        if($usage_limit <= 0) {
            $this->errnos[COUPON_USAGE_LIMIT_ERROR] = 1;
        }
        
        if($usage_restriction <= 0) {
            $this->errnos[COUPON_USAGE_RESTRICTION_ERROR] = 1;
        }

        if(empty($this->errnos)) {
            $conpon_attributes = array_keys($conpon_obj->_toArray());
            $array_objects = compact($conpon_attributes);
            BaseModel::saveObjects($account_dbobj, $array_objects, 'mycoupon');
            $this->response = $array_objects;
        } else {
            $this->status = 1;
        }       
    }
    
    public function get_coupons() {
        
        $account_dbobj = $this->params['account_dbobj'];

        $response = MycouponsMapper::get_coupons($account_dbobj, $this->params);
        
        $this->response = $response;
    }
    
}