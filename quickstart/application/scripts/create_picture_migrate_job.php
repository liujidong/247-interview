<?php

require_once('includes.php');

$start_store_id = 0;
$end_store_id = PHP_INT_MAX;

if($argc === 2) {
    $start_store_id = intval($argv[1]);
} else if($argc === 3) {
    $start_store_id = intval($argv[1]);
    $end_store_id = intval($argv[2]);
}


// filter active stores or stores created after 2014-03-00
$ck = CacheKey::c("status=".ACTIVATED."|created>2014-03-00");
$ck->_and("id>=" . $start_store_id .  "&id<=" . $end_store_id);

$cond_sql = $ck->conditionSQL();
$sql = "select id from stores where $cond_sql";

if($res = $account_dbobj->query($sql, $account_dbobj)) {
    while($store = $account_dbobj->fetch_assoc($res)) {
        $job_type = PICTURE_MIGRATION;
        $data = array('store_id'=>$store['id']);
        $job = new Job($job_dbobj);
        $job->setType($job_type);
        $job->setData($data);
        $job->save();
    }
}
