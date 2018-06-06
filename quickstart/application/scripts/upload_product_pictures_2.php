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
    
    echo "start upload pictures for store id: $store_id and store_host is: $store_host \n";

    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);
    
    if(!$store_dbobj->is_db_existed()) {
        continue;
    }
    
    $products_ids = ProductsMapper::getAllProductIds($store_dbobj);
    
    foreach ($products_ids as $product_id) {
        
        echo "store id : $store_id, product id : $product_id \n";
        
        $product_pictures = ProductsMapper::getProductPictures($product_id, $store_dbobj);
        
        echo "product pictures:".json_encode($product_pictures)."\n";
        foreach ($product_pictures as $product_picture) {
            
            $pinterest_pin_id = $product_picture['pinterest_pin_id'];
            $picture_url = $product_picture['picture_url'];
            $original_url = $product_picture['original_url'];
            $picture_id = $product_picture['picture_id'];
            
            $picture_obj = new Picture($store_dbobj, $picture_id);   
            
            // try to get valid picture_url 
            if(!stored_in_s3_stores($picture_url) || !checkRemoteFileIsImage($picture_url)) {
                echo "picture: $picture_id url is invalidate \n";
                
                $pin_image = new PinImage($account_dbobj);
                $pin_image->findOne("pinterest_pin_id = $pinterest_pin_id");
                $pinterest_image_url = $pin_image->getImage_736();

                if(checkRemoteFileIsImage($pinterest_image_url)) {
                    $picture_url = $pinterest_image_url;
                    echo "get valid url from pin_images table: $picture_url \n";
                } else {
                    $pin = new PinterestPin($account_dbobj);
                    $pin->findOne('id='.$pinterest_pin_id);
                    $pin_external_id = $pin->getExternalId();
                    $mobile_img = $pin->getImagesMobile();

                    $pinterest_pin_page = new PinterestPinPage($pin_external_id);
                    $pin_info = $pinterest_pin_page->getPinInfo();  
                    $pinterest_image_url = !empty($pin_info['image_736']) ? $pin_info['image_736'] : '';       

                    if(checkRemoteFileIsImage($pinterest_image_url)) {
                        $picture_url = $pinterest_image_url;
                        echo "get valid url from pinterest page: $picture_url \n";
                    } else {
                        if(checkRemoteFileIsImage($mobile_img)) {
                            $picture_url = $mobile_img;
                            echo "get valid url from pinterest_pin table: $picture_url \n";
                        } else {                            
                            $s3_url = $amazonconfig->api->s3->url.get_pinterest_upload_dst('pins', $pinterest_pin_id, 'pin', 'mobile'); 
                            if(checkRemoteFileIsImage($s3_url)) {
                                $picture_url = $s3_url;
                                echo "get valid url from s3: $picture_url \n";
                            } else {
                                $picture_url = checkRemoteFileIsImage($original_url) ? $original_url : '';
                            }
                        }    
                    }
                }
                
                echo "save valid url: $picture_url \n";
                $picture_obj->setUrl($picture_url);
                $picture_obj->save();

                if(empty($picture_url)) {
                    echo "picture $picture_id has no valid url \n";
                    continue;
                }

                // upload image to s3 and update url field
                $dst = get_product_image_upload_dst($store_id, uniqid(), ORIGINAL);
                if($stored_url = upload_image($dst, $picture_obj->getUrl())) {
                    echo "upload image to s3, returnd url: $stored_url \n";    
                    $picture_obj->setUrl($stored_url);
                    $picture_obj->setPicUploadTime(get_current_datetime());
                    $picture_obj->save();             
                }                  
            }             
        }
    }    
}