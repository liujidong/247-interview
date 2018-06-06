<?php

require_once('includes.php');

while(1) {
    // get the config of the job
    $job_type = PICTURE_CONVERT;
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
    
    if($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {

        Log::write(INFO, "job id: $job_id");
        
        // get the data
        $job15 = new Job($job_dbobj);
        $job15->findOne('id='.$job_id);
        $data = $job15->getData();
        $store_id = $data['store_id'];
        $product_id = $data['product_id'];
        $refresh = isset($data['refresh'])?TRUE:FALSE;
        
        Log::write(INFO, "data: ".json_encode($data));
        
        $store = new Store($account_dbobj);
        $store->findOne('id='.$store_id);
        $store_dbobj = DBObj::getStoreDBObj($store->getHost(), $store_id);        
        
        $service = new ProductPhotosService();
        $service->setMethod('create_product_photo');
        $service->setParams(array(
            'store_id' => $store_id,
            'product_id' => $product_id,
            'refresh' => $refresh,
            'store_dbobj' => $store_dbobj
        ));
        $service->call();   
        
        // update status
        if($service->getStatus() === 0) {
            $job15_status = PROCESSED;
            Log::write(INFO, "DONE: type $job_type job $job_id marked as processed");
        } else {
            $job15_status = FAILED;
            Log::write(INFO, "ERROR: type $job_type job $job_id marked as failed");
        }        
        $job15->setStatus($job15_status);
        $job15->save();           
    }
    
    $sleep = $job_config->getSleep();
    sleep($sleep);
    Log::write(INFO, "Wakeup after $sleep seconds");
}