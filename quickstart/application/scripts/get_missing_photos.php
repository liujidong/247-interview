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
            $picture_url = $product_picture['picture_url'];
            $original_url = $product_picture['original_url'];
            $picture_id = $product_picture['picture_id'];
            $pinterest_pin_id = $product_picture['pinterest_pin_id'];
            $missing_photo = new MissingPhoto($account_dbobj);
            $missing_photo->setStoreId($store_id);
            $missing_photo->setProductId($product_id);
            $missing_photo->setPictureId($picture_id);
            
            if(!checkRemoteFileIsImage($picture_url)) {
                echo "id: $picture_id url: $picture_url **doesnt exist**\n";
                $pin_image = new PinImage($account_dbobj);
                $pin_image->findOne("pinterest_pin_id = $pinterest_pin_id");
                $pinterest_image_url = $pin_image->getImage_736();
                if(empty($pinterest_image_url)) {
                    $pin = new PinterestPin($account_dbobj);
                    $pin->findOne('id='.$pinterest_pin_id);
                    $pin_external_id = $pin->getExternalId();
                    $pinterest_pin_page = new PinterestPinPage($pin_external_id);
                    $pin_info = $pinterest_pin_page->getPinInfo();  
                    $pinterest_image_url = !empty($pin_info['image_736']) ? $pin_info['image_736'] : '';
                }
                $s3_url = $amazonconfig->api->s3->url.get_pinterest_upload_dst('pins', $pinterest_pin_id, 'pin', 'mobile');
                if(!checkRemoteFileIsImage($s3_url)) {
                    $s3_url = '';
                }
                if(!checkRemoteFile($original_url)) {
                    $original_url = '';
                }
                $missing_photo->setPinterestImageUrl($pinterest_image_url);
                $missing_photo->setS3Url($s3_url);
                $missing_photo->setOriginalUrl($original_url);
                $missing_photo->setStatus(1);
                
            } else {
                echo "id: $picture_id url: $picture_url SUCCESS\n";
                $missing_photo->setStatus(2);
            }
            $missing_photo->save();
        }
        
    }
}
