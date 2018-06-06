<?php
ini_set("auto_detect_line_endings", true);
//Please set &2>1 >>log file in cron
//error_reporting(E_ERROR | E_PARSE);
require_once('includes.php');

$job_type = PRODUCT_CSV_IMPORTER;

while($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {

    Log::write(INFO, "job id: ".$job_id);
    $job_status = PROCESSED;

    // get the job data
    $job = new Job($job_dbobj);
    $job->findOne('id='.$job_id);
    $data = $job->getData();

    $store_id = $data['store_id'];
    $file_path = $data['csv_file_path'];

    $store = new Store($account_dbobj);
    $store->findOne('id='.$store_id);
    $store_dbobj = DbObj::getStoreDBObj($store->getHost(), $store_id);

    $service = new ProductService();
    $service->setMethod('get_product_from_csv');
    $service->setParams(array(
        'file_path' => $file_path
    ));
    $service->call();

    if($service->getStatus() === 0) {
        $products = $service->getResponse();
        // loop start
        $offset = 0; $step = 20; $status = 0;
        while($offset < count($products)){
            $save_slice = array_slice($products, $offset, $step);
            $offset += $step;
            $x = pcntl_fork();
            if($x == 0){
                $service = new StoreService();
                $service->setMethod('create_products');
                $service->setParams(array(
                    'products' => $save_slice,
                    'store_dbobj' => $store_dbobj
                ));
                $service->call();
                $response = $service->getResponse();
                /*
                foreach ($response as $product) {
                    if(empty($product['id'])) {
                        continue;
                    }

                    $product_id = $product['id'];

                    $db_name = $store_dbobj->getDBName();
                    $product_ck = CacheKey::q($db_name.".product?id=".$product_id);
                    $old_data = DAL::get($product_ck);

                    $service = new ProductPhotosService();
                    $service->setMethod('create_product_photo');
                    $service->setParams(array(
                        'store_id' => $store_id,
                        'product_id' => $product_id,
                        'store_dbobj' => $store_dbobj
                    ));
                    $service->call();

                    DAL::s($product_ck, $old_data);
                }
                */
                posix_kill(getmypid(),9);
            } else if($x > 0){
                pcntl_wait($status);
            }else {
                ddd('error');
            }
        }
        // loop done
        $job_status = PROCESSING;
    } else {
        $job_status = FAILED;
        $job->setStatus($job_status);
        $job->save();
        break;
    }

    // send an email to notify the user the product import has been done
    $merchant_info = StoresMapper::getMerchantInfo($store_id, $account_dbobj);
    global $shopinterest_config;
    $email_service = new EmailService();
    $email_service->setMethod('create_job');
    $email_service->setParams(array(
        'to' => $merchant_info['merchant_username'],
        'from' => $shopinterest_config->support->email,
        'type' => MERCHANT_PRODUCTS_IMPORTED,
        'data' => array(
            'site_url' => getSiteMerchantUrl(),
            'link' => getStoreUrl($store->getSubdomain())
        ),
        'job_dbobj' => $job_dbobj
    ));
    $email_service->call();

    $job_status = PROCESSED;
    $job->setStatus($job_status);
    $job->save();
}

Log::write(INFO, 'no more type 12 job');
