<?php
class AdminController extends BaseController{

    public function init() {
        $this->view->old = default2String($_REQUEST['old']);
    }
    
    public function indexAction() {
        $this->datatablePreprocess();
        $this->view->table_object_options = getTableObjectSelectOptions();
    }

    public function datatableAction() {

    }

    private function getCateogryFeaturedProductsList() {
        $l1_category_keys = DAL::get(lck_categories(true));
        $lists = array();
        foreach($l1_category_keys as $category_key) {
            $category = DAL::get(CacheKey::q($category_key));
            $keys[] = lck_featured_products(CATEGORY_FEATURED, $category['id']);
        }
        return $keys;
    }

    public function storesAction() {
        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page'])? (int)$_REQUEST['page'] : 1;
        $merchant_email = isset($_REQUEST['merchant_email']) ? $_REQUEST['merchant_email'] : '';
        $store_subdomain = isset($_REQUEST['store_subdomain']) ? $_REQUEST['store_subdomain'] : '';

        $service = StoreService::getInstance();
        $service->setMethod('get_all_stores_info');
        $service->setParams(array(
            'account_dbobj' => $account_dbobj,
            'merchant_email' => $merchant_email,
            'store_subdomain' => $store_subdomain,
            'page_num' => $page_num
        ));
        $service->call();
        $response = $service->getResponse();
        $store_count = StoresMapper::getAllStoreCnt($account_dbobj, $merchant_email, $store_subdomain);

        $this->view->store_info = $response;
        // pagenation code
        $this->view->total_rows = $store_count;
        $this->view->rows_per_page = STORE_NUM_PER_PAGE;
        $this->view->page_num = $page_num;
        $this->view->extra_params = array(
            'merchant_email' => $merchant_email,
            'store_subdomain' => $store_subdomain
        );
    }

    public function flashdealAction(){
        /**
         * Deal Scope: 1-Site 2-Store 3-Product
         *
         */

        $account_dbobj = $this->account_dbobj;

        if(isset($_REQUEST['submit'])) {

            $service = new CouponService();
            $service->setMethod('saveDeal');
            $data = $_REQUEST;

            $data['account_dbobj'] = $account_dbobj;
            $service->setParams($data);
            $service->call();
            $status = $service->getStatus();

            if($status === 0) {
                $this->view->create_ok = true;
            } else {
                $this->view->errno = $service->getErrnos();
            }

        }

        $this->view->coupon_code = uniqid(); // In future, we need check the coupon code firstly.

        $deals = CouponsMapper::getActiveDeals($account_dbobj, array(ACTIVATED, CREATED, DELETED), 1);
        foreach($deals as $id => $deal) {
            $store_id = $deal['store_id'];
            $product_id = $deal['product_id'];
            $store_obj = new Store($account_dbobj);
            $store_obj->findOne('id='.$store_id);
            $subdomain = $store_obj->getSubdomain();
            $store_url = getStoreUrl($subdomain);
            $deals[$id]['store_url'] = $store_url;
            $deals[$id]['product_url'] = $store_url."/products/item?id=".$product_id;

            /* get the product name */
            $store_host = $store_obj->getHost();
            $store_dbobj = DBObj::getStoreDBObj($store_host,$store_id);
            $product_obj = new Product($store_dbobj);
            $product_obj->findOne('id='.$product_id);
            $product_name = $product_obj->getName();
            $deals[$id]['product_name'] = $product_name;
        }
        $this->view->deals = $deals;
    }

    public function auctionAction(){

        $account_dbobj = $this->account_dbobj;
        $auction_id = default2Int($_REQUEST['auction_id']);

        if(isset($_REQUEST['submit'])) {

            $service = new AuctionService();
            $service->setMethod('create_auction');
            $data = $_REQUEST;

            $data['account_dbobj'] = $account_dbobj;
            $service->setParams($data);
            $service->call();
            $status = $service->getStatus();

            if($status === 0) {
                $this->view->create_ok = true;
            } else {
                $this->view->errnos = $service->getErrnos();
            }
        }

        $auctions = AuctionsMapper::getAuctions($account_dbobj);
        foreach($auctions as $id => $auction) {
            $store_id = $auction['store_id'];
            $product_id = $auction['product_id'];
            $subdomain = $auction['subdomain'];
            $store_url = getStoreUrl($subdomain);
            $auctions[$id]['store_url'] = $store_url;
            $auctions[$id]['product_url'] = $store_url."/products/item?id=".$product_id;

            $bid_status  =  "in bid";
            $now = get_current_datetime();
            if($now < $auction['start_time']) {
                $bid_status = "not start yet";
            } else if ($auction['end_time'] < $now) {
                $bid_status = "end";
            }
            $db_status = "Activated";
            if($auction['status'] == CREATED) {
                $db_status = "Created";
            } else if($auction['status'] == PENDING){
                $db_status = "Pending";
            }
            $auctions[$id]['auction_status'] = $db_status . ", " . $bid_status;

            if($auction['id'] == $auction_id) {
                $_REQUEST = $auctions[$id];
            }
        }
        $this->view->auctions = $auctions;
    }

