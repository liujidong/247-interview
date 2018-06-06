<?php

class WalletsMapper {

    public static function findOrCreateWallets($dbobj, $user_id){
        $cond = "user_id = " . $user_id . " and status = " . ACTIVATED;
        $w = new Wallet($dbobj);
        $w->findOne($cond);
        if($w->getId()<1){
            $w->setStatus(ACTIVATED);
            $w->setUserId($user_id);
            $w->save();
        }
        return $w;
    }

    public static function updateWallet($dbobj, $wid){
        // TODO: deal the currency
        $sql = "update wallets set current_balance = (
                  select sum(current_balance) from wallet_activities
                  where wallet_id = $wid and status != 127
                ) where id = $wid";
        $dbobj->query($sql, $dbobj);

        $sql = "update wallets set available_balance = (
                  select sum(available_balance) from wallet_activities
                  where wallet_id = $wid and status != 127
                ) where id = $wid";
        $dbobj->query($sql, $dbobj);
    }

    public static function fillWallets($dbobj, $order_grp_id){
        global $dbconfig;
        $order_grp = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name . ".myorder_group?id=$order_grp_id"));
        $uid_for_aid = ResellOrderItemsMapper::getUserIdOfAid($dbobj, $order_grp['aid']);
        $orders = MyorderGroupsMapper::getOrders($dbobj, $order_grp_id, COMPLETED);

        foreach($orders as $order){

            $order_commission = 0;
            $items = MyordersMapper::getOrderItems($dbobj, $order['id']);
            foreach($items as $item){
                $commission_rate = $item['product_commission'];
                if($commission_rate < 5.0){
                    $commission_rate = 5.0;
                }
                $commission = ((int)($item['product_price'] * $item['product_quantity'] * $commission_rate)) / 100.0;
                $roi = new ResellOrderItem($dbobj);
                $roi->setMyorderItemId($item['id']);
                $roi->setUserId($uid_for_aid);
                $roi->setAid($order_grp['aid']);
                $roi->setCommission($commission);
                $roi->setPaymentStatus(PENDING);
                $roi->setStatus(ACTIVATED);
                $roi->setCurrency($order_grp['currency_code']);
                $roi->save();
                $order_commission += $commission;

                $commission_wallet = WalletsMapper::findOrCreateWallets($dbobj, $uid_for_aid);
                $commission_wallet_id = $commission_wallet->getId();
                $wa = new WalletActivity($dbobj);
                $wa->setStatus(PENDING);
                $wa->setWalletId($commission_wallet_id);
                $wa->setRefId($roi->getId());
                $wa->setType('commission');
                $wa->setCurrency($order_grp['currency_code']);
                $wa->setAmount($commission);
                $wa->setCurrentBalance($commission);
                $wa->setAvailableBalance(0);
                $wa->save();
                WalletsMapper::updateWallet($dbobj, $commission_wallet_id);
            }

            $store_id = $order['store_id'];
            $ck = CacheKey::q($dbconfig->account->name . ".store?id=$store_id");
            $store = BaseModel::findCachedOne($ck);
            $uid = $store['uid'];

            $wallet = self::findOrCreateWallets($dbobj, $uid);
            $has_verified_cards = CreditCardsMapper::hasVerifiedCreditCard($dbobj, $uid);
            $wallet_id = $wallet->getId();
            $wa = new WalletActivity($dbobj);
            $wa->setStatus(PENDING);
            $wa->setWalletId($wallet_id);
            $wa->setRefId($order['id']);
            $wa->setType('sale');
            $wa->setCurrency($store['currency']);
            $wa->setAmount($order['total']);
            $wa->setCommission($order_commission);

            //if(Store::isSubscribed($store)){
            //    $balance = $order['total'] - $order_commission;
            //} else {
            $balance = ((int)(($order['total'] - $order['shipping'] - $order['tax']) * 95)) / 100
                - $order_commission + $order['shipping'] + $order['tax'];
            $wa->setOurTransactionFee(((int)(($order['total'] - $order['shipping'] - $order['tax']) * 5)) / 100);
            //}
            // paypal fee : 2.9%+0.3
            if($store['transaction_fee_waived'] == 0) {
                $balance = ((int)($balance * 97.1 - 30)) / 100;
            }
            $wa->setCurrentBalance($balance);
            if($balance < 100 && $has_verified_cards){ // predicate by balance or amount?
                $wa->setAvailableBalance($balance);
                $wa->setStatus(ACTIVATED);
            } else {
                $wa->setAvailableBalance(0);
            }
            $wa->save();
            self::updateWallet($dbobj, $wallet_id);
        }
    }

    public static function getWalletActivitiesForUser($dbobj, $uid, $status = '', $page_num = 1, $page_size = 10){
        $wallet = self::findOrCreateWallets($dbobj, $uid);
        $wid = $wallet->getId();

        $limit = " order by status asc, updated desc limit " . (($page_num-1) * $page_size) . "," . $page_size;
        $cond = " status != " . DELETED;
        if(!empty($status)){
            $cond = " status = " . $status;
        }
        $sql = "select * from wallet_activities where wallet_id = $wid and $cond $limit";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                if($record['status'] == PENDING){
                    $record['status'] = "Pending";
                } else if($record['status'] == ACTIVATED){
                    $record['status'] = "Available";
                } else if($record['status'] == COMPLETED){
                    $record['status'] = "Completed";
                }
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getWalletActivitiesCntForUser($dbobj, $uid, $status = '') {
        $wallet = self::findOrCreateWallets($dbobj, $uid);
        $wid = $wallet->getId();

        $cond = " status != " . DELETED;
        if(!empty($status)){
            $cond = " status = " . $status;
        }
        $sql = "select count(1) as cnt from wallet_activities where wallet_id = $wid and $cond";

        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['cnt'];
            }
        }
        return 0;
    }

    public static function getCommissionWalletActivitiesForOrder($dbobj, $oid) {
        $sql = "select wa.*
                from wallet_activities wa
                join resell_order_items roi on (roi.id = wa.ref_id)
                join myorder_items mi on (mi.id = roi.myorder_item_id)
                where wa.type = 'commission' and wa.status != " . DELETED . "
                and roi.status != " . DELETED . " and mi.order_id = $oid
                and mi.status !=" . DELETED;

        $return =array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function updateWalletAfterVerifiedCard($dbobj, $uid){
        $sql_0 = "select id from wallets where user_id = $uid and status != 127";
        $wids = array();
        if($res = $dbobj->query($sql_0, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $wids[] = $record['id'];
            }
        }

        foreach($wids as $wid){
            $sql_1 = "update wallet_activities set status = " . ACTIVATED . ",
                      available_balance = current_balance
                      where wallet_id = $wid and status = " . PENDING . "
                      and current_balance < 100";
            $dbobj->query($sql_1, $dbobj);
            $sql_1 = "update wallet_activities wa join myorders mo on (wa.ref_id = mo.id)
                      set wa.status = " . ACTIVATED . ",
                      wa.available_balance = wa.current_balance
                      where wa.wallet_id = $wid and wa.status = " . PENDING . "
                      and wa.current_balance >= 100 and mo.tracking_number != ''";
            $dbobj->query($sql_1, $dbobj);
            WalletsMapper::updateWallet($dbobj, $wid);
        }

    }
}
