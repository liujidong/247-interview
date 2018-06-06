<?php

class StoreController extends BaseController
{

    public function init()
    {
    }

    public function checkStatus(){
        if(empty($this->visit_store)){
            redirect(getSiteMerchantUrl());
        }

        if($this->visit_store['subdomain'] == 'marketplace') return;

        if(!Store::isLaunched($this->visit_store)){
            redirect(getSiteMerchantUrl());
        }
    }

    public function indexAction() {

        global $admin_account;
        global $dbconfig;

        $this->checkStatus();

        // Store Info
        $store = $this->visit_store;
        $this->view->store = $store;
        $store_dbobj = $this->visit_store_dbobj;
        $store_dbname = getStoreDBName($store['id']);

        // Country
        $country_info =  BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name.".country?iso2=".$store['country']));
        $this->view->country =  $store['country'];
        $this->view->country_name = isset($country_info['short_name']) ? $country_info['short_name'] : "";
        $this->view->currency = $store['currency'];
        $this->view->currency_symbol = currency_symbol($store['currency']);

        if($this->visit_store['subdomain'] == 'marketplace') {
            return $this->marketplaceHomepage();
        }

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
        $this->view->store_tags = implode(array_map(function($t){return $t['category'];},$tags['data']), ", ");
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

        $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'updated';
        if(!in_array($sort, array("updated", "price"))) $sort = 'updated';
        $dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : 'desc';
        if(!in_array($dir, array("asc", "desc"))) $dir = 'desc';

        $page_num = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
        $aid = isset($_REQUEST['aid']) ? $_REQUEST['aid'] : '';

        $ck = NULL;
        if(!empty($tag)) {
            $ck = lck_store_tag_products($store_dbname, $tag)->limit($page_num, PRODUCT_NUM_PER_PAGE);
        } else if(!empty($cat_id)) {
            $ck = lck_store_category_products($store_dbname, $cat_levels[0], $cat_levels[1])->limit($page_num, PRODUCT_NUM_PER_PAGE);
        } else {
            $ck = lck_store_active_products($store_dbname)->limit($page_num, PRODUCT_NUM_PER_PAGE);
        }
        $ck->$dir($sort);

        $products_data = BaseMapper::getCachedObjects($ck);
        $products = $products_data['data'];
        $products_cnt = $products_data['total_rows'];

        $this->view->products = $products;
        $this->view->products_count = count($products);
        $this->view->total_rows = $products_cnt;

        if(isset($this->user_session->user_id)){
            $user = $this->user;
            $username = $user['username'];
            if(in_array($username, $admin_account)) {
                $this->view->is_admin = true;
            }
        }

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

    }

    public function thankYouAction(){
        $this->checkStatus();
    }

    public function loginAction() {
        $this->checkStatus();
    }

    public function signupAction() {
        $this->checkStatus();
    }

    public function marketplaceHomepage() {
        $this->view->categories = array(
            array("id" => "Electronics", "path" => "Electronics"),
            array("id" => "Men", "path" => "Men"),
            array("id" => "Women", "path" => "Women"),
        );
        $this->view->tags = array();
        $this->view->currency_symbol = "";

        $cat_id = NULL;
        $query = "man women electronic";
        if(isset($_REQUEST['cat_id'])){
            $query = $_REQUEST['cat_id'];
            $cat_id = $query;
        }
        $page_num = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
        $service = AmazonSearchService::getInstance();
        $service->setMethod('search');
        $service->setParams(array(
            'keywords' => $query,
            'page' => $page_num,
        ));
        $service->call();
        $response = $service->getResponse();
        $this->view->products = $response['view_objects'];

        // Pagenation
        $this->view->total_rows = $response['total_rows'];
        $this->view->rows_per_page = PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;
        $this->view->extra_params = array();
        if(!empty($cat_id)) {
            $this->view->extra_params['cat_id'] = $cat_id;
        }
    }

    public function infoAction() {
        global $dbconfig;
        $this->checkStatus();

        $store = $this->visit_store;
        $this->view->store = $store;

        $country_info =  BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name.".country?iso2=".$store['country']));
        $this->view->country =  $store['country'];
        $this->view->country_name = isset($country_info['short_name']) ? $country_info['short_name'] : "US";
    }

    function productsItemAction() {
        global $dbconfig;
        $this->checkStatus();

        $account_db_name = $dbconfig->account->name;
        $store_db_name = $dbconfig->store->name;
        $store_subdomain = $this->visit_store['subdomain'];

        if(isset($_GET['ASIN']) && $store_subdomain == 'marketplace') {
            return $this->amazonProductsItem();
        } else if(!isset($_GET['id'])){
            $store_url = getStoreUrl($store_subdomain);
            redirect($store_url);
            return;
        }

        if(!Store::isLaunched($this->visit_store)){
            redirect(getSiteMerchantUrl());
        }

        $store = $this->visit_store;
        $store_dbname = getStoreDBName($store['id']);
        $tags = BaseMapper::getCachedObjects(lck_store_tags($store_dbname));
        $this->view->store_tags = implode(array_map(function($t){return $t['category'];},$tags['data']), ", ");

        $this->view->local = TRUE;
        $account_dbobj = $this->account_dbobj;
        $subdomain = $this->view->store_subdomain;
        $store = $this->visit_store;
        $this->view->store = $store;
        $this->view->currency = $store['currency'];
        $this->view->currency_symbol = currency_symbol($store['currency']);
        $store_id = $store['id'];
        $store_name = $store['name'];
        $store_shipping = $store['shipping'];
        $additional_shipping = $store['additional_shipping'];
        $store_optin_salesnetwork = $store['optin_salesnetwork'];


        $parts = explode('_', $_GET['id']);
        if(sizeof($parts) ===  2) {
            $aid = array_shift($parts);
        } else if(isset ($_GET['aid'])){
            $aid = $_GET['aid'];
        }
        $product_id = array_shift($parts);

        $product = BaseModel::findCachedOne(CacheKey::q($store_db_name . "_$store_id.product?id=" .$product_id));
        $real_quantity = $product['quantity'];
        $product['quantity'] = 1;
        global $cond_active_product;
        if(!$cond_active_product->test($product)){
            redirect("/store/" . $product['store_subdomain']);
        }
        $product['quantity'] = $real_quantity;
        $global_cat = BaseModel::findCachedOne(CacheKey::q("$account_db_name.global_category?id=" . $product['global_category_id']));
        $this->view->global_cat = $global_cat;

        if(!empty($product) && isset($product['id'])) {

            $product_commission = $product['commission'];
            // we only increment the count in sales report when the product is in sales network & commission is not zero
            // also the status of the associate needs to be active
            if(!empty($aid) && $store_optin_salesnetwork == ACTIVATED && !empty($product_commission)) {
                $associate = new Associate($account_dbobj);
                $associate->findOne('aid='."'$aid' and status=".ACTIVATED);
                $associate_id = $associate->getId();
                if($associate_id != 0) {
                    $associate_status = ACTIVATED;
                    AssociatesProductMapper::addAssociatesProduct($associate_id, $store_id, $product_id, $account_dbobj);
                }
            }
        } else {
            // redirect to store home page
            $store_url = getStoreUrl($store_subdomain);
            redirect($store_url);
        }

        $this->view->product = $product;
        $this->view->custom_fields = $product['custom_fields'];

        $ck = lck_store_active_products($store_db_name . "_" . $store['id'])->limit(1, PRODUCT_NUM_PER_PAGE);
        $products_data = BaseMapper::getCachedObjects($ck);
        $this->view->recommend_products = $products_data['data'];

        // seo purpose
        $this->view->product_image = reset($this->view->product['pictures'][736]);
        $this->view->product_title = $this->view->product['name'];

        $this->view->js_code = "";
        if($this->is_shopinterest_product_item) {
            $js_code = 'var url = window.location.href;
                        var domain = url.replace(/^\w+:\/\//, "").replace(/\/.*$/gi,"").toLowerCase();
                        var domain_parts = domain.match(/([^.]+)(\.staging)?\.shopinterest\.co(:\d+)?$/, "");
                        if(domain_parts){
                            var new_url = "http://www" + (domain_parts[2]? domain_parts[2] : "") + ".shopintoit.com/store/" + domain_parts[1];
                            new_url = new_url + url.replace(/^\w+:\/\/.*?\//i, "/");
                            window.location.href = new_url;
                        }';
            $this->view->js_code = "<script type='text/javascript'>$js_code</script>";
        }
    }


    function amazonProductsItem() {
        $this->checkStatus();

        $this->view->local = FALSE;
        $account_dbobj = $this->account_dbobj;
        $subdomain = $this->view->store_subdomain;
        $store = $this->visit_store;
        $this->view->store = $store;
        $this->view->currency = $store['currency'];
        $this->view->currency_symbol = currency_symbol($store['currency']);

        $this->view->global_cat = array();

        $service = AmazonSearchService::getInstance();
        $service->setMethod('lookup');
        $service->setParams(array(
            'ASIN' => $_GET['ASIN'],
            'format' => true,
        ));
        $service->call();
        $product = $service->getResponse();

        $product['dealer'] = 'amazon';
        $product['external_id'] = $_GET['ASIN'];
        $this->view->product = $product;
        $product_shipping = $product['shipping'];
        $this->view->total_shipping = 0;
        $this->view->product_shipping = 0;
        $this->view->additional_shipping = 0;
        $this->view->custom_fields = array();

        $service = AmazonSearchService::getInstance();
        $service->setMethod('search');
        $service->setParams(array(
            'keywords' => "men women electronics",
            'page' => 1,
        ));
        $service->call();
        $response = $service->getResponse();
        $products = $response['view_objects'];
        $this->view->recommend_products = array_slice($products, 0, 8);

        // seo purpose
        $this->view->product_image = reset($this->view->product['pictures'][736]);
        $this->view->product_title = $this->view->product['name'];
    }

}
