<?php

class FeaturedProductsDatatable extends BaseDatatable {
    
    public function read() {
        parent::read();
    }
    
    
    // input: 
    // product_url:
    // featured_score
    public function create() {
        
        // validate the params
        
        if(!checkIsSet($this->action_params, 'product_url', 'featured_score')) {
            $this->errors[] = $GLOBALS['errors'][INAVALID_FEATURED_PRODUCTS_CREATE_PARAMS];
            return;
        }
        
        if(!$store_subdomain_product_id = parseProductUrl($this->action_params['product_url'])) {
            $this->errors[] = $GLOBALS['errors'][INVALID_PRODUCT_URL];
            return;
        }
        
        $store_subdomain = $store_subdomain_product_id['store_subdomain'];
        $product_id = $store_subdomain_product_id['product_id'];
        
        // further validate the store subdoamin & product id
        global $dbconfig;
        $dbname = $dbconfig->account->name;
        if(!$store = BaseModel::findCachedOne(CacheKey::q($dbname.'.'.'store?subdomain='.$store_subdomain))) {
            $this->errors[] = $GLOBALS['errors'][INVALID_STORE_SUBDOMAIN];
            return;
        }
        $store_id = $store['id'];
        global $dbconfig;
        $store_dbname = $dbconfig->store->name.'_'.$store_id;
        
        if(!$product = BaseModel::findCachedOne(CacheKey::q($store_dbname.'.'.'product?id='.$product_id))) {
            $this->errors[] = $GLOBALS['errors'][PRODUCT_CANT_FIND_ERROR];
            return;
        }

        if(empty($product['name']) || $product['price'] <= 0 || empty($product['pictures']) ||
        $product['status'] == 127 || $product['global_category_id'] == 0) {
            $this->errors[] = $GLOBALS['errors'][PRODUCT_IS_INACTIVE];
            return;
        }

        if($this->table_object !== 'slider_featured_products' && $this->table_object !== 'ad_featured_products') {
            if(!GlobalCategoriesMapper::isSubCategory($this->table_object_configs['extra_params']['category']['id'], $product['global_category_id'])) {
                $this->errors[] = $GLOBALS['errors'][PRODUCT_HAS_DIFFERENT_CATEGORY];
                return;
            }
        }
        
        $base_list_key = $this->table_object_configs['base_list_key'];
        $parameters = $base_list_key->getParameters();
        $featured = $parameters['featured'];
        
        $product_obj = new Product(DAL::getDBObj($store_dbname), $product_id);
        $product_obj->setFeatured($featured);
        $product_obj->setFeaturedScore($this->action_params['featured_score']);
        $product_obj->save();
        
        $this->read();
    }
    
    
    
    public function update() {
        if(empty($this->action_params['row_id']) || !isset($this->action_params['featured_score'])) {
            $this->errors[] = $GLOBALS['errors'][INAVALID_FEATURED_PRODUCTS_UPDATE_PARAMS];
            return;
        }

        $row_id = $this->action_params['row_id'];
        $object_key = CacheKey::q($row_id);
        $parameters = $object_key->getParameters();
        $dbname = $object_key->getDBName();
        $featured_score = $this->action_params['featured_score'];
        $product_id = $parameters['id'];
        $product = new Product(DAL::getDBObj($dbname), $product_id);
        $product->setFeaturedScore($featured_score);
        $product->save();
        $this->read();
    }
    
    public function delete() {
        
        if(empty($this->action_params['row_id'])) {
            $this->errors[] = $GLOBALS['errors'][INAVALID_FEATURED_PRODUCTS_DELETE_PARAMS];
            return;
        }
        
        $row_id = $this->action_params['row_id'];
        $object_key = CacheKey::q($row_id);
        $parameters = $object_key->getParameters();
        $dbname = $object_key->getDBName();
        $product_id = $parameters['id'];
        $product = new Product(DAL::getDBObj($dbname), $product_id);
        $product->setFeatured(NOT_FEATURED);
        $product->setFeaturedScore(0);
        $product->save();
        
        $this->read();
    }
    
    public function create_form() {
        $tpl = $this->mu_engine->loadTemplate('create_form_featured_products');
        return html_entity_decode($tpl->render($this));
    }
    
