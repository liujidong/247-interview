<?php

class Store extends BaseModel {

    public function setSubdomain($subdomain) {
        $subdomain = sanitize_string($subdomain);

        if(validate($subdomain, 'subdomain')) {
            $this->_subdomain = $subdomain;
            return true;
        } else {
            return false;
        }
    }

    public function setTax($tax) {
        $this->_tax = round(floatval2($tax), 2);
    }

    public function setShipping($shipping) {
        $this->_shipping = round(floatval2($shipping), 2);
    }

    public function setAdditionalShipping($additional_shipping) {
        $this->_additional_shipping = round(floatval2($additional_shipping), 2);
    }

    public function setLogo($logo) {
        if(validate($logo, 'url')) {
            $this->_logo = $logo;
            return true;
        } else {
            return false;
        }
    }

    public function getTax() {
        return round($this->_tax, 2);
    }

    public function getShipping() {
        return round($this->_shipping, 2);
    }

    public function getAdditionalShipping() {
        return round($this->_additional_shipping, 2);
    }

    public static function format($data, $ck){
        if(empty2($data['converted_logo'])) {
            $data['converted_logo'] = "/img/merchant_placeholder.jpg";
        } else if(!startsWith($data['converted_logo'], 'http')){ // cloudinary
            $filename = $data['converted_logo'];
            $folder = cloudinary_store_misc_ns($data['id']);
            $options = array("width" => 140, "height" => 140, "crop" => "fill");
            $store_logo_url = cloudinary_url($folder . $filename . ".jpg", $options);
            $data['converted_logo'] = $store_logo_url;
        }
        if(empty2($data['logo'])) $data['logo'] = "/img/merchant_placeholder.jpg";
        if(!empty2($data['external_website']) && !startsWith($data['external_website'], 'http')) {
            $data['external_website'] = "http://" . $data['external_website'];
        }
        if(empty($data['country'])) $data['country'] = "US";
        $data['url'] = getStoreUrl($data['subdomain']);
        $data['literal_transaction_fee_waived'] = $data['transaction_fee_waived'] ? 'Y' : 'N';

        switch ($data['status']) {
        case 0:
            $store_status = "Created";
            break;
        case 1:
            $store_status = "Pending";
            break;
        case 2:
            $store_status = "Active";
            break;
        default:
            $store_status = $data['status'];
        }
        $data['literal_status'] = $store_status;
        $data['description'] = nl2br(strip_tags($data['description']));
        return $data;
    }

    public static function basicLaunchCheck(&$store, $restrict=TRUE, $merchant = NULL){
        global $dbconfig, $account_dbobj;
        $active_products_cnt = DAL::getListCount(lck_store_active_products($dbconfig->store->name . "_" . $store['id']));
        if($active_products_cnt <= 0){
            if($store['status'] == ACTIVATED){
                $store_obj = new Store($account_dbobj, $store['id']);
                $store_obj->setStatus(PENDING);
                $store_obj->save();
                GlobalProductsMapper::deleteProductsInStore($account_dbobj, $store['id']);
            }
            return FALSE;
        }
        if(!$restrict) return TRUE;

        if(empty($merchant)){
            $merchant = BaseModel::findCachedOne($dbconfig->account->name . ".user?id=" . $store['uid']);
        }
        $launch_cond = !(
            empty($merchant['first_name']) || empty($merchant['last_name']) ||
            empty($merchant['addr1']) || empty($merchant['city'])||
            empty($merchant['state']) ||empty($merchant['country'])||
            empty($merchant['zip']) || empty($merchant['phone']) ||
            empty($merchant['paypal_email']) ||
            empty($store['name']) || empty($store['subdomain']) ||
            empty($store['return_policy'])
        );
        return $launch_cond;
    }

    public static function isSubscribed(&$store){
        $ret = isset($store['subscribed']) && isset($store['subscr_id']);
        $ret = $ret && (!empty($store['subscribed'])) && $store['subscribed'] !== '0000-00-00 00:00:00';
        $ret = $ret && (!empty($store['subscr_id']));
        return $ret;
    }

    public static function canLaunch(&$store, $restrict=TRUE, $merchant = NULL){
        return self::basicLaunchCheck($store, $restrict, $merchant);// && self::isSubscribed($store);
    }

    public static function isLaunched(&$store, $merchant=NULL){
        return self::basicLaunchCheck($store, FALSE, $merchant) && $store['status'] == ACTIVATED;
    }

    public static function findStoreByDBObj($store_dbobj){
        global $dbconfig;
        $store_dbname = $store_dbobj->getDBName();
        $store_id = preg_replace("/.*_/", "", $store_dbname);
        $ck = CacheKey::q($dbconfig->account->name . ".store?id=$store_id");
        $store = BaseModel::findCachedOne($ck);
        return $store;
    }

    public static function clearCache($store_id){
        global $dbconfig;
        $store_ck = $dbconfig->account->name . ".store?id=" . $store_id;
        DAL::delete(CacheKey::q($store_ck));
        $store = BaseModel::findCachedOne($store_ck);
        DAL::delete(CacheKey::q($dbconfig->account->name . ".store?subdomain=" . $store['subdomain']));
    }
}
