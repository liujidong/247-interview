<?php

class CustomersMapper {
        
    public static function deleteCustomerById($id, $dbobj){
        $sql = 'delete c.*, cce.*, cca.*, ccp.*
            from 
            customers c join customers_cust_emails cce on (c.id=cce.customer_id)
            left join customers_cust_addresses cca on (c.id=cca.customer_id)
            left join customers_cust_phones ccp on (c.id=ccp.customer_id)
            where c.id='.$id;
        
        return $dbobj->query($sql);
    }

    public static function getCachedObject($params, $dbobj){
        $customer_id = $params['id'];
        $ck = $params['_cachekey'];

        $sql = "select c.id as customer_id, c.status, c.first_name, c.last_name, ce.id as email_id, ce.email from customers c
                join customers_cust_emails cce on (c.id = cce.customer_id)
                join cust_emails ce on (cce.cust_email_id = ce.id)
                where c.id = $customer_id";

        $return = array();
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
        }
        return $return;
    }
    
    public static function getCachedObjectList($params, $dbobj) {

        $ck = $params['_cachekey'];

        $dbname = $ck->getDBName();

        $sql = "select c.id, c.updated from customers_cust_emails cce
        join cust_emails ce on cce.cust_email_id = ce.id
        join customers c on c.id = cce.customer_id
        where c.status != ".DELETED."
        group by c.id ";

        $keys = array();
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {

                $score = strtotime2($record['updated']);
                $key = $dbname.".customer?id=".$record['id'];
                $keys[$key] = $score;
            }
        }
        return $keys;
    }
    
}


