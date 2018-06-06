<?php

require_once('includes.php');

if($argc > 3) {
    die("ERROR: php ".$_SERVER['SCRIPT_NAME'].".php [min store id] [max store id]\n");
}

switch ($argc) {
    case 1:
        $store_infos = StoresMapper::getAllStoreInfo($account_dbobj);
        break;
    case 2:
        $store_infos = StoresMapper::getAllStoreInfo($account_dbobj, $argv[1]);
        break;
    case 3:
        $store_infos = StoresMapper::getAllStoreInfo($account_dbobj, $argv[1], $argv[2]);
        break;
}

foreach ($store_infos as $store_info) {

    // input store_id store_host
    $store_id = $store_info['id'];
    $store_host = $store_info['host'];
    
    echo "start converting pictures for store id: $store_id and store_host is: $store_host \n";

    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);
    
    if(!$store_dbobj->is_db_existed()) {
        continue;
    }
    
    $products_ids = ProductsMapper::getAllProductIds($store_dbobj);
    
    foreach ($products_ids as $product_id) {
        
        echo "store id : $store_id, product id : $product_id \n";
        
        $product_pictures = ProductsMapper::getProductPictures($product_id, $store_dbobj);
        
        foreach ($product_pictures as $product_picture) {
            
            $picture_id = $product_picture['picture_id'];   
            $picture_url = $product_picture['picture_url'];
         
            $converted_pictures = ConvertedPicturesMapper::get_converted_pictures($picture_id, $store_dbobj);

            // status 0 -- needent convert, 1 -- need to convert
            $status = 0;
            
            foreach($converted_pictures as $converted_picture) {
                if(!checkRemoteFileIsImage($converted_picture['url'])) {
                    echo "converted pictures id : {$converted_picture['id']}, url : {$converted_picture['url']} is invalid \n";
                    $status = 1;
                    break;
                }
            }
            if(empty($converted_pictures)) {
                echo "pictures id : $picture_id is havent been converted before \n";
                $status = 1;
            }
            
            if($status === 1) {
                // convert it, update/store the converted ones
                $converted_images = convertImage($picture_url);
                uploadConvertedImageToS3(&$converted_images, $store_id);
                echo "upload converted pictures to s3:".json_encode($converted_images)."\n";
                
                foreach($converted_images as $converted_image) {
                    
                    $image_url = $converted_image['converted_image_url'];
                    $image_type = $converted_image['converted_image_type'];
                    
                    $converted_picture_obj = new ConvertedPicture($store_dbobj);
                    $converted_picture_obj->findOne("picture_id = $picture_id and type = $image_type");
                    if($converted_picture_obj->getId() === 0) {
                        $converted_picture_obj->setPictureId($picture_id);
                        $converted_picture_obj->setType($image_type);
                    }
                    $converted_picture_obj->setUrl($image_url);
                    $converted_picture_obj->save();
                    echo "saved converted pictures url, picture_id : $picture_id type : $image_type url : $image_url\n";                    
                }                
            }
        }
    }    
    
    $arg2 = isset($argv[2]) ? $argv[2] : $argv[1];
    echo "finish sync store start: {$argv[1]} ends : $arg2 \n";
}