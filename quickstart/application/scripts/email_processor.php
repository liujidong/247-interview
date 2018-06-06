<?php

// job type: 4
// data: to, toname(optional), subject, text, from, fromname(optional)

require_once('includes.php');

while(1) {
    // get the config of the job
    $job_type = EMAIL_SENDER;
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
        $job4 = new Job($job_dbobj);
        $job4->findOne('id='.$job_id);
        $data = $job4->getData();
        Log::write(INFO, "data: ".json_encode($data));
        
        $service = EmailService::getInstance();
        $service->setMethod('send');
        $service->setParams($data);
        $service->call();
        
        if($service->getStatus() === 0) {
            // update status
            $job4->setStatus(PROCESSED);
            $job4->save();
            Log::write(INFO, "DONE: type $job_type job $job_id marked as success");
        } else {
            $job4->setStatus(FAILED);
            $job4->save();
            Log::write(ERROR, "ERROR: type $job_type job $job_id marked as failure");
        }
        
    }
    
    $sleep = $job_config->getSleep();
    sleep($sleep);
    Log::write(INFO, "Wakup after $sleep seconds");
}




