<?php

$store_id = isset($argv[1])?$argv[1]:'';

if(empty($store_id)) {
    die("ERROR: php ".$_SERVER['SCRIPT_NAME'].".php <store_id>\n");
}

require_once('includes.php');

echo "Application Env:".APPLICATION_ENV."\n";

$store_db_name = getStoreDBName($store_id);
$store_dbobj = DBObj::getStoreDBObjById($store_id);

$product_ids = array();
$sql_0 = "select id from products where status != " . DELETED;
if($res = $store_dbobj->query($sql_0)) {
    while($record = $store_dbobj->fetch_assoc($res)) {
                $product_ids[] = $record['id'];
    }
}

$file = fopen("store-$store_id-products.csv","w");

//commission,pinterest_pin_id,pictures,global_category_id,status,brand,ext_ref_url,quantity,tags,start_date,product_url,shipping_options,category_l1,id,category_path,shipping_destinations,resell,misc,featured,price,store_currency,name,store_subdomain,size,store_logo,root_category_id,custom_fields,picture_count,end_date,ext_ref_id,shipping,store_status,store_id,featured_score,free_shipping,description,created,store_url,updated,store_description,store_name,purchase_url,row_id
$headers = array(
    "id", "name", "category_path", "price", "store_currency", "quantity", "description",
    "tags", "pictures", "ext_ref_url", "product_url",
    "custom_fields", "created"
);

fputcsv($file, $headers);
foreach($product_ids as $pid){
    $ck = $store_db_name . ".product?id=" . $pid;
    $product = BaseModel::findCachedOne($ck);
    $values = array();
    foreach($headers as $k){
        if(is_array($product[$k])){
            $values[] = json_encode($product[$k]);
        } else {
            $values[] = $product[$k];
        }
    }
    fputcsv($file, $values);
}

fclose($file);