    public function update_form() {
        $tpl = $this->mu_engine->loadTemplate('update_form_featured_products');
        return html_entity_decode($tpl->render($this));
    }
    
//    public static $ck = null;
//    public static $featured_type = null;
//
//    public static function create($data) {
//        global $dbconfig;
//        $store_dbname = $dbconfig->store->name;
//        
//        $account_dbobj = $data['account_dbobj'];
//        $product_url = $data['product_url'];
//        $score = $data['featured_score'];
//
//        $site_url = 'www.'.getSiteDomain();
//        $store_subdomin_and_priduct_id_reg = "/^(http:\/\/)?$site_url\/store\/?([a-zA-Z0-9]+)\/products\/item\?id=(\d+)$/";
//
//        if(preg_match($store_subdomin_and_priduct_id_reg, $product_url, $match)) {
//
//            $store_subdomin = $match[2];
//            $product_id = $match[3];
//            $store_obj = new Store($account_dbobj);
//            $store_obj->findOne("subdomain='".$account_dbobj->escape($store_subdomin)."'");
//            $store_dbobj = DBobj::getStoreDBObj($store_obj->getHost(), $store_obj->getId());
//
//            if(ProductsMapper::isActiveProduct($store_dbobj, $product_id)) {
//                $product_obj = new Product($store_dbobj, $product_id);
//                $product_obj->setFeatured(self::$featured_type);
//                $product_obj->setFeaturedScore($score);
//                $product_obj->save();
//                return array('status' => 'success');
//            }
//
//        }
//        return array('status' => 'failure');
//    }
//        
//    public static function update($data){
//
//        $key = $data['id'];
//        $score = $data['featured_score'];
//
//        $ck = CacheKey::q($key);
//        $store_name = $ck->getDBName();
//        $parameters = $ck->getParameters();
//        $product_id = $parameters['id'];
//
//        $store_dbobj = DBobj::getStoreDBObjByDBName($store_name);
//        $product_obj = new Product($store_dbobj, $product_id);
//        $product_obj->setFeaturedScore($score);
//        $product_obj->save();
//        return array('status' => 'success');            
//
//    }
//    
//    public static function read($data=array()) {
//
//        $current_page = default2Int($data['page'], 1);
//        $items_per_page = default2Int($data['items_per_page'], DATATABLE_ITEMS_PER_PAGE);
//
//        $response = array();
//
//        $return = BaseMapper::getCachedObjects(self::$ck, $current_page, $items_per_page);
//
//        $response['current_page'] = $return['current_page'];
//        $response['items_per_page'] = $items_per_page;
//        $response['rows'] = $return['data'];
//        $response['status'] = 'success';
//        return $response;
//    }
//    
//    public static function delete($data) {
//
//        $key = $data['id'];
//
//        $ck = CacheKey::q($key);
//        $store_name = $ck->getDBName();
//        $parameters = $ck->getParameters();
//        $product_id = $parameters['id'];
//
//        $store_dbobj = DBobj::getStoreDBObjByDBName($store_name);
//        $product_obj = new Product($store_dbobj, $product_id);
//        $product_obj->setFeatured(NOT_FEATURED);
//        $product_obj->setFeaturedScore(0);
//        $product_obj->save();
//
//        return array('status' => 'success');
//    }
//
//    public static function generateList($account_dbobj) {
//        global $dbconfig;
//        $store_dbname = $dbconfig->store->name;
//
//        self::purgeList();
//
//        $store_info = StoresMapper::getAllStoreInfo($account_dbobj, 0, PHP_INT_MAX, ACTIVATED);
//
//        foreach($store_info as $store) {
//            $store_id = $store['id'];
//            $store_dbobj = DBObj::getStoreDBObjById($store_id);
//
//            $products = StoresMapper::getFeaturedProducts($store_dbobj);
//
//            foreach($products as $product) {
//                $product_id = $product['id'];
//                $product_score = $product['featured_score'];
//                
//                $key = $store_dbname.'_'.$store_id.".product?id=$product_id";
//                
//                DAL::addToList(
//                    self::$ck,
//                    array($key => $product_score));
//            }
//        }
//
//        return array('status' => 'success');
//    }
//
//    public static function purgeList() {
//        DAL::delete(self::$ck);
//        return array('status' => 'success');        
//    }
}
