<?php

class PaymentsMapper {
    
    public static function getPayments($dbobj, $page_num =1) {
        
        if($page_num === 0) {
            $limit = '';
        } else {
            $start = PAYMENT_ITEMS_PER_PAGE * ( $page_num - 1);
            $limit = 'limit '.$start.', '.PAYMENT_ITEMS_PER_PAGE;
        }
        
        $sql = "select p.*,pay.username as receiver,payt.username as sender from payments p 
        left join payment_accounts pa on (p.receiver=pa.id)
        left join payment_accounts pat on (p.sender=pat.id)
        join paypal_accounts pay on (pay.id=pa.paypal_account_id) 
        join paypal_accounts payt on (payt.id=pat.paypal_account_id)         
        where
        p.receiver!=0 and
        p.sender!=0 and
        p.status != ".DELETED.
        ' order by p.updated desc '.
        $limit;

        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
    
    public static function getPaymentsCount($dbobj) {
        $sql = "select count(1) as cnt from payments where status != ".DELETED."";
        $return = 0;
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['cnt'];
            }
        }
        return $return;
    }   
    
    public static function getPaypayAccountByPaymentId($payment_id, $dbobj) {
        $sql = "select pa.username as username from payment_accounts p join paypal_accounts pa on (p.paypal_account_id = pa.id) where p.id=$payment_id";
        $return = '';
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['username'];
            }
        }
        return $return;        
    }
      
}


