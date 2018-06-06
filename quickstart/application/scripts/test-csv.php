<?php

require_once('includes.php');
$store_dbobj = DBObj::getStoreDBObjById(2);
$file_path = "/home/kdr2/01.csv";

$service = new ProductService();
$service->setMethod('get_product_from_csv');
$service->setParams(array(
    'file_path' => $file_path,
));
$service->call();

$products = $service->getResponse();

ddd(sizeof($products));
ddd($service->getStatus());
xhprof_enable();
$offset = 0; $step =2; $status =0;
while($offset < count($products)){
    $tmp_p = array_slice($products, $offset, $step);
    $offset += $step;
    $service1 = new StoreService();
    $service1->setMethod('create_products');
    $service1->setParams(array(
        'products' => $tmp_p,
        'store_dbobj' => $store_dbobj
    ));
    ddd("before call:". posix_getpid());
    $service1->call();
    ddd("after call:". posix_getpid());
}


$data = xhprof_disable();
$XHPROF_ROOT = '/home/kdr2/Pool/xhprof-0.9.4';
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
$xhprof_runs = new XHProfRuns_Default();
// Save the run under a namespace "xhprof".
$run_id = $xhprof_runs->save_run($data, "xhprof");
//ddd($response);
