<?php

class WalletActivity extends BaseModel {

    public static function format($data, $ck){
        global $dbconfig;
        $p_status = $data['status'];
        if($p_status == PENDING){
            $status = 'pending';
        }elseif($p_status == ACTIVATED){
            $status = 'activated';
        }elseif($p_status == COMPLETED){
            $status = 'completed';
        }elseif($p_status == CREATED){
            $status = 'created';
        }elseif($p_status == DELETED){
            $status = 'deleted';
        }else{
            $status = '--';
        }
        $data['status_literal'] = $status;
        return $data;
    }

}
