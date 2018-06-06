<?php

require 'includes.php';

if(isset($argv[1]) && $argv[1] === 'test') {
    $GLOBALS['test'] = 1;
}

while(1) {

    $job_type = UPDATE_SEARCH_PRODUCTS;
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

    if($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {

        Log::write(INFO, "job id: $job_id");
        // get the data
        $job18 = new Job($job_dbobj);
        $job18->findOne('id='.$job_id);
        $data = $job18->getData();
        Log::write(INFO, "data: ".json_encode($data));

        $store_id = $data['store_id'];
        $product_id = $data['product_id'];
        $store_obj = new Store($account_dbobj, $store_id);
        $store_dbobj = DBObj::getStoreDBObj($store_obj->getHost(), $store_obj->getId());

        // fill data into the search_product object  
        Log::write(INFO, "now index store : $store_id product : $product_id");
        $product_info = StoresMapper::getSearchProduct($product_id, $store_dbobj, IGNORE_STATUS);
    
        $search_product = new SearchProduct($account_dbobj);
        $search_product->findOne('store_id='.$store_id.' and product_id='.$product_id);
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
        $search_product->setStoreId($store_id);
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
    
        $job18->setStatus(PROCESSED);
        $job18->save();
    }

    $sleep = $job_config->getSleep();
    sleep($sleep);
    Log::write(INFO, "Wakeup after $sleep seconds");
}