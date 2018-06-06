<?php
class PaymentAccountsMapper {

    public static function findOrCreatePaymentAccount($dbobj, $uid, $query = array(), $retry = 0) {

        $sql = "select
                pa.*
                from payment_accounts pa
                join users_payment_accounts upa
                where upa.user_id = $uid";

        foreach($query as $k => $v){
            $sql .= " and pa.$k = $v";
        }

        $return = array();
        if ($res = $dbobj->query($sql)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        if(count($return) > 0){
            return $return[0];
        }
        if($retry > 2) return null;
        // create one
        $users=array(
            array(
                "id"=> $uid,
                "payment_accounts"=> array($query),
            )
        );
        BaseModel::saveObjects($dbobj, $users, "users");

        return self::findOrCreatePaymentAccount($dbobj, $uid, $query, $retry + 1);
    }
}
