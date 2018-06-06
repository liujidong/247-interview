<?php

class PaymentItemsMapper {
    public static function getPaymentItems($dbobj) {

        $sql = "select * from payment_items";

        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
    
    // for generating the mass payment csv
    public static function getPayments($dbobj) {
        
        global $paypalconfig;
        $shopinterest_paypal_username = $paypalconfig->user->email;      
        
        $sql = "select receiver as email, sum(amt) as amt, currency_code, 
                sender_id as sender,receiver_id as receiver, group_concat(id) as payment_item_ids
                from 
                (select pay.username as receiver, if(payt.username is null, '$shopinterest_paypal_username', payt.username) as sender, 
                pi.amt, pi.currency_code, 
                pi.sender as sender_id,pi.receiver as receiver_id,pi.status, pi.payment_id, pi.contract, pi.created, pi.updated,
                pi.id, 
                if(op.store_id is null, 0, op.store_id) as store_id, 
                if(op.order_id is null, 0, op.order_id) as order_id, 
                if(sp.id is null, 0, sp.id) as sale_id
                from
                payment_items pi join payment_accounts pa on (pi.receiver=pa.id)
                left join payment_accounts pat on  (pi.sender=pat.id)
                join paypal_accounts pay on (pay.id=pa.paypal_account_id) 
                left join paypal_accounts payt on (payt.id=pat.paypal_account_id)
                left join order_payments op on (op.payment_item_id = pi.id)
                left join sale_payments sp on (sp.payment_item_id = pi.id) 
                where
                pi.receiver!=0 and pi.payment_id =0
                group by pi.id) middle_table 
                where 
                status = ".PROCESSING."
                group by receiver, currency_code";        

        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
    
    public static function updatePaymentsStatus($payment_id, $status, $dbobj) {

        $sql = "update payments p join payment_items pi on (p.id=pi.payment_id)
                set p.status=$status, pi.status=$status
                where p.id=$payment_id
                ";

        $dbobj->query($sql);
    }
    
    public static function getfuturepayment($dbobj, $page_num =1) {
        global $paypalconfig;
        $shopinterest_paypal_username = $paypalconfig->user->email;
        
        if($page_num === 0) {
            $limit = '';
        } else {
            $start = PAYMENT_ITEMS_PER_PAGE * ( $page_num - 1);
            $limit = 'limit '.$start.', '.PAYMENT_ITEMS_PER_PAGE;
        }
        $sql = "select pay.username as receiver, if(payt.username is null, '$shopinterest_paypal_username', payt.username) as sender, 
                pi.amt, pi.currency_code, 
                pi.status, pi.payment_id, pi.contract, pi.created, pi.updated,
                pi.id, 
                if(op.store_id is null, 0, op.store_id) as store_id, 
                if(op.order_id is null, 0, op.order_id) as order_id, 
                if(sp.id is null, 0, sp.id) as sale_id
                from
                payment_items pi join payment_accounts pa on (pi.receiver=pa.id)
                left join payment_accounts pat on  (pi.sender=pat.id)
                join paypal_accounts pay on (pay.id=pa.paypal_account_id) 
                left join paypal_accounts payt on (payt.id=pat.paypal_account_id)
                left join order_payments op on (op.payment_item_id = pi.id)
                left join sale_payments sp on (sp.payment_item_id = pi.id) 
                where
                pi.receiver!=0
                group by pi.id order by pi.updated desc $limit";

        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;       
    }
    
    public static function getfuturepaymentsCount($dbobj) {
        $sql = "select count(1) as cnt
                from
                payment_items pi join payment_accounts pa on (pi.receiver=pa.id)
                left join payment_accounts pat on  (pi.sender=pat.id)
                join paypal_accounts pay on (pay.id=pa.paypal_account_id) 
                left join paypal_accounts payt on (payt.id=pat.paypal_account_id)
                left join order_payments op on (op.payment_item_id = pi.id)
                left join sale_payments sp on (sp.payment_item_id = pi.id) 
                where
                pi.receiver!=0";
        $return = 0;
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['cnt'];
            }
        }
        return $return;
    }    
    
}


