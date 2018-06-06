<?php
require_once('includes.php');

while ($exist_pins = PinterestPinsMapper::getUnSyncPins($account_dbobj)) {

    foreach ($exist_pins as $pin) {
        echo "now sync Pinterest Pin_id : {$pin['id']}, External_Id : {$pin['external_id']} \n";
        $pin_external_id = $pin['external_id'];
        $pin_image_obj = new PinImage($account_dbobj);
        $pin_image_obj->setPinterestPinId($pin['id']);
        $pin_image_obj->setExternalId($pin_external_id);
        $pinterest_pin_page = new PinterestPinPage($pin_external_id);
        $pin_info = $pinterest_pin_page->getPinInfo();
        if((isset($pin_info['http_code']) && $pin_info['http_code'] === 404) || $pin_info['is_image'] === 0) {
            echo "pin image not exist \n";
            $pin_image_obj->setStatus(DELETED);
            $pin_image_obj->save();
            continue;
        }
        echo "set Pin image here \n";
        if(!$pin_image_obj->setImage45($pin_info['image_45']) || !$pin_image_obj->setImage70($pin_info['image_70']) 
                || !$pin_image_obj->setImage192($pin_info['image_192']) || !$pin_image_obj->setImage236($pin_info['image_236'])
                || !$pin_image_obj->setImage550($pin_info['image_550']) || !$pin_image_obj->setImage736($pin_info['image_736'])) {
            $pin_image_obj->setStatus(DELETED);
        }
        $pin_image_obj->save();
    }
}

echo "finish sync pins \n";
