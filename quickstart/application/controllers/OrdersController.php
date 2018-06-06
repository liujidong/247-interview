<?php

class OrdersController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function indexAction() {
        $service = MerchantService::getInstance();
        $service->setMethod('get_orders');
        $service->setParams(array(
            'account_dbobj' => $this->account_dbobj,
            'store_dbobj' => $this->store_dbobj
        ));
        $service->call();
        
        $this->view->orders = $service->getResponse();
    }
        
    public function itemAction() {
        if(empty($_REQUEST['id'])) {
            redirect('/orders');
        }
        
        $order_id = $_REQUEST['id'];
        $service = MerchantService::getInstance();
        $service->setMethod('get_order_details');
        $service->setParams(array(
            'order_id' => $order_id,
            'account_dbobj' => $this->account_dbobj,
            'store_dbobj' => $this->store_dbobj
        ));
        $service->call();
        
        $response = $service->getResponse();
        $this->view->currency_symbol = currency_symbol($response['currency_code']);
        $this->view->items = $response['order_details'];       
        $this->view->address = $response['address'];
        $this->view->order_status = $response['order_status'];
    }
}   

