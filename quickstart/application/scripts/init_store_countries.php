<?php

require 'includes.php';

$currency_sql = "update stores set currency = 'USD' where currency = ''";
$country_sql = "select a.country, s.id as store_id
                from addresses a
                left join users_addresses ua on ( ua.address_id = a.id )
                left join users u on ( u.id = ua.user_id )
                left join merchants_stores  ms on ( u.merchant_id = ms.merchant_id )
                left join stores s on (ms.store_id = s.id)
                where s.country=''";


if($res = $account_dbobj->query($country_sql, $account_dbobj)) {
    while($record = $account_dbobj->fetch_assoc($res)) {
        $store = new Store($account_dbobj);
        $store->findOne("id = ${record['store_id']}");
        if($store->getId()>0){
            $store->setCountry($record['country']);
            $store->save();
        }
    }
}

$account_dbobj->query($currency_sql, $account_dbobj);