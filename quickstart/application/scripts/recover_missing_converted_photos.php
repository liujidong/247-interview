<?php

require_once('includes.php');

$photo_ids = MissingConvertedPhotosMapper::get_missing_converted_photo_ids($account_dbobj);

// status: 
// 3 -- failed
// 4 -- succeeded

foreach($photo_ids as $photo_id) {
    echo "missing converted photo id: $photo_id\n";
    $photo = new MissingConvertedPhoto($account_dbobj, $photo_id);
    $picture_url = $photo->getPictureUrl();
    
    $status = 3;
    if(checkRemoteFileIsImage($picture_url)) {
        // convert it, update/store the converted ones
        $converted_images = convertImage($picture_url);
        uploadConvertedImageToS3(&$converted_images, 1);
        
        foreach($converted_images as $converted_image) {
            if($converted_image['converted_image_type'] == CONVERTED45) {
                $photo->setConverted_45($converted_image['converted_image_url']);
                continue;
            }
            if($converted_image['converted_image_type'] == CONVERTED70) {
                $photo->setConverted_70($converted_image['converted_image_url']);
                continue;
            }
            if($converted_image['converted_image_type'] == CONVERTED192) {
                $photo->setConverted_192($converted_image['converted_image_url']);
                continue;
            }
            if($converted_image['converted_image_type'] == CONVERTED236) {
                $photo->setConverted_236($converted_image['converted_image_url']);
                continue;
            }
            if($converted_image['converted_image_type'] == CONVERTED550) {
                $photo->setConverted_550($converted_image['converted_image_url']);
                continue;
            }
            if($converted_image['converted_image_type'] == CONVERTED736) {
                $photo->setConverted_736($converted_image['converted_image_url']);
                continue;
            }
        }
        if(!empty($converted_images)) {
            $status = 4;  
            echo "missing photos uploaded to s3\n";
        } else {
            echo "no missing photos uploaded to s3\n";
        }
        
    } else {
        echo "the original photo doesnt exist\n";
    }
    $photo->setStatus($status);
    $photo->save();
}
