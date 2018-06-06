<?php

class SellingController extends BaseController {

    public function init() {

    }

    public function productsAction() {
        if($this->store['allow_resell']){
            redirect(getSiteMerchantUrl('/selling/products/resell'));
        }
        $status = isset($_GET['status']) ? $_GET['status'] : 'active';
        if(!in_array($status, array('active', 'inactive'))) $status = 'active';

        $page = default2Int($_GET['page'], 1);

        $store_dbname = $this->store_dbobj->getDBName();
        $active_product_ck = lck_store_active_products($store_dbname);
        $inactive_product_ck = lck_store_inactive_products($store_dbname);

        $ck = $status === 'active' ? $active_product_ck : $inactive_product_ck;

        $response = BaseMapper::getCachedObjects($ck, $page, CREATE_PRODUCT_NUM_PER_PAGE);

        $this->view->active_products_count = DAL::getListCount($active_product_ck);
        $this->view->inactive_products_count = DAL::getListCount($inactive_product_ck);
        $this->view->products = $response['data'];
        $this->view->shipping_options = ShippingOptionsMapper::getShippingOptions($this->store_dbobj);
        $this->view->status = $status;
        //dddd($this->view->products);
        $this->view->total_rows = $response['total_rows'];
        $this->view->rows_per_page = CREATE_PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page;
        $extra_params = array(
            'status' => $status
        );
        $this->view->extra_params = $extra_params;
    }

    public function productsResellAction() {

        if($this->store['allow_resell'] == 0){
            redirect(getSiteMerchantUrl('/selling/products'));
        }

        $page = default2Int($_GET['page'], 1);

        $store_dbname = $this->store_dbobj->getDBName();
        $ck = lck_store_resell_products($store_dbname);

        $response = BaseMapper::getCachedObjects($ck, $page, CREATE_PRODUCT_NUM_PER_PAGE);

        $this->view->products = $response['data'];
        $this->view->total_rows = $response['total_rows'];
        $this->view->rows_per_page = CREATE_PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page;
    }

    public function productsShippingAction() {
        $store_dbobj = $this->store_dbobj;

        $response = ShippingOptionsMapper::getShippingOptions($store_dbobj);

        $this->view->shipping_options = $response;
    }

    public function ordersAction() {
        $page = default2Int($_GET['page'], 1);

        $_REQUEST['table_object'] = 'myorders';
        $status = '';
        $p_status = isset($_GET['status']) ? $_GET['status'] : '';
        if($p_status == 'canceled'){
            $status = ORDER_CANCELED;
        }elseif($p_status == 'unpaid'){
            $status = ORDER_UNPAID;
        }elseif($p_status == 'paid'){
            $status = ORDER_PAID;
        }elseif($p_status == 'shipped'){
            $status = ORDER_SHIPPED;
        }elseif($p_status == 'completed'){
            $status = ORDER_COMPLETED;
        }
        $cond = 'store_id=' . $this->store['id'];
        if($status !== ''){
            $cond .= "&payment_status=$status";
        }
        $_REQUEST['action_params']['condition_string'] = $cond;
        $this->datatablePreprocess();
        //dddd($this->view->datatable_view);/selling/orders
        //$total_cnt = MyordersMapper::getOrdersCntForMerchant($this->account_dbobj, $this->store['id'], $status);
        //$orders = MyordersMapper::getOrdersForMerchant($this->account_dbobj, $this->store['id'], $status, $page);
        //$this->view->total_rows = $total_cnt;
        //$this->view->rows_per_page = SHIPPING_OPTIONS_NUM_PER_PAGE;
        //$this->view->page_num = $page;
    }

    public function ordersDetailAction() {
        $id = default2Int($_GET['id'], 1);
        if($id<1){
            redirect(getSiteMerchantUrl("/dashboard"));
        }
        global $dbconfig;
        $ck = CacheKey::q($dbconfig->account->name . ".myorder?id=$id");
        $myorder = BaseModel::findCachedOne($ck, array('force'=>true));

        if((!$this->is_admin() && $myorder['store_id'] != $this->store['id']) || $myorder['status'] == DELETED ) {
            redirect(getSiteMerchantUrl("/dashboard"));
        }
        $grp_id = $myorder['myorder_group_id'];
        $grp = new MyorderGroup($this->account_dbobj, $grp_id);
        $buyer_id = $grp->getUserId();
        $buyer = BaseModel::findCachedOne($dbconfig->account->name . ".user?id=$buyer_id");

        $service = NativeCheckoutService::getInstance();
        $service->setMethod("myorder_summary");
        $params = array(
            'account_dbobj' => $this->account_dbobj,
            'order_group' => $grp,
            'save_summary' => false,
        );
        $service->setParams($params);
        $service->call();

        $response = $service->getResponse();

        $this->view->order_group = $grp;
        $sock = CacheKey::q($dbconfig->store->name . "_" . $this->store['id'] . ".shipping_option?id=".$myorder['shipping_option_id']);
        $this->view->shipping_opt = BaseModel::findCachedOne($sock);
        $this->view->items = $response['items_by_store'][$this->store['id']];
        $this->view->store_summary = $response['store_summaries'][$this->store['id']];
        $this->view->order = $myorder;
        $this->view->store = $this->store;
        $this->view->buyer = $buyer;
    }

