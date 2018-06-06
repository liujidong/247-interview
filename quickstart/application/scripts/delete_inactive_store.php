<?php

if(isset($argv[1]) && $argv[1] === 'test') {
    $GLOBALS['test'] = 1;
}

require_once('includes.php');
$job_type = DELETE_INACTIVE_STORE;
$max_run_times = 100;
$run_times = 0;

while($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {

    // get the config of the job
    $job_config = new JobConfig($job_dbobj);
    $job_config->findOne('type=18');
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
    $store_dbname = getStoreDBName($store_id);

    //=================
    $store_ck = $dbconfig->account->name . ".store?id=" . $store_id;
    $store = BaseModel::findCachedOne($store_ck);
    if(empty($store)) continue;
    $user = BaseModel::findCachedOne($dbconfig->account->name . ".user?id=" . $store['user_id']);
    if($store['status'] == MARK_TODEL && $user['last_activity'] < getNdaysago(70)){
        if($store['updated'] > getNdaysago(10)){ // updated in last 10 days
            continue;
        }
        echo "delete store ", $store['id'], ".   ";
        StoresMapper::forceDeleteStore($account_dbobj, $store_id);
        echo " ... done\n";
    } else if($store['status'] == MARK_TODEL && $user['last_activity'] > getNdaysago(60)){
        $store_obj = new Store($account_dbobj, $store_id);
        $store_obj->setStatus(ACTIVATED);
        $store_obj->save();
    }
}
