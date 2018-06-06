<?php

require_once('includes.php');

if(empty($argv[1])) {
    $start_store_id = 0;
} else {
    $start_store_id = intval($argv[1]);
}

$store_infos=StoresMapper::getAllStoreInfo($account_dbobj, $start_store_id);

foreach ($store_infos as $store_info) {
    ddd('************************************start');
    $store_id = $store_info['id'];
    $store_subdomain = $store_info['subdomain'];
    ddd('store_id: '.$store_id.' store_subdomain: '.$store_subdomain);
    $host = $store_info['host'];
    $store_dbobj = DBObj::getStoreDBObj($host, $store_id);
    $product_ids = StoresMapper::getProductIds($store_dbobj,IGNORE_STATUS);
    foreach($product_ids as $product_id) {
        $product_info = StoresMapper::getProduct($product_id, $store_dbobj);
        $pic_ids = explode(',', $product_info['pic_ids']);
        foreach($pic_ids as $pic_id) {
            // loop through images
            $picture = new Picture($store_dbobj);
            $picture->findOne('id='.$pic_id);
            if($picture->getId() !== 0) {
                $pic_url = trim($picture->getUrl());
                $new_pic_url = trim(get_valid_pinterest_image_url2($pic_url));
                ddd("org image:".$pic_url);
                ddd("validated image:".$new_pic_url);
                if($pic_url !== $new_pic_url) {
                    ddd('update pic');
                    $picture->setUrl($new_pic_url);
                    $picture->save();
                }
            }
            
        }
        
        
    }
    ddd('************************************end');
    
}

function get_valid_pinterest_image_url2($url) {
    if((!strpos($url, 'pinterest.com') && !strpos($url, 'pinimg.com')) || url_exists($url)) {
        return $url;
    }
    $parts = parse_url($url);
    $new_hosts = array('media-cache-ec0.pinterest.com', 'media-cache-ec1.pinterest.com', 
        'media-cache-ec0.pinimg.com', 'media-cache-ec0.pinimg.com');
    $found = false;
    foreach($new_hosts as $new_host) {
        $parts['host'] = $new_host;
        $new_url = http_build_url($parts);
        if(url_exists($new_url)) {
            $found = true;
            break;
        }
        if(url_exists($new_url = str_replace('192', '192x', $new_url))) {
            $found = true;
            break;
        }
    }
    if(!$found) {
        if(!strpos($url, '/f.jpg') && !strpos($url, '/c.jpg') && !strpos($url, '/t.jpg') && !strpos($url, '/b.jpg') 
                && !strpos($url, 'assets3.pinimg.com')) {
            dddd('******URL NOT FOUND:'.$url);
        } else {
            ddd('******URL NOT FOUND and continue:'.$url);
        }
        
    }
    return $found?$new_url:$url;
}