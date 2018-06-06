<?php

class MyorderService extends BaseService{

    public function cancel_order() {
        global $dbconfig;
        global $shopinterest_config;
        $account_dbobj = $this->params['account_dbobj'];
        $job_dbobj = $this->params['job_dbobj'];
        $is_admin = $this->params['is_admin'];
        $order_id = $this->params['order_id'];
        $my_store = $this->params['store'];

        $ck = CacheKey::q($dbconfig->account->name . ".myorder?id=" . $order_id);
        $myorder_model = BaseModel::findCachedOne($ck, array('force'=>True));
        if($myorder_model['store_id'] != $my_store['id'] && !$is_admin){
            $this->status = 1;
            return;
        }

        $myorder = new Myorder($account_dbobj, $order_id);
        $myorder->setPaymentStatus(ORDER_CANCELED);
        $myorder->save();

        $items = MyordersMapper::getOrderItems($account_dbobj, $order_id);
        foreach($items as $item){
            ProductsMapper::decreaseCount(
                $item['store_id'], $item['product_id'], $item['custom_fields'], 0 - $item['product_quantity']
            );
        }

        // update wallet for merchant
        $wa = new WalletActivity($account_dbobj);
        $wa->findOne(" type = 'sale' and ref_id = " . $order_id);
        if($wa->getId() > 0){
            $wa->setStatus(COMPLETED);
            $wa->save();
            // cancel order
            $cwa = new WalletActivity($account_dbobj);
            $cwa->setWalletId($wa->getWalletId());
            $cwa->setCurrency($wa->getCurrency());
            $cwa->setAmount(0 - $wa->getAmount());
            $cwa->setCurrentBalance(0 - $wa->getCurrentBalance());
            $cwa->setAvailableBalance(0 - $wa->getAvailableBalance());
            $cwa->setRefId($wa->getRefId());
            $cwa->setType('sale canceling');
            $cwa->setStatus(COMPLETED);
            $cwa->save();
            WalletsMapper::updateWallet($account_dbobj, $wa->getWalletId());
        }
        // update wallet for assoc
        $resell_was = WalletsMapper::getCommissionWalletActivitiesForOrder($account_dbobj, $order_id);
        foreach($resell_was as $rwa){
            $wa = new WalletActivity($account_dbobj, $rwa['id']);
            $wa->setStatus(COMPLETED);
            $wa->save();
            $cwa = new WalletActivity($account_dbobj);
            $cwa->setWalletId($wa->getWalletId());
            $cwa->setCurrency($wa->getCurrency());
            $cwa->setAmount(0 - $wa->getAmount());
            $cwa->setCurrentBalance(0 - $wa->getCurrentBalance());
            $cwa->setAvailableBalance(0 - $wa->getAvailableBalance());
            $cwa->setRefId($wa->getRefId());
            $cwa->setType('commission canceling');
            $cwa->setStatus(COMPLETED);
            $cwa->save();
            WalletsMapper::updateWallet($account_dbobj, $wa->getWalletId());
        }
        // send mails to shopper/seller/admin
        $user_ck = CacheKey::q($dbconfig->account->name.'.user?id='.$myorder_model['user_id']);
        $user = BaseModel::findCachedOne($user_ck);
        $store_ck = CacheKey::q($dbconfig->account->name.'.store?id='.$myorder_model['store_id']);
        $store = BaseModel::findCachedOne($store_ck);
        $merchant_uck = CacheKey::q($dbconfig->account->name.'.user?id='.$store['user_id']);
        $merchant = BaseModel::findCachedOne($merchant_uck);

        if($is_admin){
            $by = "Shopintoit Admin";
        } else {
            $by = "Merchant(${merchant['username']})";
        }

        $receivers = array(
            $user['username'] => SHOPPER_ORDER_CANCEL_NOTIFICATION,
            $merchant['username'] => MERCHANT_ORDER_CANCEL_NOTIFICATION,
            $shopinterest_config->support->email => ADMIN_ORDER_CANCEL_NOTIFICATION,
        );
        foreach($receivers as $email => $type){
            $service = new EmailService();
            $service->setMethod('create_job');
            $service->setParams(array(
                'to' => $email,
                'from' => $shopinterest_config->support->email,
                'type' => $type,
                'data' => array(
                    'site_url' => getURL(),
                    'store_url' => getStoreUrl($store['subdomain']),
                    'order_num' => $myorder_model['order_num'],
                    'by' => $by,
                ),
                'job_dbobj' => $job_dbobj,
            ));
            $service->call();
        }
        $this->status = 0;
        $this->response = array();
    }
}
