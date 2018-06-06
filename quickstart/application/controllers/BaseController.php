<?php

class BaseController extends Zend_Controller_Action {
    
    public function preDispatch() {
        $this->initialize();
        $this->router();
        $this->init_view();
        $this->init_user_store();
    }
    
    public function postDispatch() {

    }
    
    private function initialize() {
        global $page_acl, $account_dbobj, $job_dbobj, $site_version;
        $this->page_acl = $page_acl;
        $this->account_dbobj = $account_dbobj;
        $this->job_dbobj = $job_dbobj;
        $this->is_shopinterest_product_item = false;
        
        // url forward indicator
        $this->doforward = false;        

        Redis_Session::start();
        $this->user_session = new Redis_Session_Namespace('user');  
        $this->user_session->referrer = default2String($_SERVER['HTTP_REFERER'], '/');

        // get the role of the visitor
        $this->role = $this->get_vistor_role();        
        
        // get site_domain, subdomain, subdomain_type
        $this->site_domain = getSiteDomain();
        $this->subdomain = getSubdomain($this->site_domain);
        $this->subdomain_type = getSubdomainType($this->subdomain);

        $this->page_path = $this->get_page_path();
        
        // get controller, action, uri, params, site_version
        $this->controller = $this->getControllerName();
        $this->action = $this->getActionName();
        $this->uri = '/'.$this->controller.'/'.$this->action;
        $this->full_uri = $_SERVER['REQUEST_URI'];
        $this->site_version = $site_version;
    }
    
    protected function router() {

        global $site_versions;

        $site_domain = array();
        foreach($site_versions as $version => $info) {
            $site_domain[$version] = $info->domain;
        }

        if($_SERVER['HTTP_HOST'] == "www." . $site_domain[1]){
            $scheme = $this->is_secure_connect() ? 'https://' : 'http://';
            redirect($scheme . "www." . $site_domain[2] .  $_SERVER['REQUEST_URI']);
        }

        // this code is for cookie-cross-subdomain issue
        /*
          if($_SERVER['HTTP_HOST'] === $this->site_domain) {
          redirect(getSiteMerchantUrl($this->uri)); // staging.shopinterest.co => www.staging.shopinterest.co
          }
        */
        
        // if the subdomain type is store, we need to redirect it to www
        if($this->subdomain_type === 'store' && !$this->is_shopinterest_product_item) {
            // http://splurge316.shopinterest.co/products/item?id=4
            // http://www.shopinterest.co/store/splurge316/products/item?id=4
            redirect(getSiteMerchantUrl('/store/'.$this->subdomain.$this->full_uri));
        }
        if(!$this->page_exists() || !$this->role_check()) {
            $this->redirect_to_default_page();
        } else if($this->doforward) {
            $this->_forward($this->action, $this->controller);
        }

        if(https_enable()) {
            $this->scheme_toggle();
        }

    } 
      
    private function init_view() {

        // common view data
        $this->view->site_version = $this->site_version;
        $this->view->role = $this->role;
        $this->view->is_anonymous = $this->is_anonymous();
        $this->view->is_user = $this->is_user();
        $this->view->is_merchant = $this->is_merchant();
        $this->view->is_admin = $this->is_admin();
        $this->view->auctions = $this->getAuctionList();
        $body_id = $this->controller.'_'.$this->action;
        $this->view->body_id = $body_id;
        $this->view->$body_id = 1;
        $this->view->controller = $this->controller;
        $this->view->action = $this->action;
        $this->view->subdomain_type = $this->subdomain_type;
        $this->view->errnos = $GLOBALS['errnos'];
        $this->view->errors = $GLOBALS['errors'];
        $this->view->page_url = getRequestUrl();
        $this->view->user_id = empty($this->user_session->user_id)?0:$this->user_session->user_id;

        $this->view->merchant_id = empty($this->user_session->merchant_id)?0:$this->user_session->merchant_id;
        $this->view->referrer = $this->user_session->referrer;
        $cached_categories = BaseMapper::getCachedObjects(lck_categories());
        $this->view->global_categories = $cached_categories['data'];
        $this->view->store_subdomain = $this->get_store_subdomain();
        $cart_id = CartsMapper::findCurrentCartForUser($this->account_dbobj, $this->view->user_id, False);
        $this->view->nav_cart_num = CartsMapper::getItemsCntInCart($this->account_dbobj, $cart_id);

        $ad_ck = lck_featured_products(AD_FEATURED);
        $this->view->ad_featured_products = BaseMapper::getCachedObjects($ad_ck);
        
        if($this->view->ad_featured_products['total_rows'] == 0) {
            $this->view->ad_featured_products = AmazonSearchService::getFeaturedProducts();
        }
        if(!empty($this->user_session->user_id)){
            $user_obj = new User($this->account_dbobj, $this->user_session->user_id);
            if($user_obj->getId()>0){
                $user_obj->setLastActivity(get_current_datetime());
                $user_obj->save();
            }
        }
    }
    
