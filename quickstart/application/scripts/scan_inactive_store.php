<?php
require_once('includes.php');

$store_ids = StoresMapper::getAllStoreIds($account_dbobj);

foreach ($store_ids as $store_id) {
    if(APPLICATION_ENV != 'production' && $store_id > 30){
        break;
    }

    $store = BaseModel::findCachedOne($dbconfig->account->name . ".store?id=" . $store_id);
    $user = BaseModel::findCachedOne($dbconfig->account->name . ".user?id=" . $store['user_id']);
    if($store['status'] != MARK_TODEL && $user['last_activity'] < getNdaysago(60)){
        $store_obj = new Store($account_dbobj, $store_id);
        $store_obj->setStatus(MARK_TODEL);
        $store_obj->save();
        // send alert email
        $to_email = $user['username'];
        if(APPLICATION_ENV != 'production'){
            $to_email = $shopinterest_config->support->email;
        }
        $service = new EmailService();
        $service->setMethod('create_job');
        $service->setParams(array(
            'to' => $to_email,
            'from' => $shopinterest_config->support->email,
            'type' => MERCHANT_STORE_DELETE_ALERT,
            'data' => array(
                'site_url' => getURL(),
                'store_name' => $store['name'],
                'store_subdomain' => $store['subdomain'],
                'first_name' => _u($user['first_name']),
            ),
            'job_dbobj' => $job_dbobj,
        ));
        $service->call();
    } else if($store['status'] == MARK_TODEL && $user['last_activity'] < getNdaysago(70)){
        // create job?
        // StoresMapper::forceDeleteStore($account_dbobj, $store_id);
        $data = array(
            'store_id'=> $store_id, 
        );
        $job = new Job($job_dbobj);
        $job->setType(DELETE_INACTIVE_STORE);
        $job->setData($data);
        $job->save();            
    } else if($store['status'] == MARK_TODEL && $user['last_activity'] > getNdaysago(60)){
        $store_obj = new Store($account_dbobj, $store_id);
        $store_obj->setStatus(ACTIVATED);
        $store_obj->save();
    }
}
