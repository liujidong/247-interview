<?php
require_once('includes.php');

if(empty($argv[1])) {
    $start_pin_id = 0;
} else {
    $start_pin_id = intval($argv[1]);
}

$pin_ids = PinterestPinsMapper::getAllPinsId($account_dbobj, $start_pin_id);

foreach ($pin_ids as $pin_id) {

    ddd('************************************start');
    ddd("Pin id : ".$pin_id);

    $pin_obj = new PinterestPin($account_dbobj);
    $pin_obj->findOne('id='.$pin_id);

    if($pin_obj->getId() !== 0) {
        $board_img = trim($pin_obj->getImagesBoard());
        $mobile_img = trim($pin_obj->getImagesMobile());
        $closeup_img = trim($pin_obj->getImagesCloseup());
        $thumbnail_img = trim($pin_obj->getImagesThumbnail()); 

        $imgs = array('images_board'=>$board_img, 'images_mobile'=>$mobile_img, 'images_closeup'=>$closeup_img, 'images_thumbnail'=>$thumbnail_img);

        foreach ($imgs as $key => $img) {
            if(!empty($img)) {
                $method = 'set'. ucfirst(to_camel_case($key));
                $method = 'setImagesBoard';
                $new_pic_url = trim(get_valid_pinterest_image_url2($img));       
                ddd("org image:".$img);
                ddd("validated image:".$new_pic_url);   
                if($new_pic_url !== $img) {
                    ddd("upload $key");
                    $pin_obj->$method($new_pic_url);
                }
            }
        }
        //$pin_obj->save();    
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