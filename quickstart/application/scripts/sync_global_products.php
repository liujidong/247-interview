<?php

if(isset($argv[1]) && $argv[1] === 'test') {
    $GLOBALS['test'] = 1;
}

require_once('includes.php');
$job_type = SYNC_GLOBAL_PRODUCTS;
$max_run_times = 100;
$run_times = 0;

$sql = "select distinct store_id from global_products";
if ($res = $account_dbobj->query($sql)) {
    while($record = $account_dbobj->fetch_assoc($res)) {
        $store = BaseModel::findCachedOne(
            $dbconfig->account->name . ".store?id=" . $record['store_id'],
            array('force' => True)
        );
        if(empty($store) || $store['status'] != ACTIVATED){
            GlobalProductsMapper::deleteProductsInStore($account_dbobj, $record['store_id']);
        }
    }
}

while($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {

    // get the config of the job
    $job_config = new JobConfig($job_dbobj);
    $job_config->findOne('type='.$job_type);
    $max_instances = $job_config->getMaxInstances();
    $instance_name = $_SERVER['SCRIPT_NAME'];
    $instances_count = get_instances_num($instance_name);
    Log::write(INFO, "Instance Count: $instances_count");
    if($instances_count > $max_instances) {
        Log::write(INFO, "ERROR: there are more than $max_instances instances of $instance_name");
        die("ERROR: there are more than $max_instances instances of $instance_name\n");
    }
    Log::write(INFO, "job id: $job_id");

    // get the data
    $job19 = new Job($job_dbobj);
    $job19->findOne('id='.$job_id);
    $data = $job19->getData();
    Log::write(INFO, "data: ".json_encode($data));

    $store_id = $data['store_id'];
    $page_num = 0;
    $store_dbname = getStoreDBName($store_id);

    do {
        $page_num++;
        $ck = lck_store_active_products($store_dbname)->limit($page_num, PRODUCT_NUM_PER_PAGE);
        $product_rows = BaseMapper::getCachedObjects($ck);
        $products = $product_rows['data'];
        $products_cnt = $product_rows['total_rows'];

        foreach($products as $product) {
            Log::write(INFO, "product: ".$product['id']);
            GlobalProductsService::sync($store_id, $product['id'], $product);
        }

    } while($page_num*PRODUCT_NUM_PER_PAGE < $products_cnt);

    $job19->setStatus(PROCESSED);
    $job19->setHash1();
    $job19->save();

    if($run_times > $max_run_times) {
        Log::write(INFO, "reach the max run times $max_run_times, exit...");
        break;
    } else {
        $run_times++;
    }
}
