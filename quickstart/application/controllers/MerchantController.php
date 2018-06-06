<?php

class MerchantController extends BaseController
{

    public function init() {
        /* Initialize action controller here */
    }

    public function customersAction() {

        global $redis;
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        if($redis->get("store:$store_id:status") == ACTIVATED) {
            $username = $redis->get("user:$user_id:username");
            $first_name = $redis->get("user:$user_id:first_name");
            $last_name = $redis->get("user:$user_id:last_name");
            $store_name = $redis->get("store:$store_id:name");

            $contacts = StoresMapper::getContacts($this->store_dbobj);
            $this->view->replyto_email = $username;
            $merchant_name = trim($first_name.' '.$last_name);
            $store_name = $store_name;
            $this->view->replyto_name = empty($merchant_name)?$store_name:$merchant_name;
            $this->view->contacts = $contacts;
        } else {
            redirect('/profile');
        }
    }

    public function dashboardAction() {
    }

    public function profileAction() {

    }

    public function selectboardsAction() {

        global $redis;

        // input
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $account_dbobj = $this->account_dbobj;
        $pinterest_account_id = $redis->get("merchant:$merchant_id:pinterest_account_id")?
                $redis->get("merchant:$merchant_id:pinterest_account_id"):0;
        $store_id = $redis->get("merchant:$merchant_id:store_id");

        // output: view variables
        $this->view->boards = array();
        $this->view->boards_count = 0;

        $pinterest_account = new PinterestAccount($account_dbobj);
        $pinterest_account->findOne('id='.$pinterest_account_id);

        if(!empty($pinterest_account_id) && validate($pinterest_account->getUsername(), 'pinterest_username')) {
            // save the selected boards
            if(isset($_REQUEST['submit'])) {

//                if(empty($_REQUEST['boards'])) {
//                    $this->view->errnos[NO_BOARD_AVAILABLE] = 1;
//                    return;
//                }

                if(empty($_REQUEST['boards'])) {
                    $selected_board_ids = array();
                } else {
                    $selected_board_ids = $_REQUEST['boards'];
                }

                $service = StoreService::getInstance();
                $service->setMethod('save_selected_boards');
                $service->setParams(array('store_id'=>$store_id, 'board_ids'=>$selected_board_ids,
                    'account_dbobj'=>$account_dbobj));
                $service->call();

                redirect('/merchant/selectpins');
            } else {

                $force = empty($_REQUEST['refresh'])?false:true;

                $service = PinterestService::getInstance();
                $service->setMethod('import_account_boards');
                $service->setParams(array('pinterest_account_id'=>$pinterest_account_id, 'account_dbobj'=>$account_dbobj,
                    'job_dbobj' => $this->job_dbobj, 'force' => $force));
                $service->call();
                if($service->getStatus() === 1) {
                    Log::write(WARN, 'Import pinterest account boards failed for pinterest account '.$pinterest_account_id.' redirect to profile page');
                    $this->view->errnos = $service->getErrnos();
                    return;
                }
            }

            $boards = StoresMapper::getValidBoards($store_id,
                    $pinterest_account_id, $account_dbobj);
            foreach($boards as $i=>$board) {
                $this->view->boards[$i]['id'] = $board['id'];
                $this->view->boards[$i]['url'] = 'http://pinterest.com'.$board['url'];
                $this->view->boards[$i]['covers'] = getBoardCovers($board['thumbnails']);
                $this->view->boards[$i]['name'] = $board['name'];
                $this->view->boards[$i]['store_id'] = $board['store_id'];
            }

            $this->view->boards_count = sizeof($boards);
        } else {
            Log::write(INFO, 'The merchant doesnt set pinterest account, redirect to profile page then');
            redirect('/profile');
        }

    }

