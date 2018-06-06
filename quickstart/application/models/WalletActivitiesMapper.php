<?php

class WalletActivitiesMapper {

    public static function getCachedObject($params, $dbobj) {
        $waid = $params['id'];
        $sql = "select wa.*,
                w.user_id
                from wallet_activities wa
                join wallets w on (wa.wallet_id = w.id)
                where wa.id = $waid";

        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $record['currency_symbol'] = currency_symbol($record['currency']);
                return $record;
            }
        }
        return NULL;
    }

    public static function getCachedObjectList($params, $dbobj) {
        $ck = $params['_cachekey'];
        $dbname = $ck->getDBName();
        $sort = $ck->getOrderInfo();
        $sort = $sort['orderby'];

        $keys = array();
        $was = array();
        $sql = "";
        $cond ="";

        if(isset($params['status']) && $params['status'] == DELETED){
            $cond = "status != " . DELETED;
        }else{
            $cond = "status = " . $params['status'];
        }

        if(isset($params['type'])){
            $cond = $cond . " and type = '" . $dbobj->escape($params['type']) ."'";
        }
        
        if(isset($params['user_id'])){
            $sql = "select wa.id, wa.updated
                    from wallet_activities wa
                    join wallets w on (wa.wallet_id = w.id)
                    where wa.$cond and w.user_id = " . $params['user_id'];
        } else {
            $sql = "select id, updated from wallet_activities where $cond";
        }

        $sql = $sql . $ck->orderSQL() . $ck->limitSQL() ;

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $was[] = $record;
            }
        }

        if(count($sort) != 1) { // no order, no score
            foreach($was as $wa) {
                $key = $dbname.".wallet_activity?id=".$wa['id'];
                $keys[] = $key;
            }
        } else {
            $order_key = $sort[0];
            foreach($was as $wa) {
                $score = 0;
                if($order_key == 'updated'){
                    $score = strtotime2($wa['updated']);
                } else {
                    $score = isset($product[$order_key]) ? $product[$order_key] : 0;
                }
                $key = $dbname.".wallet_activity?id=".$wa['id'];
                $keys[$key] = $score;
            }
        }
        return $keys;
    }

    public static function getCachedObjectListCount($params, $dbobj) {
        $ck = $params['_cachekey'];
        $dbname = $ck->getDBName();
        $sort = $ck->getOrderInfo();
        $sort = $sort['orderby'];

        $keys = array();
        $was = array();
        $sql = "";
        $cond ="";

        if(isset($params['status']) && $params['status'] == DELETED){
            $cond = "status != " . DELETED;
        }else{
            $cond = "status = " . $params['status'];
        }

        if(isset($params['type'])){
            $cond = $cond . " and type = '" . $dbobj->escape($params['type']) ."'";
        }

        if(isset($params['user_id'])){
            $sql = "select count(1) as cnt
                    from wallet_activities wa
                    join wallets w on (wa.wallet_id = w.id)
                    where wa.$cond and w.user_id = " . $params['user_id'];
        } else {
            $sql = "select count(1) as cnt from wallet_activities where $cond";
        }

        if($res = $dbobj->query($sql)){
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['cnt'];
            }
        }
    }
}
