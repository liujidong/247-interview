<?php

require_once('includes.php');

if(isset($argv[1]) && $argv[1] === 'test') {
    $GLOBALS['test'] = 1;
}

while(1) {
    // get the config of the job
    $job_type = UPLOAD_CONVERTED_PICTURES;
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
        $job16 = new Job($job_dbobj);
        $job16->findOne('id='.$job_id);
        $data = $job16->getData();
        $store_id = $data['store_id'];
        $product_id = $data['product_id'];
        
        Log::write(INFO, "data: ".json_encode($data));
        
        $store = new Store($account_dbobj);
        $store->findOne('id='.$store_id);
        $store_dbobj = DBObj::getStoreDBObj($store->getHost(), $store_id);   
        
        $picture_array = ProductsMapper::getPictures($product_id, $store_dbobj);

        foreach ($picture_array as $picture) {
            
            // picture_id
            Log::write(INFO, " now processing picture id : {$picture['picture_id']}");  
            $images = $picture['pictures'];

            // upload picture to s3 and update each record
            foreach ($images as $image) {
                
                uploadImageToS3IfNotExist($image, $store_id);
                Log::write(INFO, "image: ".json_encode($image));
                
                $picture_id = $image['id'];
                $picture_type = $image['type'];
                $picture_url = $image['url'];
                $picture_uploaded = isset($image['uploaded']) ? $image['uploaded'] : false;
                
                if($picture_uploaded === false) {
                    Log::write(INFO, "picture id : $picture_id, picture type : $picture_type failed to be uploaded");    
                    continue;
                }
                
                if($image['type'] === 'original') {
                    $picture_obj = new Picture($store_dbobj, $picture_id);
                    if($picture_obj->getId() !== 0 ) {
                        $picture_obj->setType($picture_type);
                        $picture_obj->setUrl($picture_url);
                        $picture_obj->setPicUploadTime(get_current_datetime());
                        $picture_obj->save();
                        Log::write(INFO, "picture id : $picture_id, picture type : $picture_type uploaded");                          
                    }

                } else {
                    $converted_picture_obj = new ConvertedPicture($store_dbobj, $picture_id);
                    if($converted_picture_obj->getId() !== 0 ) {
                        $converted_picture_obj->setType($picture_type);
                        $converted_picture_obj->setUrl($picture_url);
                        $converted_picture_obj->save();    
                        Log::write(INFO, "picture id : $picture_id, picture type : $picture_type uploaded");                          
                    }                  
                }
            }
        }  
        
        $job16->setStatus(PROCESSED);
        $job16->save();

        // refresh cache 
        DAL::delete(CacheKey::q($store_dbobj->getDBName(). '.product?id='. $product_id));
    }
    
    $sleep = $job_config->getSleep();
    sleep($sleep);
    Log::write(INFO, "Wakeup after $sleep seconds");    
}