    public function listbidsAction(){
        if(!isset($_REQUEST['auction_id'])) {
            $this->view->bids = array();
            return;
        }
        $auction_id = (int)$_REQUEST['auction_id'];
        if(empty($auction_id)) {
            $this->view->bids = array();
            return;
        }

        $this->view->auction_id= $auction_id;
        $bids = AuctionsMapper::getBids($this->account_dbobj, $auction_id);
        foreach($bids as $id => $bid) {
            $bids[$id]['product_url'] = getStoreUrl($bid['store_subdomain']) . "/products/item?id=" . $bid['product_id'];
        }
        $this->view->bids = $bids;
    }

    public function tagsAction(){

        $this->view->categories = ProductCategoryMapper::getCategories($this->account_dbobj);
    }

    public function categoryAction(){
        $cached_categories = BaseMapper::getCachedObjects(lck_categories());
        $this->view->categories = $cached_categories['data'];
    }

    public function closestoreAction(){

        if(isset($_REQUEST['submit']) && !empty($_REQUEST['query'])) {
            $query = trim($_REQUEST['query']);
            StoresMapper::closeStore($this->account_dbobj, $query);
            $this->view->errnos[STORE_CLOSED] = 1;
        }
    }

    public function emailAction(){
        $user_categories = UserService::$user_categories;
        $this->view->user_cats = $user_categories;
        $this->view->email_tags = UserService::$email_tags;
        $this->view->email_tpls = BaseMapper::getCachedObjects(
            CacheKey::q($GLOBALS['account_dbname'].'.email_templates?status!=127')->desc('created'),
            0, 9999,
            array('list'=>array('ignore_cache'=>true))
        );
        $this->view->email_tpls = $this->view->email_tpls['data'];
        if(isset($_REQUEST['user_category'])) {
            if(isset($user_categories[$_REQUEST['user_category']]) || isset($this->view->email_tags[$_REQUEST['user_category']])){
                $params['email_content'] = $_REQUEST['content'];
                $params['user_category'] = $_REQUEST['user_category'];
                $params['account_dbobj'] = $this->account_dbobj;;
                $params['email_subject'] = $_REQUEST['subject'];
                $params['job_dbobj'] = $this->job_dbobj;
                if(isset($_REQUEST['template_type']) && !empty($_REQUEST['template_type'])){
                    $params['email_template'] = $_REQUEST['template_type'];
                    $this->view->selected_tpl = $_REQUEST['template_type'];
                } else {
                    $this->view->selected_tpl = "";
                }

                $service = UserService::getInstance();
                $service->setMethod('admin_send_email');
                $service->setParams($params);
                $service->call();
                $response = $service->getResponse();
                $this->view->email_job_info = $response;
                $this->view->email_content = $params['email_content'];
                $this->view->email_subject = $params['email_subject'];
                $this->view->selected_category = $_REQUEST['user_category'];
            } else {
                $this->view->errs = $GLOBALS['errors'][INVALID_USER_CATEGORY]['msg'];
            }
        }
    }


    public function payhistoryAction(){
        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page'])? (int)$_REQUEST['page'] : 1;

        $payments = PaymentsMapper::getPayments($account_dbobj, $page_num);
        $count = PaymentsMapper::getPaymentsCount($account_dbobj);

        $this->view->payments = $payments;

        // pagenation code
        $this->view->total_rows = $count;
        $this->view->rows_per_page = PAYMENT_ITEMS_PER_PAGE;
        $this->view->page_num = $page_num;
    }

    public function futurepayAction(){
        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page'])? (int)$_REQUEST['page'] : 1;

        $futurepayments = PaymentItemsMapper::getfuturepayment($account_dbobj, $page_num);
        $count = PaymentItemsMapper::getfuturepaymentsCount($account_dbobj);

        $this->view->payments = $futurepayments;

        // pagenation code
        $this->view->total_rows = $count;
        $this->view->rows_per_page = PAYMENT_ITEMS_PER_PAGE;
        $this->view->page_num = $page_num;
    }

    public function abtestsAction() {


    }

    public function featuredproductAction(){

        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page'])? (int)$_REQUEST['page'] : 1;

        $response = SearchProductsMapper::getProducts(
            $account_dbobj,
            array(
                'page_num' => $page_num,
                'where' => 'featured!=0',
                'orderby' => array('score' => 'desc'),
            )
        );
        $product_count = SearchProductsMapper::getProductsCnt(
            $account_dbobj,
            array('where' => 'featured!=0')
        );

        $this->view->search_products = $response;
        // pagenation code
        $this->view->total_rows = $product_count;
        $this->view->rows_per_page = PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;

    }

    public function closeaccountAction() {

        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $search = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
        $user_info = UsersMapper::getAllUsers($account_dbobj, $page_num, $search);
        $user_cnt = UsersMapper::getUsersCount($account_dbobj);

        $this->view->user_info = $user_info;
        // pagenation code
        $this->view->total_rows = $user_cnt;
        $this->view->rows_per_page = ACCOUNT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;
    }

