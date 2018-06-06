<?php

require_once('includes.php');

$count = 0;
    

        
// get the config of the job
$job_type = UPLOAD_PRODUCT_PICTURES;
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
    
while($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {
    echo "**start job id: $job_id\n";

    // get the data
    $job14 = new Job($job_dbobj);
    $job14->findOne('id='.$job_id);
    $data = $job14->getData();

    $store_id = $data['store_id'];
    $product_id = $data['product_id'];
    echo "the store id: $store_id product id: $product_id\n";

    $store_obj = new Store($account_dbobj, $store_id);
    $store_host = $store_obj->getHost();        

    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);

    $product_pictures = ProductsMapper::getProductPictures($product_id, $store_dbobj);

    echo "pictures count:".count($product_pictures)."\n";

    foreach ($product_pictures as $product_picture) {
        $pinterest_pin_id = $product_picture['pinterest_pin_id'];
        $picture_id = $product_picture['picture_id'];
        $picture_url = $product_picture['picture_url'];
        $picture_source = $product_picture['picture_source'];

        $picture_obj = new Picture($store_dbobj, $picture_id);
        $original_picture_url = $picture_obj->getUrl();

        echo "****current stored picture url $original_picture_url\n";

        if(stored_in_s3_stores($original_picture_url)) {
            echo "picture already stored in s3, skip this\n";      
            continue;
        }

        echo "get the original picture url for product id: $product_id and picture id is: $picture_id \n";                

        if($picture_source === 'pinterest') { 
            // upload pinterest image url 
            $pin_image = new PinImage($account_dbobj);
            $pin_image->findOne("pinterest_pin_id = $pinterest_pin_id");
            $image_736_url = $pin_image->getImage_736();
            if(empty($image_736_url)) {
                $pin = new PinterestPin($account_dbobj);
                $pin->findOne('id='.$pinterest_pin_id);
                $pin_external_id = $pin->getExternalId();
                $pinterest_pin_page = new PinterestPinPage($pin_external_id);
                $pin_info = $pinterest_pin_page->getPinInfo();  
                $image_736_url = !empty($pin_info['image_736']) ? $pin_info['image_736'] : '';

                if(empty($image_736_url)) {
                    $s3_image = $amazonconfig->api->s3->url.get_pinterest_upload_dst('pins', $pinterest_pin_id, 'pin', 'mobile');
                    $image_736_url = checkRemoteFile($s3_image) ? $s3_image : '';
                    echo "find the original image on s3 $image_736_url\n";
                } else {
                    echo "find the original image on pinterest page $image_736_url\n";
                }

                if(empty($image_736_url) && checkRemoteFile($original_picture_url)) {
                    $image_736_url = $original_picture_url;
                    echo "find the original image by its url $image_736_url\n";
                }
            } else {
                echo "find the original image in pin_images $image_736_url\n";
            }              
        } else {
            // etsy and single product imported image     
            $image_736_url = checkRemoteFile($picture_url) ? $picture_url : '';            
        }

        if(empty($image_736_url)) {
            echo "cant find the picture url, ignore and continue...\n";
            continue;
        }

        $picture_obj->setUrl($image_736_url);
        $picture_obj->save(); 
        echo "save the picture url: $image_736_url\n";

        $service = new ProductPhotosService();
        $service->setMethod('upload_original_photo');
        $service->setParams(array(
            'store_id' => $store_id,
            'picture_id' => $picture_id,
            'store_dbobj' => $store_dbobj
        ));
        $service->call();
        echo "product original pictures uploaded to {$service->getResponse()}"."\n";                              

    }   

    echo "**finish the job $job_id for store id: $store_id product id: $product_id\n\n";

    $job14->setStatus(PROCESSED);
    $job14->save();
    
    $count++;   
    
    if($count >= 1000) {
        echo "daemon has processed 1000 jobs, exit now\n";        
        break;
    } else {
        echo "daemon has processed $count \n";        
    }
    
}  
    
    
    
    
