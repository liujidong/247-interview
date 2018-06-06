<?php

require_once('includes.php');

while(1) {
    // get the config of the job
    $job_type = BOARD_SCRAPER;
    $job_config = new JobConfig($job_dbobj);
    $job_config->findOne('type='.$job_type);
    $max_instances = $job_config->getMaxInstances();
    $instance_name = $_SERVER['SCRIPT_NAME'];
    $instances_count = get_instances_num($instance_name);
    Log::write(INFO, "Instance Count: $instances_count");
    if($instances_count > $max_instances) {
        Log::write(ERROR, "ERROR: there are more than $max_instances instances of $instance_name");
        die("ERROR: there are more than $max_instances instances of $instance_name\n");
    }
    
    if($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {
        Log::write(INFO, "job id: $job_id");
        
        // get the data
        $job2 = new Job($job_dbobj);
        $job2->findOne('id='.$job_id);
        $data = $job2->getData();
        Log::write(INFO, "data: ".json_encode($data));
        $data['account_dbobj'] = $account_dbobj;
        $data['job_dbobj'] = $job_dbobj;
        
        // import board pins
        $service = PinterestService::getInstance();
        $service->setMethod('import_board_pins');
        $service->setParams($data);
        $service->call();
        Log::write(INFO, "Called PinterestService imported board pins");
        
        // update status
        $job2->setStatus(PROCESSED);
        $job2->save();
        Log::write(INFO, "DONE: type $job_type job $job_id marked as success");
    }
    
    $sleep = $job_config->getSleep();
    sleep($sleep);
    Log::write(INFO, "Wakup after $sleep seconds");
}










