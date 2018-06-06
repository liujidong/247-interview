<?php

require_once('includes.php');
$count = 0;


        
// get the config of the job
$job_type = CONVERT_PICTURES;
$job_config = new JobConfig($job_dbobj);
$job_config->findOne('type='.$job_type);
$max_instances = $job_config->getMaxInstances();
$instance_name = $_SERVER['SCRIPT_NAME'];
$instances_count = get_instances_num($instance_name);
echo "Instance Count: $instances_count\n";
if($instances_count > $max_instances) {
    Log::write(INFO, "ERROR: there are more than $max_instances instances of $instance_name");
    die("ERROR: there are more than $max_instances instances of $instance_name\n");
}

while($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {
    echo "**start job id: $job_id\n";

    // get the data
    $job17 = new Job($job_dbobj);
    $job17->findOne('id='.$job_id);
    $data = $job17->getData();

    $store_id = $data['store_id'];
    $product_id = $data['product_id'];
    echo "store id: $store_id product id: $product_id\n";

    $store_obj = new Store($account_dbobj, $store_id);
    $store_host = $store_obj->getHost();       

    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);

    $service = new ProductPhotosService();
    $service->setMethod('create_product_photo');
    $service->setParams(array(
        'store_id' => $store_id,
        'product_id' => $product_id,
        'store_dbobj' => $store_dbobj,
        'account_dbobj' => $account_dbobj,
        'skip_upload_original_photo' => true
    ));
    $service->call();
    echo "product : $product_id pictures converted\n";

    $job17->setStatus(PROCESSED);
    $job17->save();
    echo "converted picture urls:".json_encode($service->getResponse())."\n";
    echo "**finish job id $job_id\n\n";

    $count++;   
    if($count >= 1000) {
        echo "daemon has processed 1000 jobs, exit\n";        
        break;
    } else {
        echo "has processed $count \n";        
    }
}
