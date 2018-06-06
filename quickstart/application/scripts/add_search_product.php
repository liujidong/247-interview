<?php

//a cron job runs 3 times a day

if(isset($argv[1]) && $argv[1] === 'test') {
    $GLOBALS['test'] = 1;
}

require_once('includes.php');
$job_type = PRODUCT_SEARCH;
$max_run_times = 30;
$run_times = 0;

while($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {

    // get the config of the job
    $job_config = new JobConfig($job_dbobj);
    $job_config->findOne('type='.$job_type);
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
    $job13 = new Job($job_dbobj);
    $job13->findOne('id='.$job_id);
    $data = $job13->getData();
    Log::write(INFO, "data: ".json_encode($data));

    $store_id = $data['id'];
    $store_host = $data['host'];
    $store_status = $data['status'];
    
    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);
    if(!$store_dbobj->is_db_existed()) {
        $job13->setStatus(FAILED);
        $job13->setHash1();
        $job13->save();        
        continue;        
    }

    $store_profile = StoresMapper::getProfile($store_id, $account_dbobj);
    $store_profile['store_tags'] = get_store_tag_from_ids_tags($store_profile['ids_tags']);
    $product_ids = StoresMapper::getProductIds($store_dbobj, IGNORE_STATUS);

    foreach ($product_ids as $product_id) {
        
         // fill data into the search_product object  
        Log::write(INFO, "now index store : $store_id product : $product_id");
        $product_info = StoresMapper::getSearchProduct($product_id, $store_dbobj, IGNORE_STATUS);
        $search_product = new SearchProduct($account_dbobj);
        $search_product->findOne('store_id='.$store_id.' and product_id='.$product_id);
        $search_product_status = ($store_status==ACTIVATED&&$product_info['status']==CREATED)?CREATED:DELETED;
        $search_product->setStatus($search_product_status);
        $search_product->setProductId($product_id);
        $search_product->setProductStatus($product_info['status']);
        $search_product->setProductName($product_info['name']);
        $search_product->setProductDescription($product_info['description']);
        $search_product->setProductSize($product_info['size']);
        $search_product->setProductQuantity($product_info['quantity']);
        $search_product->setProductPrice($product_info['price']);
        $search_product->setProductShipping($product_info['shipping']);
        $search_product->setProductPinterestPinId($product_info['pinterest_pin_id']);
        $search_product->setExtRefId($product_info['ext_ref_id']);
        $search_product->setExtRefUrl($product_info['ext_ref_url']);
        $search_product->setProductBrand($product_info['brand']);
        $search_product->setProductMisc($product_info['misc']);
        $search_product->setProductStartDate($product_info['start_date']);
        $search_product->setProductEndDate($product_info['end_date']);
        $search_product->setProductCommission($product_info['commission']);        
        $search_product->setGlobalCategoryId($product_info['global_category_id']);
        $search_product->setCategory($product_info['category']);
        $search_product->setCategoryDescription($product_info['category_description']);
        $search_product->setPicIds($product_info['pic_ids']);
        $search_product->setPicTypes($product_info['pic_types']);
        $search_product->setPicSources($product_info['pic_sources']);
        $search_product->setPicUrls($product_info['pic_urls']);
        $search_product->setStoreId($store_profile['store_id']);
        $search_product->setStoreStatus($store_profile['store_status']);
        $search_product->setStoreSubdomain($store_profile['store_subdomain']);
        $search_product->setStoreName($store_profile['store_name']);
        $search_product->setStoreHost($store_profile['store_host']);
        $search_product->setStoreFeatured($store_profile['store_featured']);
        $search_product->setStoreLogo($store_profile['store_logo']);
        $search_product->setStoreTax($store_profile['store_tax']);
        $search_product->setStoreShipping($store_profile['store_shipping']);
        $search_product->setStoreAddtionalShipping($store_profile['store_additional_shipping']);
        $search_product->setStoreTags($store_profile['store_tags']);
        $search_product->setStoreOptinSalesnetwork($store_profile['store_optin_salesnetwork']);
        $search_product->save();
        
        // use picture_id to get all converted_picture
        $picture_ids = explode3(',', $product_info['pic_ids']);
        
        foreach ($picture_ids as $picture_id) {
            
            $converted_pictures = ConvertedPicturesMapper::get_converted_pictures($picture_id, $store_dbobj);
            if(empty($converted_pictures)) {
                Log::write(INFO, "store_id : $store_id picture_id : $picture_id has no converted pictures");
                SearchProductConvertedPicturesMapper::remove_useless_data($account_dbobj, $search_product->getId(), $picture_id);
                continue;
            }
            Log::write(INFO, "converted_pictures: ".json_encode($converted_pictures));
            
            $converted_picture_obj = new SearchProductConvertedPicture($account_dbobj);  
            $picture_obj = new Picture($store_dbobj, $picture_id);
            $search_product_id = $search_product->getId();
            
            $converted_picture_obj->findOne("id = $search_product_id and picture_id = $picture_id");
            if($converted_picture_obj->getId() === 0) {
                $converted_picture_obj->setSearchProductId($search_product_id);
                $converted_picture_obj->setPictureId($picture_id);
            }
            $converted_picture_obj->setPictureOrder($picture_obj->getOrderby());            
            $converted_picture_obj->setPictureStatus($picture_obj->getStatus());
            $converted_picture_obj->setPictureUrl($picture_obj->getUrl());
            
            foreach ($converted_pictures as $converted_picture) {
                
                $type = $converted_picture['type'];
                $url = $converted_picture['url'];
                $method = 'setConverted_'.$type;
                $converted_picture_obj->$method($url);        
            }
            $converted_picture_obj->save();
            Log::write(INFO, "SearchProductConvertedPicture updated id : {$converted_picture_obj->getId()}");
        }

    }
    $job13->setStatus(PROCESSED);
    $job13->setHash1();
    $job13->save();
    
    if($run_times > $max_run_times) {
        Log::write(INFO, "reach the max run times $max_run_times, exit...");
        break;
    } else {
        $run_times++;
    }
} 