    public function categorizingAction() {

        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $section = default2String($_REQUEST['section']);

        $option = array('page_num' => $page_num, 'exclude_in_search' => false);

        if($section === 'categorized') {
            $option['where'] = "global_category_id!=0";
        } else if($section === 'uncategorized') {
            $option['where'] = "global_category_id=0";
        }

        $products = SearchProductsMapper::getProducts($account_dbobj, $option);
        $products_cnt = SearchProductsMapper::getProductsCnt($account_dbobj, $option);

        $cached_categories = BaseMapper::getCachedObjects(lck_categories());
        $category_list = $cached_categories['data'];

        $this->view->global_category_list = $category_list;
        $this->view->products = $products;
        $this->view->section = $section;
        // pagenation code
        $this->view->total_rows = $products_cnt;
        $this->view->rows_per_page = PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;
        $this->view->extra_params = array(
            'section' => $section
        );
    }

    public function couponAction() {
        $_REQUEST['table_object'] = 'admin_coupon';
        $this->datatablePreprocess();        
    }

    public function ncPaymentAction() {
        $_REQUEST['table_object'] = 'wallet_payments';
        $cond = "";
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
        if(!empty($user_id)){
            $cond = 'user_id=' . $user_id . "&";
        }

        $status = '';
        $p_status = isset($_GET['status']) ? $_GET['status'] : '';
        if($p_status == 'pending'){
            $status = PENDING;
        }elseif($p_status == 'available'){
            $status = ACTIVATED;
        }elseif($p_status == 'completed'){
            $status = COMPLETED;
        }
        if($status !== ''){
            $cond .= "status=$status";
        }else{
            $cond .= "status!=" . DELETED;
        }

        $type = isset($_GET['type']) ? $_GET['type'] : '';
        if(!empty($type) && $type != 'all'){
            $cond .= "&type=$type";
        }

        $this->view->status = $p_status;
        $this->view->type = $type;
        $_REQUEST['action_params']['condition_string'] = $cond;
        $this->datatablePreprocess();
    }

    public function paymentDetailAction() {
        global $dbconfig;
        $id =$_REQUEST['id'];
        $wa_ck = CacheKey::q($dbconfig->account->name . ".wallet_activity?id=$id");
        $this->view->wa = BaseModel::findCachedOne($wa_ck, array('force'=>true));
        $w_ck = CacheKey::q($dbconfig->account->name . ".wallet?id=" . $this->view->wa['wallet_id']);
        $this->view->w = BaseModel::findCachedOne($w_ck, array('force'=>true));
        $u_ck = CacheKey::q($dbconfig->account->name . ".user?id=" . $this->view->w['user_id']);
        $this->view->w_user = BaseModel::findCachedOne($u_ck);
    }

    public function usersAction() {
        $_REQUEST['table_object'] = 'all_users';
        $this->datatablePreprocess();
    }

    public function emailTemplatesAction() {
        $_REQUEST['table_object'] = 'email_templates';
        $this->datatablePreprocess();
    }

    public function emailTemplatesEditAction() {
        global $dbconfig;
        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if(!empty($id)){
            $this->view->tpl = BaseModel::findCachedOne($dbconfig->account->name . ".email_template?id=$id");
        } else {
            $this->view->tpl = array();
        }
        $this->view->email_tpls = BaseMapper::getCachedObjects(
            CacheKey::q($GLOBALS['account_dbname'].'.email_templates?status!=127')->desc('created'),
            0, 9999,
            array('list'=>array('ignore_cache'=>true))
        );
        $this->view->email_tpls = $this->view->email_tpls['data'];
    }

    public function ordersAction() {
        $_REQUEST['table_object'] = 'myorders_for_admin';
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
        if($status !== ''){
            $cond = "payment_status=$status";
            $_REQUEST['action_params']['condition_string'] = $cond;
        }
        $this->datatablePreprocess();
    }

    public function annoucementAction() {

        $annoucement_obj = new Annoucement($this->account_dbobj, 1);
        if(checkIsSet($_REQUEST, 'submit', 'content')){
            $annoucement_obj->setContent($_REQUEST['content']);
            $annoucement_obj->save();
        }
        $this->view->content = $annoucement_obj->getContent();
    }

    public function amazonProductsAction() {
        $cached_categories = BaseMapper::getCachedObjects(lck_categories());
        $this->view->categories = $cached_categories['data'];

        $_REQUEST['table_object'] = 'amazon_products';
        $this->datatablePreprocess();
    }

    public function storeDetailAction() {
        //TODO
        global $dbconfig;
        $store_id = $_REQUEST['id'];
        $store_ck = CacheKey::q($dbconfig->account->name . ".store?id=" . $store_id);
        $store = BaseModel::findCachedOne($store_ck);
        if(empty($store)){
            redirect('/admin/users');
        }
        $this->view->target_store = $store;
        $this->view->target_user = BaseModel::findCachedOne(
            CacheKey::q($dbconfig->account->name . ".user?id=" . $store['uid'])
        );
        $this->view->target_store_active_products_cnt = DAL::getListCount(lck_store_active_products(getStoreDBName($store['id'])));
        $this->view->target_store_inactive_products_cnt = DAL::getListCount(lck_store_inactive_products(getStoreDBName($store['id'])));
    }
}
