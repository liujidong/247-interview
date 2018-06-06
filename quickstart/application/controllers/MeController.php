<?php

class MeController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */

    }

    public function settingsAction() {
        
        global $dbconfig;
        $acount_dbname = $dbconfig->account->name;
        $user_id = $this->user_session->user_id;
        $user = BaseModel::findCachedOne(CacheKey::q($acount_dbname.'.user?id='.$user_id));
        $this->view->user = $user;
        $this->view->countries = CountriesMapper::getAllCountryInfo($this->account_dbobj);
    }

    public function ordersPurchaseAction() {
        $_REQUEST['table_object'] = 'myorders_for_shopper';
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
        $cond = 'user_id=' . $this->user_session->user_id;
        if($status !== ''){
            $cond .= "&payment_status=$status";
        }else{
            $p_status = 'all';
        }
        $this->view->status = $p_status;
        $_REQUEST['action_params']['condition_string'] = $cond;
        $this->datatablePreprocess();
    }

    public function ordersPurchaseDetailAction() {
        $id = default2Int($_GET['id'], 1);
        if($id<1){
            redirect(getSiteMerchantUrl("/dashboard"));
        }
        global $dbconfig;
        $ck = CacheKey::q($dbconfig->account->name . ".myorder?id=$id");
        $myorder = BaseModel::findCachedOne($ck, array('force'=>true));
        $user_id = $this->user_session->user_id;

        if($myorder['user_id'] != $user_id){
            redirect(getSiteMerchantUrl("/dashboard"));
        }
        $grp_id = $myorder['myorder_group_id'];
        $grp = new MyorderGroup($this->account_dbobj, $grp_id);

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
        $sock = CacheKey::q($dbconfig->store->name . "_" . $myorder['store_id'] . ".shipping_option?id=".$myorder['shipping_option_id']);
        $this->view->shipping_opt = BaseModel::findCachedOne($sock);
        if(empty($this->view->shipping_opt)){
            $this->view->shipping_opt = array('name' => 'Standard');
        }

        if(isset($response['items_by_store'][$myorder['store_id']])){
            $this->view->items = $response['items_by_store'][$myorder['store_id']];
        }
        if(empty($this->view->items))$this->view->items=array();
        $this->view->store_summary = $response['store_summaries'][$myorder['store_id']];
        $this->view->order = $myorder;
        $store_ck = CacheKey::q($dbconfig->account->name . ".store?id=" . $myorder['store_id']);
        $this->view->store = BaseModel::findCachedOne($store_ck);
    }

    public function ordersResellAction() {
        $_REQUEST['table_object'] = 'resell_order_items';
        $status = '';
        $p_status = isset($_GET['status']) ? $_GET['status'] : '';
        if($p_status == 'pending'){
            $status = PENDING;
        }elseif($p_status == 'completed'){
            $status = COMPLETED;
        }
        $cond = 'user_id=' . $this->user_session->user_id;
        if($status !== ''){
            $cond .= "&payment_status=$status";
        } else {
            $p_status = 'all';
        }
        $cond .= "&status!=" . DELETED;
        $this->view->status = $p_status;
        $_REQUEST['action_params']['condition_string'] = $cond;
        $this->datatablePreprocess();
    }

    public function ordersResellDetailAction() {

    }

    public function walletAction() {
        global $dbconfig;
        $acount_dbname = $dbconfig->account->name;
        $user_id = $this->user_session->user_id;
        $page = default2Int($_GET['page'], 1);

        $this->view->wallet = WalletsMapper::findOrCreateWallets($this->account_dbobj, $user_id);

        $status = '';
        $p_status = isset($_GET['status']) ? $_GET['status'] : '';
        if($p_status == 'available'){
            $status = ACTIVATED;
        }elseif($p_status == 'pending'){
            $status = PENDING;
        }elseif($p_status == 'completed'){
            $status = COMPLETED;
        }else{
            $p_status = 'all';
        }
        $this->view->status = $p_status;
        $user = BaseModel::findCachedOne(CacheKey::q($acount_dbname.'.user?id='.$user_id));
        $this->view->user = $user;
        $wallet_activities = WalletsMapper::getWalletActivitiesForUser(
            $this->account_dbobj, $user['id'], $status, $page, 10
        );
        $wallet_activities_cnt = WalletsMapper::getWalletActivitiesCntForUser(
            $this->account_dbobj, $user['id'], $status
        );

        $this->view->wallet_activities = $wallet_activities;

        $this->view->total_rows = $wallet_activities_cnt;
        $this->view->rows_per_page = 10;
        $this->view->page_num = $page;
        $extra_params = array(
            'status' => $p_status,
        );
        $this->view->extra_params = $extra_params;
    }

    public function walletDetailAction() {
        $id = default2Int($_GET['id'], 0);
        $user_id = $this->user_session->user_id;
        $wallet = WalletsMapper::findOrCreateWallets($this->account_dbobj, $user_id);

        $wa = new WalletActivity($this->account_dbobj);
        $wa->findOne('id = ' . $id . ' and wallet_id = ' . $wallet->getId());
        if($wa->getId()<1){
            redirect(getSiteMerchantUrl("/me/wallet"));
            return;
        }
        $this->view->wallet = $wallet;
        $this->view->wa =$wa;
        // status and ref
        $this->view->wa_status = "Pending";
        if($wa->getStatus() == COMPLETED){
            $this->view->wa_status = "Available";
        }
        $this->view->wa_ref = "";
        if($wa->getType()=="sale"){
            $this->view->wa_ref = "Sales Order " . $wa->getRefId();
            $this->view->wa_ref_link = "/selling/orders/detail?id= " . $wa->getRefId();
        }else {
            $this->view->wa_ref = "Resell Order " . $wa->getRefId();
            $this->view->wa_ref_link = "/me/orders/resell";
        }
    }

    public function paymentAccountsPaypalAction() {
        
        
    }

    public function paymentAccountsBankAction() {

    }

    public function paymentAccountsCreditcardAction() {

        global $dbconfig;
        
        $this->view->month_list = getExpMonthList();
        $this->view->year_list = getExpYearList();
        
        $account_dbname = $dbconfig->account->name;
        $countries = BaseMapper::getCachedObjects(CacheKey::q($account_dbname.'.countries'));
        $countries = $countries['data'];

        $this->view->countries = $countries;
        
        $credit_card_ids = $this->user['credit_card_ids'];
        // currently, we only allow one credit card per user
        if(!empty($credit_card_ids)) {
            $this->view->credit_card = BaseModel::findCachedOne($account_dbname.'.credit_card?id='.$credit_card_ids[0]);
        }
        
        
    }

    public function verifyAction() {
        
        if(empty($_REQUEST['code'])) {
            redirect(getURL());
        }
        
        $service = AccountsService::getInstance();
        $service->setMethod('verify');
        $service->setParams(array(
            'code' => $_REQUEST['code'],
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
            
        if($this->is_user()) {
            $next_page = '/dashboard';
        } else {
            $next_page = getURL();
        }
        
        $this->view->status = $service->getStatus();
        $this->view->next_page = $next_page;        
    }

}

