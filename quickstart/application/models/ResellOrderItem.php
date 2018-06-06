<?php

class ResellOrderItem extends BaseModel {

    public static function format($data, $ck){
        $status = 'pending';
        $p_status = $data['status'];
        if($p_status == PENDING){
            $status = 'pending';
        }elseif($p_status == COMPLETED){
            $status = 'completed';
        }
        $data['status_literal'] = $status;

        if($data['product_commission'] < 3){
            $data['product_commission'] = 3;
        }

        return $data;
    }

}
