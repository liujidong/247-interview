<?php
require_once __DIR__.'/../../scripts/includes.php';

$status = 'FAILURE';
$messages = array();

$account_dbname = $dbconfig->account->name;
//$username = 'xxx@gmail.com';
$username = $shopinterest_config->test->username;
$user= BaseModel::findCachedOne(CacheKey::q($account_dbname.'.user?username='.$username));
$store_id = $user['store']['id'];
$store_dbobj = DBObj::getStoreDBObjById($store_id);

$file_path = __DIR__.'/../../scripts/tests/data/esty_test_01.csv';

$service = new ProductService();
$service->setMethod('get_product_from_csv');
$service->setParams(array(
    'file_path' => $file_path,
));
$service->call();

if($service->getStatus() === 0) {
    // nothing to do
} else {
    $messages[] = 'Get Products From  Esty CSV Test Fails';
    dddd($status, $messages);
}

$products = $service->getResponse();

$service1 = new StoreService();
$service1->setMethod('create_products');
$service1->setParams(array(
    'products' => $products,
    'store_dbobj' => $store_dbobj
));
$service1->call();

if($service1->getStatus() === 0) {
    $status = 'SUCCESS';
    $messages[] = 'Import Esty CSV Test Succeeds';
} else {
    $messages[] = 'Import Esty CSV Test Fails';
}

ddd($status, $messages);
