<?php

require_once('includes.php');

define('CONVERTERD_STORE_LOGO', 'store_avatar_converted');

$store_info = StoresMapper::getAllStoreInfo($account_dbobj);
$filepicker = new Filepicker();
$options = array(
    'w' => 120, 
    'h' => 120,
    'format' => 'jpg', 
    'quality' => '100',
    'fit' => 'crop'
); 

foreach ($store_info as $store) {
    $store_id = $store['id'];
    $store_logo = $store['logo'];
    echo "store id : $store_id logo : $store_logo \n";
    
    $store_obj = new Store($account_dbobj, $store_id);   

    if(checkRemoteFileIsImage($store_logo)) {
        
        $salt = uniqid();

        $image_resource = $filepicker->store_image($store_logo);
        $converted_store_logo = $filepicker->convert_image($image_resource, $options);    

        $dst = get_product_image_upload_dst($store_id, $salt, CONVERTERD_STORE_LOGO);    
        if($stored_url = upload_image($dst, $converted_store_logo)) {
            echo "upload converted_logo to s3, returnd url: $stored_url \n";    
            $store_obj->setConvertedLogo($stored_url);
            $store_obj->save();
        }     
    } else {
        echo "store id : $store_id logo : $store_logo is invalidate \n";  
    }
}


