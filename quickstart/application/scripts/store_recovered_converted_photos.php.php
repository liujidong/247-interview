<?php

require_once('includes.php');

$photo_ids = MissingConvertedPhotosMapper::get_recovered_converted_photo_ids($account_dbobj);

// status: 
// 3 -- failed
// 4 -- succeeded

foreach($photo_ids as $photo_id) {
    
    $photo = new MissingConvertedPhoto($account_dbobj, $photo_id);
    $store_id = $photo->getStoreId();
    $store = new Store($account_dbobj, $store_id);
    $store_host = $store->getHost();
    $store_dbobj = DBObj::getStoreDBObj($store_host, $store_id);
    $picture_id = $photo->getPictureId();
    $converted45 = $photo->getConverted_45();
    $converted70 = $photo->getConverted_70();
    $converted192 = $photo->getConverted_192();
    $converted236 = $photo->getConverted_236();
    $converted550 = $photo->getConverted_550();
    $converted736 = $photo->getConverted_736();
    echo "recovered missing converted photo id: $photo_id store id: $store_id picture_id $picture_id\n";
    echo "converted45: $converted45\n";
    echo "converted70: $converted70\n";
    echo "converted192: $converted192\n";
    echo "converted236: $converted236\n";
    echo "converted550: $converted550\n";
    echo "converted736: $converted736\n";
    
    $converted_picture = new ConvertedPicture($store_dbobj);
    $converted_picture->findOne('picture_id='.$picture_id.' and type=45');
    if($converted_picture->getId() === 0) {
        $converted_picture->setPictureId($picture_id);
        $converted_picture->setType(CONVERTED45);
    }
    $converted_picture->setUrl($converted45);
    $converted_picture->save();
    echo "converted45 saved ".$converted_picture->getId()."\n";
    
    $converted_picture = new ConvertedPicture($store_dbobj);
    $converted_picture->findOne('picture_id='.$picture_id.' and type=70');
    if($converted_picture->getId() === 0) {
        $converted_picture->setPictureId($picture_id);
        $converted_picture->setType(CONVERTED70);
    }
    $converted_picture->setUrl($converted70);
    $converted_picture->save();
    echo "converted70 saved ".$converted_picture->getId()."\n";
    
    $converted_picture = new ConvertedPicture($store_dbobj);
    $converted_picture->findOne('picture_id='.$picture_id.' and type=192');
    if($converted_picture->getId() === 0) {
        $converted_picture->setPictureId($picture_id);
        $converted_picture->setType(CONVERTED192);
    }
    $converted_picture->setUrl($converted192);
    $converted_picture->save();
    echo "converted192 saved ".$converted_picture->getId()."\n";
    
    $converted_picture = new ConvertedPicture($store_dbobj);
    $converted_picture->findOne('picture_id='.$picture_id.' and type=236');
    if($converted_picture->getId() === 0) {
        $converted_picture->setPictureId($picture_id);
        $converted_picture->setType(CONVERTED236);
    }
    $converted_picture->setUrl($converted236);
    $converted_picture->save();
    echo "converted236 saved ".$converted_picture->getId()."\n";
    
    $converted_picture = new ConvertedPicture($store_dbobj);
    $converted_picture->findOne('picture_id='.$picture_id.' and type=550');
    if($converted_picture->getId() === 0) {
        $converted_picture->setPictureId($picture_id);
        $converted_picture->setType(CONVERTED550);
    }
    $converted_picture->setUrl($converted550);
    $converted_picture->save();
    echo "converted550 saved ".$converted_picture->getId()."\n";
    
    $converted_picture = new ConvertedPicture($store_dbobj);
    $converted_picture->findOne('picture_id='.$picture_id.' and type=736');
    if($converted_picture->getId() === 0) {
        $converted_picture->setPictureId($picture_id);
        $converted_picture->setType(CONVERTED736);
    }
    $converted_picture->setUrl($converted736);
    $converted_picture->save();
    echo "converted736 saved ".$converted_picture->getId()."\n";
    
    
    $photo->setStatus(6);
    $photo->save();
}
