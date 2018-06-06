<?php

// job type: ACCOUNT_SCRAPER
// data: pinterest_account_id

require_once('includes.php');

while(1) {
    // get the config of the job
    $job_type = ACCOUNT_SCRAPER;
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
        $job5 = new Job($job_dbobj);
        $job5->findOne('id='.$job_id);
        $data = $job5->getData();
        Log::write(INFO, "data: ".json_encode($data));
        $data['account_dbobj'] = $account_dbobj;
        $data['job_dbobj'] = $job_dbobj;
        $data['force'] = true;
        
        // import board pins
        $service = PinterestService::getInstance();
        $service->setMethod('import_account_boards');
        $service->setParams($data);
        $service->call();
        Log::write(INFO, "Called PinterestService imported account boards");
        
        // update status
        if($service->getStatus() === 0) {
            $job5->setStatus(PROCESSED);
            $job5->save();
            Log::write(INFO, "DONE: type $job_type job $job_id marked as processed");
        } else {
            $job5->setStatus(FAILED);
            $job5->save();
            Log::write(INFO, "ERROR: type $job_type job $job_id marked as failed");
        }
        
    }
    
    $sleep = $job_config->getSleep();
    sleep($sleep);
    Log::write(INFO, "Wakeup after $sleep seconds");
}










