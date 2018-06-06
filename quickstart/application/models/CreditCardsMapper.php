<?php

class CreditCardsMapper{

    public static function hasVerifiedCreditCard($dbobj, $user_id) {
        $sql = "select count(1) as cnt from credit_cards
                where user_id = $user_id and verified = 1 and status!=".DELETED;

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['cnt'] > 0;
            }
        }
        return false;
    }

}
