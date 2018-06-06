<?php

// job type: PIN_STORE_PRODUCTS (7)
// data: pinterest_email, pinterest_password, pinterest_account_id, pinterest_board_id, store_id, scheduled_job_id 

// a cron job
// while get next job till no such job avail and then exit

if(isset($argv[1]) && $argv[1] === 'test') {
    $GLOBALS['test'] = 1;
}

require_once('includes.php');

$job_type = PIN_STORE_PRODUCTS;

global $pinterest_config, $cond_active_product;

while($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {
    // get the config of the job
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
    Log::write(INFO, "job id: $job_id");

    // get the data
    $job7 = new Job($job_dbobj);
    $job7->findOne('id='.$job_id);
    $data = $job7->getData();
    Log::write(INFO, "data: ".json_encode($data));

    // initialize the job status
    $job7_status = PROCESSED;
    
    $pinterest_email = $data['pinterest_email'];
    $pinterest_password = decrypt($data['pinterest_password']);
    $pinterest_account_id = $data['pinterest_account_id'];
    $external_pinterest_board_id = $data['pinterest_board_id'];
    $scheduled_job_id = $data['scheduled_job_id'];
    $store_id = $data['store_id'];
    $store = new Store($account_dbobj);
    $store->findOne('id='.$store_id);
    $store_subdomain = $store->getSubdomain();
    $store_dbobj = DBObj::getStoreDBObj($store->getHost(), $store_id);

    $product_ids = StoresMapper::getProductIds($store_dbobj, IGNORE_STATUS);    
    $browser = new PinterestBrowser($pinterest_email, $pinterest_password);
    
    $pinterest_board = new PinterestBoard($account_dbobj);
    $pinterest_board->findOne('external_id='."'".$external_pinterest_board_id."'");
    $pinterest_board_id = $pinterest_board->getId();
    Log::write(INFO, "external_pinterest_board_id: $external_pinterest_board_id");
    $uploadpin_interval = 1;

    foreach($product_ids as $i=>$product_id) {
        $product_ck = CacheKey::q($store_dbobj->getDBName().".product?id=".$product_id);
        $product = BaseModel::findCachedOne($product_ck);

        Log::write(INFO, 'product: '.json_encode($product));
        
        $pinned_product = new PinnedProduct($store_dbobj);
        $pinned_product->findOne("product_id=$product_id and 
            pinterest_account_id=$pinterest_account_id and pinterest_board_id=$pinterest_board_id");
        
        $postfields = array();        
        // active product
        if($cond_active_product->test($product)) {

            if($pinned_product->getId() === 0 ||
            ($pinned_product->getId() !== 0 &&
            $pinned_product->getStatus() != DELETED &&
            !checkRemoteFile('http://pinterest.com/pin/'.$pinned_product->getExternalPinterestPinId().'/'))) {
                
                $postfields = array(
                    'board' => $external_pinterest_board_id,
                    'details' => $product['name'],
                    'link' => getStoreUrl($store_subdomain).'/products/item?id='.$product_id,
                    'img_url' => reset($product['pictures']['736']),
                    'tags' => $product['tags'],
                    'buyable' => '$'.$product['price']
                );
                $external_pinterest_pin_id = $browser->upload_pin($postfields);
                if(!$external_pinterest_pin_id) {
                    $job7_status = PARTIALLY_FAILED;
                    Log::write(WARN, "failed on uploading the pin ".json_encode($postfields));
                } else {
                    Log::write(INFO, "succeeded on uploading the pin ".json_encode($postfields));
                    $pinned_product->setProductId($product_id);
                    $pinned_product->setPinterestAccountId($pinterest_account_id);
                    $pinned_product->setPinterestBoardId($pinterest_board_id);
                    $pinned_product->setExternalPinterestPinId($external_pinterest_pin_id);
                    $pinned_product->save();
                    Log::write(INFO, 'saved pinned product '.$pinned_product->getId());
                }
            } else if($pinned_product->getId() !== 0 &&
            $pinned_product->getStatus() != DELETED &&
            checkRemoteFile('http://pinterest.com/pin/'.$pinned_product->getExternalPinterestPinId().'/')) {
                
                $postfields = array(
                    'board' => $external_pinterest_board_id,
                    'details' => $product['name'],
                    'link' => getStoreUrl($store_subdomain).'/products/item?id='.$product_id,
                    'img_url' => reset($product['pictures']['736']),
                    'tags' => $product['tags'],
                    'buyable' => '$'.$product['price'],
                    'external_pinterest_pin_id' => $pinned_product->getExternalPinterestPinId()
                );
                $return = $browser->edit_pin($postfields);
                if(!$return) {
                    $job7_status = PARTIALLY_FAILED;
                    Log::write(WARN, "failed on editing the pin ".json_encode($postfields));
                } else {
                    Log::write(INFO, "succeeded on editing the pin ".json_encode($postfields));
                }
            }

            if($i >= $pinterest_config->api->uploadpin->max_num_pins) {
                Log::write(INFO, "Number of products exceeds the max allowed ".$pinterest_config->api->uploadpin->max_num_pins);
                break;
            }

        } else if($pinned_product->getId() !== 0 && $pinned_product->getStatus() != DELETED) {
            // delete the pin
            $postfields = array('external_pinterest_pin_id' => $pinned_product->getExternalPinterestPinId());
            $return = $browser->delete_pin($postfields);
                
            if(!$return) {
                $job7_status = PARTIALLY_FAILED;
                Log::write(INFO, 'failed on deleting the pin '.json_encode($postfields));
            } else {
                Log::write(INFO, 'suceeded on deleting the pin '.json_encode($postfields));
            }
            $pinned_product->setStatus(DELETED);
            $pinned_product->save();
        }

        sleep($uploadpin_interval);
    }
    
    // update status
    $job7->setStatus($job7_status);
    $job7->save();
    
    // update the status of the scheduled job
    $scheduled_job = new ScheduledJob($store_dbobj);
    $scheduled_job->findOne('id='.$scheduled_job_id);
    if($scheduled_job->getId() !== 0) {
        $scheduled_job->setStatus($job7_status);
        $scheduled_job->save();
    }
    
    // send an email to notify the merchant 
    $merchant_info = StoresMapper::getMerchantInfo($store_id, $account_dbobj);
    global $shopinterest_config;
    $email_service = new EmailService();
    $email_service->setMethod('create_job');
    $email_service->setParams(array(
        'to' => $merchant_info['merchant_username'],
        'from' => $shopinterest_config->support->email,
        'type' => MERCHANT_PRODUCTS_PINNED,
        'data' => array(
            'site_url' => 'http://'.getSiteDomain(),
            'link' => 'http://pinterest.com'.$pinterest_board->getUrl()
        ),
        'job_dbobj' => $job_dbobj
    ));
    $email_service->call();
}

Log::write(INFO, "no more type $job_type job available");