    public function selectpinsAction() {

        global $redis;

        // input
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $account_dbobj = $this->account_dbobj;
        $pinterest_account_id = $redis->get("merchant:$merchant_id:pinterest_account_id")?
                $redis->get("merchant:$merchant_id:pinterest_account_id"):0;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        $store_dbobj = $this->store_dbobj;
        $store_shipping = $redis->get("store:$store_id:shipping");
        $store_additional_shipping = $redis->get("store:$store_id:additional_shipping");
        $store_optin_salesnetwork = $redis->get("store:$store_id:optin_salesnetwork");

        // output
        $this->view->total_page = 0;
        $this->view->pins = array();
        $this->view->categories = array();
        $this->view->store_shipping = $store_shipping;
        $this->view->store_addtional_shipping = $store_additional_shipping;

        if(!empty($pinterest_account_id)) {

            if(!empty($_REQUEST['page'])) {
                $page_num = $_REQUEST['page'];
            } else {
                $page_num = 1;
            }

            if(isset($_REQUEST['submit'])) {
                if(empty($_REQUEST['pins'])) {
                    redirect('/profile');
                }

                $pins = $_REQUEST['pins'];
                $service = StoreService::getInstance();
                $service->setMethod('add_products');
                $service->setParams(array('store_id'=>$store_id, 'pins'=>$pins,
                    'account_dbobj'=>$account_dbobj, 'store_dbobj'=>$store_dbobj, 'store_optin_salesnetwork' => $store_optin_salesnetwork));

                $service->call();
                $status = $service->getStatus();

                if($status === 0) {
                    $response = $service->getResponse();
                    if(!empty($response)) {
                        $this->view->errnos[PINS_SAVED] = 1;
                    }
                } else {
                    $errnos = $service->getErrnos();
                    $this->view->errnos = $errnos;
                }
                $service->destroy();
            }

            $pins = StoresMapper::getSelectedBoardsNonSelectedPins($store_id, $this->account_dbobj, $page_num);
            $selected_board_ids = StoresMapper::getSelectedBoardIds($store_id, $this->account_dbobj);

            $total_pin_num = StoresMapper::getSelectedBoardsNonSelectedPinsCount($store_id, $this->account_dbobj);
            //$total_page = ceil( $total_pin_num / PIN_NUM_PER_PAGE );

            if(count($pins) === 0 && count($selected_board_ids) != 0) {
                $board_ids = StoresMapper::getSelectedBoardsIds($store_id, $account_dbobj);

                $service = PinterestService::getInstance();
                $service->setMethod('import_board_pins');

                $service->setParams(array('board_id'=>$board_ids[0], 'page'=>1, 'subpage'=>1, 'account_dbobj'=>$account_dbobj,
                    'job_dbobj' => $this->job_dbobj));
                $service->call();

                $pins = StoresMapper::getSelectedBoardsNonSelectedPins($store_id, $this->account_dbobj, $page_num);

            }
            //$this->view->total_page = $total_page;
            $this->view->pins = $pins;
            //$this->view->total_pin_num = $total_pin_num ;
            //$this->view->pin_num_per_page = PIN_NUM_PER_PAGE ;
            $this->view->optin_salesnetwork = $store_optin_salesnetwork;
            $this->view->categories = StoresMapper::getCategories($this->store_dbobj);

            //pagination
            $this->view->total_rows = $total_pin_num;
            $this->view->rows_per_page = PIN_NUM_PER_PAGE;
            $this->view->page_num = $page_num;

        } else {
            Log::write(INFO, 'The merchant doesnt provide a pitnerest account so there is no pins to be selected, redirect to profile page then');
            redirect('/profile');
        }
    }

