<?php

class PaypalAccountsMapper {


    public static function getPaymentAccountId($paypal_account_id, $dbobj) {
        $sql = "select id from payment_accounts where paypal_account_id=$paypal_account_id";
        $return = 0;
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return = $record['id'];
            }
        }

        return $return;

    }

    public static function findOrCreatePaypalAccount($dbobj, $email, $data = array()) {
        $ret = new PaypalAccount($dbobj);
        $ret->findOne("username = ' " . $dbobj->escape($email) . "'");
        if($ret->getId() < 1){
            $ret->setUsername($email);
            foreach($data as $k => $v){
                $ret->set($k, $v);
            }
            $ret->save();
        }
        return $ret;
    }
}
