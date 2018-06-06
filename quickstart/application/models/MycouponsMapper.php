<?php

class MycouponsMapper {

    public static function get_coupons($dbobj, $params = array()) {

        $filter_str = '';
        $filter_arr = array();

        if(isset($params['status'])){
            $filter_arr[] = " m.status in (".implode(",",$params['status']) .")";
        }

        if($id = default2Int($params['id'])) {
            $filter_arr[] = " m.id = $id";
        }

        if($store_id = default2Int($params['store_id'])) {
            $filter_arr[] = " m.store_id = $store_id";
        }

        if(!empty($filter_arr)) {
            $filter_str = " where ".implode(' AND ', $filter_arr);
        }

        $sql = "select m.*, s.*, m.id as coupon_id
                from
                mycoupons m
                join stores s on (m.store_id = s.id)
                $filter_str
                group by m.id";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {

                $store_url = getStoreUrl($record['subdomain']);
                $product_url = $store_url."/products/item?id=".$record['product_id'];

                $scope = $record['scope'];
                if($scope == SITE) {
                    $record['scope'] = 'Site';
                } else if($scope == STORE) {
                    $record['scope'] = 'Store';
                    $record['store_url'] = $store_url;

                } else if($scope == PRODUCT) {
                    $record['scope'] = 'Product';
                    $record['product_url'] = $product_url;
                }

                $price_offer_type = $record['price_offer_type'];
                if($price_offer_type == PERCENTAGE_OFF) {
                    $record['price_offer_type'] = 'Percentage off';
                } else if($price_offer_type == FLAT_VALUE_OFF) {
                    $record['price_offer_type'] = 'Flat value off';
                }

                $record['is_sale'] = empty($record['is_sale']) ? 'No' : 'Yes';
                $record['free_shipping'] = empty($record['free_shipping']) ? 'No' : 'Yes';

                $return[] = $record;
            }
        }
        return $return;
    }

    public static function get_deal_coupons(
        $dbobj, $oper = 'admin', $page = 1/*1-based*/, $page_size = DEFAULT_PAGE_SIZE) {
        $limit = " limit " . (($page - 1) * $page_size) . ", " . $page_size;
        $sql = "select c.*, s.*, c.id as coupon_id
                from
                mycoupons c
                join stores s on (c.store_id = s.id)
                where operator = '$oper' and is_deal = 1 and scope = " . PRODUCT ."
                and start_time < now() and end_time > now() order by c.updated $limit";
        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function decreaseUsageLimit($dbobj, $code, $count = 1){
        $c = new Mycoupon($dbobj);
        $c->findOne("code = '$code'");
        if($c->getId()<1) return false;
        $sql = "update mycoupons set usage_limit = usage_limit - $count where code = '". $dbobj->escape($code) . "'";
        $dbobj->query($sql);
        DAL::delete($c->getCacheKey());
        DAL::delete(CacheKey::q($dbobj->getDBName() . ".mycoupon?code=$code"));
        return TRUE;
    }

    public static function getAvailableCoupon($code, $user_id, $dbobj){
        $sql = "select *, (start_time <=now() and end_time > now()) as time_ok
                from mycoupons where code = '" . $dbobj->escape($code) . "'
                and status != " . DELETED;
        $return = array();

        if ($res = $dbobj->query($sql)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            } else{
                return false;
            }
        }
        if(!$return['time_ok']){
            return false;
        }
        // usage limit check
        $ulimit = $return['usage_limit'];
        if($ulimit <= 0) return false;
        /*
        if($ulimit > 0){
            $used = 0;
            $sql = "select count(1) as cnt from carts where coupon_code = '" . $dbobj->escape($code) . "'
                and (status != " . DELETED . ")";
            if ($res = $dbobj->query($sql)) {
                if ($record = $dbobj->fetch_assoc($res)) {
                    $used += $record['cnt'];
                }
            }
            $sql = "select count(1) as cnt from cart_items where coupon_code = '" . $dbobj->escape($code) . "'
                and (status != " . DELETED . ")";
            if ($res = $dbobj->query($sql)) {
                if ($record = $dbobj->fetch_assoc($res)) {
                    $used += $record['cnt'];
                }
            }
            if($used >= $ulimit) return false;
        }
        */

        // restriction check
        $ur = $return['usage_restriction'];
        if($ur > 0){
            $used = 0;
            $sql = "select count(1) as cnt from carts c join users_carts uc on (c.id = uc.cart_id)
                    where c.coupon_code = '" . $dbobj->escape($code) . "'
                    and (c.status != " . DELETED . ")
                    and uc.user_id = $user_id";
            if ($res = $dbobj->query($sql)) {
                if ($record = $dbobj->fetch_assoc($res)) {
                    $used += $record['cnt'];
                }
            }
            $sql = "select count(1) as cnt from cart_items ci
                    join carts c on (c.id = ci.cart_id)
                    join users_carts uc on (c.id = uc.cart_id)
                    where ci.coupon_code = '" . $dbobj->escape($code) . "'
                    and (ci.status != " . DELETED . ")
                    and uc.user_id = $user_id";
            if ($res = $dbobj->query($sql)) {
                if ($record = $dbobj->fetch_assoc($res)) {
                    $used += $record['cnt'];
                }
            }
            if($used >= $ur) return false;
        }

        return $return;
    }

    public static function getCachedObjectList($params, $dbobj) {

        $dbname = $dbobj->getDBName();

        $sql = "select id, updated from mycoupons where usage_limit > 0 and status!=".DELETED . "
                and end_time < now()";

        if(isset($params['store_id'])) {
            $sql .= " and store_id = ". $params['store_id'];
        }

        $keys = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $score = strtotime2($record['updated']);
                $key = $dbname.".mycoupon?id=".$record['id'];
                $keys[$key] = $score;
            }
        }
        return $keys;
    }

}
