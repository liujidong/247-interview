<?php

class DashboardController extends BaseController {

    public function init() {

    }

    public function indexAction() {
        global $dbconfig;

        $user_id = $this->user_session->user_id;
        $store_id = $this->store['id'];

        //$this->view->unpaid_order_cnt = MyordersMapper::getOrdersCountForUser($this->account_dbobj, $user_id, ORDER_UNPAID);
        $this->view->paid_order_cnt = MyordersMapper::getOrdersCountForUser($this->account_dbobj, $user_id, ORDER_PAID);
        $this->view->shipped_order_cnt = MyordersMapper::getOrdersCountForUser($this->account_dbobj, $user_id, ORDER_SHIPPED);

          $this->view->week_resellitems_cnt = ResellOrderItemsMapper::getResellOrderItemsCount(
              $this->account_dbobj, $user_id, getNdaysago(7)
          );
          $this->view->all_resellitems_cnt = ResellOrderItemsMapper::getResellOrderItemsCount($this->account_dbobj, $user_id);
          $this->view->resellorder_cnt = $this->view->all_resellitems_cnt;

        if($this->view->is_merchant){
            $this->view->paid_salesorder_cnt = MyordersMapper::getOrdersCountForStore($this->account_dbobj, $store_id, ORDER_PAID);
            $this->view->shipped_salesorder_cnt = MyordersMapper::getOrdersCountForStore($this->account_dbobj, $store_id, ORDER_SHIPPED);
            $this->view->week_salesorder_cnt = MyordersMapper::getOrdersCountForStore(
                $this->account_dbobj, $store_id, NULL, NULL, getNdaysago(7));
            $this->view->total_salesorder_cnt = MyordersMapper::getOrdersCountForStore($this->account_dbobj, $store_id);

            $this->view->active_products_cnt = DAL::getListCount(lck_store_active_products($dbconfig->store->name . "_" . $store_id));

            $this->view->products_cnt = DAL::getListCount(lck_store_active_products($dbconfig->store->name . "_" . $store_id));
            $this->view->resell_products_cnt = DAL::getListCount(lck_store_resell_products($dbconfig->store->name . "_" . $store_id));
            $this->view->is_launched = Store::isLaunched($this->store, $this->user);
            $this->view->launch_cond = Store::canLaunch($this->store, TRUE, $this->user);
            $this->view->is_subscribed = Store::isSubscribed($this->store);
        }

        $wallet = WalletsMapper::findOrCreateWallets($this->account_dbobj, $user_id);
        $this->view->wallet = $wallet;
    }

    public function storePreviewAction() {

    }

}