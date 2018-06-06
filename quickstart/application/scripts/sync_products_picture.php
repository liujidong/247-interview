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
$default_product_image = getSiteMerchantUrl(DEFAULT_PRODUCT_IMAGE);
foreach ($store_infos as $store_info) {

    // input store_id store_host
    $store_id = $store_info['id'];
    $store_host = $store_info['host'];
    
    echo "start converting pictures for store id: $store_id and store_host is: $store_host \n";

    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);
    
    if(!$store_dbobj->is_db_existed()) {
        continue;
    }
    
    $service = new ProductPhotosService();

    //$product_pictures = array(
    //  0 => array(
    //      'product_id' => 2,
    //      'pictures' => array(
    //          0 => array(
    //              'picture_id' => 7,
    //              'picture_pinterest_pin_id' => 
    //              'picture_url' => https://s3.amazonaws.com/shopinterest_stage/stores/260/5243cdaa9194a_7.jpg
    //              'picture_source' => csvimport
    //          ),
    //          
    //      )
    //  )
    //)

    while ($products = ProductsMapper::getUnsyncProduct($store_dbobj)) {
        
        foreach ($products as $product) {
            $product_id = $product['product_id'];
            $product_pictures = $product['pictures'];
            echo "start converting picture for product id : $product_id \n";

            foreach ($product_pictures as $product_picture) {
                $pinterest_pin_id = $product_picture['pinterest_pin_id'];
                $picture_id = $product_picture['picture_id'];
                $picture_url = $product_picture['picture_url'];
                $picture_source = $product_picture['picture_source'];
                
                echo "get the original picture url for product id: $product_id and picture id is: $picture_id \n";                

                // prepare product image here ,before create multi image 
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
                        $image_736_url = !empty($pin_info['image_736']) ? $pin_info['image_736'] : $default_product_image;
                    }              
                } else {
                    // etsy and single product imported image     
                    $image_736_url = checkRemoteFileIsImage($picture_url) ? $picture_url : $default_product_image;            
                }

                $picture = new Picture($store_dbobj);
                $picture->findOne('id='.$picture_id);
                $picture->setUrl($image_736_url);
                $picture->save(); 
                echo "save original picture url: $image_736_url\n";
            }   
            
            $service->setMethod('create_product_photo');
            $service->setParams(array(
                'store_id' => $store_id,
                'product_id' => $product_id,
                'store_dbobj' => $store_dbobj
            ));
            $service->call();
            echo "product pictures converted\n";
        }                  
    }
    
    echo "finish sync store start: {$argv[1]} ends : {$argv[2]} \n";
}