    private function init_user_store() {
        global $dbconfig;
        $account_dbname = $dbconfig->account->name;

        $this->user = NULL;
        $this->store = NULL;
        $this->store_dbobj = NULL; 
        $this->visit_store = NULL;       
        $this->visit_store_dbobj = NULL;
        $this->user_session->visit_store_id = 0;

        if(!$this->view->is_anonymous) {
            $this->user= BaseModel::findCachedOne(CacheKey::q($account_dbname.'.user?id='.$this->view->user_id));
            $this->view->user = $this->user;
            if(empty($this->user['merchant_id'])){
                $this->user_session->merchant_id = 0;
            }
        }

        //if($this->view->is_merchant) {
        if($this->is_merchant()) {
            $this->store = BaseModel::findCachedOne(CacheKey::q($account_dbname.'.store?id='.$this->user['store_id']));
            $this->store_dbobj = DBobj::getStoreDBObj($this->store['host'], $this->store['id']);
            $this->view->store = $this->store;
        }

        if(!empty($this->view->store_subdomain)) {
            $this->visit_store = BaseModel::findCachedOne(CacheKey::q($account_dbname.'.store?subdomain='.$this->view->store_subdomain));
            if(empty($this->visit_store)) { //redirect to home page if subdomain is not exist
                redirect(getSiteMerchantUrl());                
            }
            $this->visit_store_dbobj = DBobj::getStoreDBObj($this->visit_store['host'], $this->visit_store['id']);
            $this->view->visit_store = $this->visit_store;
            $this->visit_merchant = BaseModel::findCachedOne(CacheKey::q($account_dbname.'.user?id='.$this->visit_store['uid']));
            $this->view->visit_merchant = $this->visit_merchant;
            $this->user_session->visit_store_id = $this->visit_store['id'];
        }

        $this->view->store = $this->store;
    }

    protected function getControllerName() {
        global $site_version;
        if($this->subdomain_type === 'store' && $site_version == 1) {
            if(strpos($_SERVER['REQUEST_URI'], '/products/item') !== FALSE) {
                $this->is_shopinterest_product_item = true;
            }
            return 'store';
        } else {
            return $this->_request->getControllerName();
        }
        
    }
    
    protected function getActionName() {
        static $i = 0;
        if($i === 0) {
            $request_path = $this->get_page_path();
            
            // check if the uri has three parts
            $parts = get_path_parts($request_path);
            $parts_cnt = sizeof($parts);

            if($this->controller === 'store' || $this->controller === 'category') {
                if($parts_cnt === 1) {
                    redirect(getSiteMerchantUrl());
                } else if($parts_cnt === 2) {
                    if($this->is_shopinterest_product_item) {
                        
                    } else {
                        $parts[1] = 'index';
                    }
                    
                } else if($parts_cnt > 2) {
                    $parts = array_shift_over($parts, 1);
                }
            }

            $parts_cnt = sizeof($parts);
            if(sizeof($parts) > 2 || $this->is_shopinterest_product_item) {
                if($this->is_shopinterest_product_item) {
                    $this->_request->setActionName(join('-', $parts));
                } else {
                    $this->_request->setActionName(join('-', array_slice($parts, 1)));
                }

                for($j=1;$j<$parts_cnt;$j++) {
                    $varname = 'action'.($j);
                    $this->view->$varname = $parts[$j];
                }
                $this->doforward = true;
                
            } else if($parts_cnt>0){
                if($this->controller === 'store' || $this->controller === 'category') {
                    $this->view->action1 = $parts[1];
                    $this->_request->setActionName($parts[1]);
                    $this->doforward = true;
                } else {
                    $this->view->action1 = $this->_request->getActionName();
                }
                
            } else {
                $this->view->action1 = 'index';
                $this->_request->setActionName('index');
                $this->doforward = true;
            }
            $i++;
        }
        return $this->_request->getActionName();
    }

    protected function get_vistor_role() {
        $role = 0;
        if(!empty($this->user_session->user_id)) {
            $role = $role | USER;
        }
        if($role === USER) {
            if(!empty($this->user_session->merchant_id)) {
                $role = $role | MERCHANT;
            }
            if($this->is_admin()) {
                $role = $role | ADMIN;
            }
        }
        if($role === 0) {
            $role = ANONYMOUS;
        }
        return $role;
    }
    
    protected function is_anonymous() {
        return empty($this->user_session->user_id);
    }

    protected function is_user() {
        return !empty($this->user_session->user_id);
    }
    
    protected function is_merchant() {
        if(isset($this->user) && !empty($this->user)){
            return !empty($this->user['merchant_id']);
        }
        return !empty($this->user_session->merchant_id); 
    }

    protected function is_admin() {
        global $redis, $admin_account;
        $username = $redis->get("user:{$this->user_session->user_id}:username");
        return in_array($username, $admin_account);
    } 

    // internal pages don't include the admin page
    private function is_internal_page() {

        $page_role = array(ANONYMOUS);
        if(isset($this->page_acl[$this->uri])) {
            $page_role = $this->page_acl[$this->uri]['role'];
        }
        
        if(in_array(ANONYMOUS, $page_role) || in_array(ADMIN, $page_role)) {
            return false;
        } else {
            return true;
        }
    }
    
