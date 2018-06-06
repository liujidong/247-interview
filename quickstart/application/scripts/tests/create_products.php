<?php
require_once __DIR__.'/../../scripts/includes.php';

$status = 'FAILURE';
$messages = array();

$products = array();

$raw_url1 = 'http://media-cache-ak0.pinimg.com/736x/5c/bb/da/5cbbda0810b1c305c6dc98731793f067.jpg';
$raw_url2 = 'http://media-cache-ak0.pinimg.com/736x/8f/a4/f7/8fa4f7916ff5512a98a51d427bbd73c7.jpg';
 
$filepicker = new Filepicker();

// pack the info for the first product

$image_resource1 = $filepicker->store_image($raw_url1);

$products[0]['name'] = uniqid();
$products[0]['pictures'][0]['url'] = $image_resource1['url'];
$products[0]['pictures'][0]['type'] = 'original';
$products[0]['pictures'][0]['source'] = 'filepicker';
$messages[] = 'Product 1 name: '.$products[0]['name'];

// pack the info for the 2nd product
$image_resource2 = $filepicker->store_image($raw_url2);

$products[1]['name'] = uniqid();
$products[1]['pictures'][0]['url'] = $image_resource2['url'];
$products[1]['pictures'][0]['type'] = 'original';
$products[1]['pictures'][0]['source'] = 'filepicker';
$messages[] = 'Product 2 name: '.$products[1]['name'];

// setup the test data
global $dbconfig;
$account_dbname = $dbconfig->account->name;
$username = 'xxx@gmail.com';
$user= BaseModel::findCachedOne(CacheKey::q($account_dbname.'.user?username='.$username));
$store_id = $user['store']['id'];
$store_dbobj = DBObj::getStoreDBObjById($store_id);
$messages[] = 'store id is '.$store_id;

$service = StoreService::getInstance();
$service->setMethod('create_products');
$service->setParams(array(
    'products' => $products, 
    'store_dbobj' => $store_dbobj
));
$service->call(); 



if($service->getStatus() === 0) {
    $status = 'SUCCESS';
    $messages[] = 'Creating Products Test Succeeds';
} else {
    $messages[] = 'Creating Products Test Fails';
}

ddd($status, $messages);
