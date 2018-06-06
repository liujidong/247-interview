<?php

require 'includes.php';

$sql_all_stores = "select id, shipping, additional_shipping from stores";

$sql_avg_extra = "select avg(shipping) as extra
                  from
                  (select p.shipping from products p
                  join products_pictures pp on (p.id = pp.product_id)
                  where p.status!= 127
                  and p.name!='' and p.price!='' and p.quantity != '' and p.global_category_id !=0
                  group by p.id) es";

if($res = $account_dbobj->query($sql_all_stores, $account_dbobj)) {
    while($record = $account_dbobj->fetch_assoc($res)) {
        echo "processing store ", $record['id'];
        $store_dbobj = DBObj::getStoreDBObjById($record['id']);
        $base = empty2($record['shipping']) ? $record['shipping'] : 0;
        $additional = empty2($record['additional_shipping']) ? $record['additional_shipping'] : 0;

        $extra = 0;
        if($avg_res = $store_dbobj->query($sql_avg_extra, $store_dbobj)) {
            if($avg_record = $store_dbobj->fetch_assoc($avg_res)){
                $extra = (int)($avg_record['extra']);
            }
        }
        $base += $extra;
        $additional += $extra;

        $store_dbobj->query("truncate table shipping_options", $store_dbobj);
        $store_dbobj->query("truncate table shipping_destinations", $store_dbobj);

        $std_opt = new ShippingOption($store_dbobj);
        $std_opt->findOne("name = 'Standard' and status != " . DELETED);
        if($std_opt->getId() < 1){
            $std_opt->setName('Standard');
            //$std_opt->setStatus(ACTIVATED);
            $std_opt->save();
        }
        $opt_id = $std_opt->getId();
        $dest_opt = new ShippingDestination($store_dbobj);
        $dest_opt->findOne("name = 'Domestic' and shipping_option_id = $opt_id and status != " . DELETED);
        $dest_opt->setName('Domestic');
        $dest_opt->setShippingOptionId($opt_id);
        $dest_opt->setFromdays('2');
        $dest_opt->setTodays('5');
        $dest_opt->setBase($base);
        $dest_opt->setAdditional($additional);
        $dest_opt->save();

        $dest_opt2 = new ShippingDestination($store_dbobj);
        $dest_opt2->findOne("name = 'International' and shipping_option_id = $opt_id and status != " . DELETED);
        $dest_opt2->setName('International'); 
        $dest_opt2->setShippingOptionId($opt_id);
        $dest_opt2->setFromdays('5');
        $dest_opt2->setTodays('10');
        $dest_opt2->setBase($base);
        $dest_opt2->setAdditional($additional);
        $dest_opt2->save();
        
        echo " ... done\n";
    }
}
