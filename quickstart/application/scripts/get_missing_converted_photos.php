<?php

require_once('includes.php');

$store_infos = StoresMapper::getAllStoreInfo($account_dbobj);

foreach ($store_infos as $store_info) {
    
    $store_id = $store_info['id'];
    $store_host = $store_info['host'];
    //$store_status = $store_info['status'];
    echo "processing store : $store_id \n";
    
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
            
            $picture_url = checkRemoteFileIsImage($picture_url) ? $picture_url : $original_url;

            if(!checkRemoteFileIsImage($picture_url)) {
                echo "id: $picture_id url: $picture_url **doesnt exist**\n";
                $pin_image = new PinImage($account_dbobj);
                $pin_image->findOne("pinterest_pin_id = $pinterest_pin_id");
                $pinterest_image_url = $pin_image->getImage_736();
                if(!checkRemoteFileIsImage($pinterest_image_url)) {
                    $pin = new PinterestPin($account_dbobj);
                    $pin->findOne('id='.$pinterest_pin_id);
                    $pin_external_id = $pin->getExternalId();
                    $mobile_img = $pin->getImagesMobile();
                    
                    if(checkRemoteFileIsImage($mobile_img)) {
                        $pinterest_image_url = $mobile_img;
                    } else {
                        $pinterest_pin_page = new PinterestPinPage($pin_external_id);
                        $pin_info = $pinterest_pin_page->getPinInfo();  
                        $pinterest_image_url = !empty($pin_info['image_736']) ? $pin_info['image_736'] : '';                        
                    }

                }
                
                if(checkRemoteFileIsImage($pinterest_image_url)) {
                    $picture_url = $pinterest_image_url;
                } else {
                    $s3_url = $amazonconfig->api->s3->url.get_pinterest_upload_dst('pins', $pinterest_pin_id, 'pin', 'mobile'); 
                    if(checkRemoteFileIsImage($s3_url)) {
                        $picture_url = $s3_url;
                    }                     
                }
                
                if(!checkRemoteFileIsImage($picture_url)) {
                    $picture_url = '';
                }                 
            }  

            $converted_pictures = ConvertedPicturesMapper::get_converted_pictures($picture_id, $store_dbobj);
            
            $missing_converted_photo = new MissingConvertedPhoto($account_dbobj);
            $missing_converted_photo->setStoreId($store_id);
            $missing_converted_photo->setProductId($product_id);
            $missing_converted_photo->setPictureId($picture_id);
            $missing_converted_photo->setPictureUrl($picture_url);
            $status = 2;
            foreach($converted_pictures as $converted_picture) {
                if($converted_picture['type'] == CONVERTED45) {
                    $missing_converted_photo->setConverted_45($converted_picture['url']);
                    if(!checkRemoteFileIsImage($converted_picture['url'])) {
                        $status = 1;
                    }
                    continue;
                }
                if($converted_picture['type'] == CONVERTED70) {
                    $missing_converted_photo->setConverted_70($converted_picture['url']);
                    if(!checkRemoteFileIsImage($converted_picture['url'])) {
                        $status = 1;
                    }
                    continue;
                }
                if($converted_picture['type'] == CONVERTED192) {
                    $missing_converted_photo->setConverted_192($converted_picture['url']);
                    if(!checkRemoteFileIsImage($converted_picture['url'])) {
                        $status = 1;
                    }
                    continue;
                }
                if($converted_picture['type'] == CONVERTED236) {
                    $missing_converted_photo->setConverted_236($converted_picture['url']);
                    if(!checkRemoteFileIsImage($converted_picture['url'])) {
                        $status = 1;
                    }
                    continue;
                }
                if($converted_picture['type'] == CONVERTED550) {
                    $missing_converted_photo->setConverted_550($converted_picture['url']);
                    if(!checkRemoteFileIsImage($converted_picture['url'])) {
                        $status = 1;
                    }
                    continue;
                }
                if($converted_picture['type'] == CONVERTED736) {
                    $missing_converted_photo->setConverted_736($converted_picture['url']);
                    if(!checkRemoteFileIsImage($converted_picture['url'])) {
                        $status = 1;
                    }
                    continue;
                }
            }
            if(empty($converted_pictures)) {
                $status = 1;
            }
            $missing_converted_photo->setStatus($status);
            $missing_converted_photo->save();
        }
        
    }
}
