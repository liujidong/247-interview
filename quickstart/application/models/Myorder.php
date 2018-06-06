<?php

class Myorder extends BaseModel {

    public static function format($data, $ck){
        global $dbconfig;
        $uck = CacheKey::q($dbconfig->account->name . ".user?id=" . $data['user_id']);
        $data['customer'] = BaseModel::findCachedOne($uck);
        $status = 'unknow';
        $p_status = $data['payment_status'];
        if($p_status == ORDER_CANCELED){
            $status = 'canceled';
        }elseif($p_status == ORDER_UNPAID){
            $status = 'unpaid';
        }elseif($p_status == ORDER_PAID){
            $status = 'paid';
        }elseif($p_status == ORDER_SHIPPED){
            $status = 'shipped';
        }elseif($p_status == ORDER_COMPLETED){
            $status = 'completed';
        }
        $data['payment_status_literal'] = $status;
        $data['shipping_date'] = preg_replace("/ \d\d:\d\d:\d\d$/", "", $data['shipping_date']);
        $data['order_num'] = "#0623-NC-GRP" . sprintf("%06d", $data['myorder_group_id']) . "-MO" . sprintf("%06d", $data['id']);
        return $data;
    }
}