    public function toolsAnalyticsAction() {

        $_REQUEST['table_object'] = 'analytics';
        $store_subdomin = $this->store['subdomain'];
        $store_url_filter = '/store/'.$store_subdomin;
        $_REQUEST['action_params']['store_url'] = $store_url_filter;
        $_REQUEST['action_params']['store_id'] = $this->store['id'];
        $_REQUEST['action_params']['from'] = isset($_REQUEST['from']) ? $_REQUEST['from'] : getNdaysago();
        $_REQUEST['action_params']['to'] = isset($_REQUEST['to']) ? $_REQUEST['to'] : getNdaysago(0);

        $this->datatablePreprocess();
    }

    public function toolsCustomersAction() {
        $_REQUEST['table_object'] = 'store_customers';

        $this->view->replyto_email = $this->user['username'];
        $this->view->replyto_name = $this->user['first_name'].$this->user['last_name'];
        $this->datatablePreprocess();
    }

    public function toolsPinstoreAction() {

    }

    public function toolsCouponAction() {
        $_REQUEST['table_object'] = 'store_coupon';
        $_REQUEST['action_params']['condition_string'] = "store_id=".$this->store['id'];
        $_REQUEST['action_params']['store_id'] = $this->store['id'];        
        $this->datatablePreprocess();        
    }

    public function settingsAction() {
        global $site_domain, $dbconfig, $currencies;

        //dddd($this->view->my_store_id);
        $this->view->site_domain = $site_domain;

        $account_dbname = $dbconfig->account->name;
        $user_id = $this->user_session->user_id;
        $user = BaseModel::findCachedOne(CacheKey::q($account_dbname.'.user?id='.$user_id));
        $store = BaseModel::findCachedOne(CacheKey::q($account_dbname.'.store?id='.$user['store_id']));
        $countries = BaseMapper::getCachedObjects(CacheKey::q($account_dbname.'.countries'));
        $countries = $countries['data'];

        $this->view->store = $store;
        $this->view->countries = $countries;
        $this->view->currencies = $currencies;
    }

    public function previewAction() {
        global $dbconfig;

        // Store Info
        $store = $this->store;
        $this->view->store = $store;
        $store_dbobj = $this->store_dbobj;
        $store_dbname = getStoreDBName($store['id']);
        // Country
        $country_info =  BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name.".country?iso2=".$store['country']));
        $this->view->country =  $store['country'];
        $this->view->country_name = $country_info['short_name'];
        $this->view->currency = $store['currency'];
        $this->view->currency_symbol = currency_symbol($store['currency']);

        // tag, category
        $tag = '';
        if(!empty($_REQUEST['tag'])) {
            $tag = $store_dbobj->escape($_REQUEST['tag']);
            $tag_ck = CacheKey::q($store_dbname . ".category?category=" . $tag);
            $tag_obj = BaseModel::findCachedOne($tag_ck);
            if(empty($tag_obj)) {
                $tag = '';
            }
        }

        $tags = BaseMapper::getCachedObjects(lck_store_tags($store_dbname));
        $this->view->tag = $tag;
        $this->view->tags = $tags['data'];

        // global category
        $cat_id = '';
        $cat_levels = array(NULL, NULL);
        if(!empty($_REQUEST['cat_id'])) {
            $cat_id = $store_dbobj->escape($_REQUEST['cat_id']);
            $cat_ck = CacheKey::q($dbconfig->account->name . ".global_category?id=" . $cat_id);
            $cat_obj = BaseModel::findCachedOne($cat_ck);
            if(empty($cat_obj)) {
                $cat_id = 0;
            } else {
                $cats = preg_split("/\s*>\s*/", preg_replace("/&/", "__and__", $cat_obj['path']));
                $cat_levels[0] = $cats[0];
                if(isset($cats[1]))$cat_levels[1] = $cats[1];
            }
        }
        $categories = BaseMapper::getCachedObjects(lck_store_categories($store_dbname));
        $this->view->categories = $categories['data'];

        $page_num = 1;

        $ck = lck_store_active_products($store_dbname)->limit($page_num, PRODUCT_NUM_PER_PAGE);
        $products_data = BaseMapper::getCachedObjects($ck);
        $products = $products_data['data'];
        $products_cnt = $products_data['total_rows'];

        $this->view->products = $products;
        $this->view->products_count = count($products);
        $this->view->total_rows = $products_cnt;
        $this->view->active_products_cnt = $products_cnt;

        // Pagenation
        $this->view->rows_per_page = PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;
        $this->view->extra_params = array();
        if(!empty($tag)) {
            $this->view->extra_params['tag'] = $tag;
        }
        if(!empty($cat_id)) {
            $this->view->extra_params['cat_id'] = $cat_id;
        }
        if(!empty($aid)) {
            $this->view->extra_params['aid'] = $aid;
        }

        $this->view->launch_cond = Store::canLaunch($this->store, TRUE, $this->user);
        $this->view->is_subscribed = Store::isSubscribed($this->store);
    }

    public function termsAction() {
        if($this->view->is_merchant) {
            redirect(getSiteMerchantUrl('/selling/products'));
        }

    }
    
    public function subscriptionAction() {
        
        // there are possible situations regarding the subscription status of a store
        // 1. non-subscriber
        // 2. subscriber
        // 3. subscription in process
        
        // whenever the "Subscribe" button is clicked, we create a redis key with 150s expiration
        
        global $redis;
        $store = $this->store;
        
        $this->view->is_subscriber = !empty($store['subscribed'])&&!empty($store['subscr_id']);
        $this->view->in_process = $redis->get('subscription_'.$this->user_session->user_id); 
    }

    public function closeStoreAction() {
    }
    
}