    private function get_page_path() {
        $request_path = default2String($_SERVER['REDIRECT_URL']);
        $request_path = preg_replace("/\?.*$/", "", $request_path);
        return trim($request_path, '/');
    }
    
    private function get_store_subdomain() {
        if($this->is_shopinterest_product_item) {
            return $this->subdomain;
        }
        preg_match("#^(/store/)([^/?]+)(.*)$#", $this->full_uri, $matches);
        return default2String($matches[2], '');
    }
    
    private function is_secure_page($page_scheme = '') { 
        if(empty($page_scheme)) {
            $page_scheme = $this->get_page_scheme();
        }
        return $this->subdomain_type === 'merchant' && $page_scheme === HTTPS;
    }    
    
    private function get_page_scheme() {
        return $this->page_acl[$this->uri]['scheme']; 
    }

    private function redirect_to_default_page() {
        if($this->is_user()) {
            redirect(getSiteMerchantUrl('/dashboard'));
        } else {
            if($this->is_internal_page()) {
                $redirect_uri = "?next=$this->uri";
                redirect(getSiteMerchantUrl("/login$redirect_uri"));
            } else {
                redirect(getSiteMerchantUrl());
            }
        }
    }
        
    private function page_exists() {
        return array_key_exists($this->uri, $this->page_acl);       
    }
    
    private function role_check() {
        $page_acl = $this->page_acl;
        $role = $this->role;

        if($this->is_admin()) {
            return true;
        }
        $allowed_roles = $page_acl[$this->uri]['role'];

        foreach($allowed_roles as $allowed_role) {
            $check_result = $role & $allowed_role;
            if($check_result !== 0) {
                return true;
            }
        }
        return false;        
    }  
    
    private function is_secure_connect() {
        return $this->_request->isSecure();
    }

    private function scheme_toggle() {
        $page_scheme = $this->get_page_scheme();
        if($page_scheme === BOTH) {
            return;
        }
        $is_secure_connect = $this->is_secure_connect();
        $is_secure_page = $this->is_secure_page($page_scheme);
        
        if($page_scheme === HTTPS) {
            $scheme = 'https://' ;
        } else if($page_scheme === HTTP) {
            $scheme = 'http://' ;
        }
        
        if(!$is_secure_page && $is_secure_connect|| // http -> https
            $is_secure_page && !$is_secure_connect) { // https -> http  

            redirect(get_url2($this->subdomain, $this->full_uri, $scheme));
        }                    
    }

    private function getAuctionList() {
        $auction_list = AuctionsMapper::getAuctionsForShopper($this->account_dbobj);
        return $auction_list;
    }
    
    // api output:
    // status: success/failure
    // data: {rows: [], total_rows: xxx, current_page: xxx, html: xxx}
    // errors: []
    protected function datatablePreprocess() {
        
        $return = array('status'=>'failure', 'data'=>array(), 'errors'=>array());
        $tableConfigs = DatatableService::getTableConfigs();

        if(empty($_REQUEST['table_object'])) {
            $_REQUEST['table_object'] = arrayFirstKey($tableConfigs);
        }
        if(empty($_REQUEST['render'])&&$this->controller !== 'api') {
            $_REQUEST['render'] = array('container');
        } else if(empty($_REQUEST['render'])&&$this->controller === 'api') {
            $_REQUEST['render'] = array();
        }
        $render = $_REQUEST['render'];
        
        $table_object = $_REQUEST['table_object'];
        $action = isset($_REQUEST['action'])?trim($_REQUEST['action'], '_'):'read';
        $action_params = isset($_REQUEST['action_params'])?$_REQUEST['action_params']:array('condition_string' => '', 'conditions' => array());

        if($this->store_dbobj !== NULL){
            $action_params['dbname'] = $this->store_dbobj->getDBName();
        }
        if(!isset($action_params['condition_string'])) {
            $action_params['condition_string'] = '';
        } 
        if(!isset($action_params['conditions'])) {
            $action_params['conditions'] = array();
        }
        if(empty($_REQUEST['page'])) {
            $page = 1;
        } else {
            $page = $_REQUEST['page'];
        }

        $view_data = array();

        foreach($this->view as $name => $value) {
            if (substr($name, 0, 1) == '_') continue;
            $view_data[$name] = $value;
        }

        $service = DatatableService::getInstance();
        $service->setMethod('process');
        $service->setParams(array(
            'account_dbobj' => $this->account_dbobj,
            'table_object' => $table_object,
            'action' => $action,
            'action_params' => $action_params,
            'render' => $render,
            'page' => $page,
            'view_data' => $view_data,
        ) );
        $service->call();
        $return['data'] = $service->getResponse();
        $return['errors'] = $service->getErrors();

        if($service->getStatus() === 0) {
            $return['status'] = 'success';
        }

        if($this->controller !== 'api') {
            $this->view->table_object = $table_object;
            $this->view->datatable_view = $return['data']['views']['container'];
        } else {
            echo json_encode($return);
        }
        
    }
    
}