    public function verifyAction() {

        if(empty($_REQUEST['code'])) {
            redirect(getURL());
        }

        $service = MerchantService::getInstance();
        $service->setMethod('verify');
        $service->setParams(array(
            'code' => $_REQUEST['code'],
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
        $this->view->status = $service->getStatus();
        if($this->is_merchant) {
            $this->view->next_page = '/profile';
        } else {
            $this->view->next_page = '/login';
        }
    }

    public function seemoreAction(){

        global $redis;
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");

        $this->_helper->layout()->disableLayout();

        $page_num = empty($_REQUEST['page_num']) ? 0 : intval(($_REQUEST['page_num']));

        $pins = array();
        if($page_num > 1){
            //only call when page_num > 1
            $pins = StoresMapper::getSelectedBoardsNonSelectedPins($store_id, $this->account_dbobj,$page_num);
        }
        $this->view->pins = $pins;
        $this->_helper->viewRenderer->setViewScriptPathSpec('merchant/pin-details.phtml');
        $this->view->categories = StoresMapper::getCategories($this->store_dbobj);
    }

    public function storesettingsAction() {

    }

    public function settingsAction() {

    }

    public function productsAction() {

        global $redis;

        $page_num = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
        $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
        $status = 'active';

        if(isset($_REQUEST['status']) && $_REQUEST['status'] === 'inactive') {
            $status = 'inactive';
        }

        $store_dbobj = $this->store_dbobj;
        $account_dbobj = $this->account_dbobj;
        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        $keys = array(
            "store:$store_id:shipping",
            "store:$store_id:additional_shipping",
            "store:$store_id:allow_resell"
        );
        $values = $redis->mget2($keys);
        $store_shipping = $values["store:$store_id:shipping"] ? $values["store:$store_id:shipping"] : 0;
        $store_additional_shipping = $values["store:$store_id:additional_shipping"] ? $values["store:$store_id:additional_shipping"] : 0;
        $store_allow_resell = $values["store:$store_id:allow_resell"] ? $values["store:$store_id:allow_resell"] : 0;
        $cached_categories = BaseMapper::getCachedObjects(lck_categories());
        $global_category_list = $cached_categories['data'];

        $products = ProductsMapper::getProducts($store_dbobj, $page_num, $search, 0, $status);
        $product_cnt = ProductsMapper::getProductsCnt($store_dbobj, $search, 0, $status);
        // output
        $this->view->store_shipping = $store_shipping;
        $this->view->store_additional_shipping = $store_additional_shipping;
        $this->view->store_allow_resell = $store_allow_resell;
        $this->view->products = $products;
        $this->view->global_category_list = $global_category_list;
        $this->view->status = $status;
        $this->view->search = $search;

        // pagenation
        $this->view->total_rows = $product_cnt;
        $this->view->rows_per_page = CREATE_PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;

        $this->view->extra_params = array(
            'search' => $search,
            'status' => $status
        );
        $this->view->shipping_options = ShippingOptionsMapper::getShippingOptions($this->store_dbobj);
    }

    public function analyticsAction() {

        global $site_domain, $redis;

        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        $store_subdomain = $redis->get("store:$store_id:subdomain");
        $store_url = $store_subdomain.'.'.$site_domain;
        //$store_url = 'kikicat2101311.shopinterest.co';

        $day_from = isset($_REQUEST['from']) ? $_REQUEST['from'] : getNdaysago();
        $day_to = isset($_REQUEST['to']) ? $_REQUEST['to'] : getNdaysago(0);
        $filters = "ga:pagePath=@$store_url";

        $service = new GoogleService();
        $service->setMethod('get_analytics_data');
        $service->setParams(array(
            'day_from' => $day_from,
            'day_to' => $day_to,
            'opt_params' => array(
                'filters' => $filters
            )
        ));
        $service->call();

        $data_rows = $service->getResponse();

        $this->view->rows = $data_rows;
        $this->view->day_from = $day_from;
        $this->view->day_to = $day_to;
    }

    public function couponAction() {
        global $redis;
        $account_dbobj = $this->account_dbobj;
        $coupon_id = default2Int($_REQUEST['coupon_id']);

        $merchant_id = $this->user_session->merchant_id;
        $store_id = $redis->get("merchant:$merchant_id:store_id");

        if(isset($_REQUEST['submit'])) {
            // call coupon servise to create coupon
            $params = $_REQUEST;
            $params['account_dbobj'] = $account_dbobj;
            $params['store_id'] = $store_id; // make sure merchant only add coupon for his owe store

            $service = CouponService::getInstance();
            $service->setMethod('add_coupon');
            $service->setParams($params);
            $service->call();

            if($service->getStatus() === 1) {
                $this->view->errnos = $service->getErrnos();
            } else {
                $this->view->create_ok = true;
            }
        }

        $service = CouponService::getInstance();
        $service->setMethod('get_coupons');
        $service->setParams(array(
            'account_dbobj' => $account_dbobj,
            'store_id' => $store_id,
            'status' => array(CREATED)
        ));
        $service->call();

        $coupons = $service->getResponse();
        if(!empty($coupon_id)) {
            foreach($coupons as $coupon) {
                if($coupon_id == $coupon['coupon_id']) {

                    if($coupon['scope'] === 'Site') {
                        $coupon['scope'] = SITE;
                    } else if($coupon['scope'] === 'Store') {
                        $coupon['scope'] = STORE;
                    } else if($coupon['scope'] === 'Product') {
                        $coupon['scope'] = PRODUCT;
                    }

                    if($coupon['price_offer_type'] === 'Percentage off') {
                        $coupon['price_offer_type'] = PERCENTAGE_OFF;
                    } else if($price_offer_type === 'Flat value off') {
                        $coupon['price_offer_type'] = FLAT_VALUE_OFF;
                    }

                    $coupon['input_url'] = $coupon['scope'] === STORE ? $coupon['store_url'] : default2String($coupon['product_url']);

                    $_REQUEST = $coupon;
                    break;
                }
            }
        };

        $this->view->page_used_by = 'merchant';
        $this->view->store_id = $store_id;
        $this->view->coupon_code = uniqid();
        $this->view->coupons = $coupons;

    }

    public function shippingAction() {
        $shipping_options = ShippingOptionsMapper::getShippingOptions($this->store_dbobj);
        $shipping_destinations = ShippingDestinationsMapper::getShippingDestinations($this->store_dbobj);
        $dests_by_opt = array();
        foreach($shipping_destinations as $dest){
            $dests_by_opt[$dest['shipping_option_id']][] = $dest;
        }
        $this->view->shipping_options = $shipping_options;
        $this->view->dests_by_opt = $dests_by_opt;

        $this->view->countries = CountriesMapper::getAllCountryInfo($this->account_dbobj);
    }
}
