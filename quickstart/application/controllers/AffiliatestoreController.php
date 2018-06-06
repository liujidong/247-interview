<?php

class AffiliatestoreController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function indexAction() {
        
        global $redis, $shopinterest_config;

        $aid = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $associate_id = $redis->get("associate:aid=$aid:id");
        $associate_status = $redis->get("associate:$associate_id:status");
        $page_num = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
        $account_dbobj = $this->account_dbobj;
        $products_cnt = SalesMapper::getProductCnt($associate_id, $account_dbobj);  
        
        if($associate_id && $associate_status == ACTIVATED && !empty($products_cnt)) {
            $response = SalesMapper::getProducts($associate_id,  $account_dbobj, $description = '', $commission_start = 0, $commission_end = 0, $price_start = 0,$price_end = 0, $page_num);

            $this->view->products_info = $response;
            $this->view->total_rows = $products_cnt;
            $this->view->rows_per_page = SALESNETWORK_PRODUCT_NUM_PER_PAGE;
            $this->view->page_num = $page_num;
            $this->view->extra_params = array('id'=>$aid);
            $this->view->default_store_logo = $shopinterest_config->store->logo->default;
        } else {
            redirect(getSiteMerchantUrl());     
        }     
    }
}   