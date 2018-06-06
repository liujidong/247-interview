<?php

// job type: 3
// data: $type, $id

// a cron job
// while get next job till no such job avail and then exit

require_once('includes.php');

$job_type = PINTEREST_IMAGE_UPLOADER;
while($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {
    // get the config of the job
    $job_config = new JobConfig($job_dbobj);
    $job_config->findOne('type='.$job_type);
    $max_instances = $job_config->getMaxInstances();
    $instance_name = $_SERVER['SCRIPT_NAME'];
    $instances_count = get_instances_num($instance_name);
    Log::write(INFO, "Instance Count: $instances_count");
    if($instances_count > $max_instances) {
        Log::write(ERROR, "ERROR: there are more than $max_instances instances of $instance_name");
        die("ERROR: there are more than $max_instances instances of $instance_name\n");
    }
    Log::write(INFO, "job id: $job_id");

    // get the data
    $job3 = new Job($job_dbobj);
    $job3->findOne('id='.$job_id);
    $data = $job3->getData();
    Log::write(INFO,"data: ".json_encode($data));

    $type = $data['type'];
    $id = $data['id'];
    $status = PROCESSED;

    if($type === 'accounts') {
        $pinterest_account = new PinterestAccount($account_dbobj);
        $pinterest_account->findOne('id='.$id);
        $image_url = $pinterest_account->getImageUrl();
        if(!upload_image(get_pinterest_upload_dst($type, $id, 'avatar'), $image_url)) {
            $status = FAILED;
            Log::write(ERROR, "FAILED on uploading $image_url to $type $id avatar");
        } else {
            Log::write(INFO, "Success on uploading $image_url to $type $id avatar");
        }
        $image_large_url = $pinterest_account->getImageLargeUrl();
        if(!upload_image(get_pinterest_upload_dst($type, $id, 'avatar', 'large'), $image_large_url)) {
            $status = FAILED;
            Log::write(ERROR, "FAILED on uploading $image_large_url to $type $id avatar large");
        } else {
            Log::write(INFO, "Success on uploading $image_large_url to $type $id avatar large");
        }
        if($status === PROCESSED) {
            $pinterest_account->setPicUploadTime(get_current_datetime());
            $pinterest_account->save();
            Log::write(INFO, "Sucess on updating the pic_update time for pinterest account $id");
        }
        
    } else if($type === 'boards') {
        $pinterest_board = new PinterestBoard($account_dbobj);
        $pinterest_board->findOne('id='.$id);
        $thumbnails = explode(',', $pinterest_board->getThumbnails());
        foreach($thumbnails as $i => $thumbnail) {
            if(!upload_image(get_pinterest_upload_dst($type, $id, 'thumbnail', $i), $thumbnail)) {
                $status = FAILED;
                Log::write(ERROR,"FAILED on uploading $thumbnail to $type $id thumbnail $i");
            } else {
                Log::write(INFO, "Success on uploading $thumbnail to $type $id thumbnail $i");
            }
        }
        if($status === PROCESSED) {
            $pinterest_board->setPicUploadTime(get_current_datetime());
            $pinterest_board->save();
            Log::write(INFO, "Sucess on updating the pic_update_time for pinterest board $id");
        }
    } else if($type == 'pins') {
        $pinterest_pin = new PinterestPin($account_dbobj);
        $pinterest_pin->findOne('id='.$id);
        $images_mobile = $pinterest_pin->getImagesMobile();
        if(!upload_image(get_pinterest_upload_dst($type, $id, 'pin', 'mobile'), $images_mobile)) {
            $status = FAILED;
            Log::write(ERROR, "FAILED on uploading $images_mobile to $type $id pin mobile");
        } else {
            Log::write(INFO, "Success on uploading $images_mobile to $type $id pin mobile");
        }
        $images_closeup = $pinterest_pin->getImagesCloseup();
        if(!upload_image(get_pinterest_upload_dst($type, $id, 'pin', 'closeup'), $images_closeup)) {
            $status = FAILED;
            Log::write(ERROR, "FAILED on uploading $images_closeup to $type $id pin closeup");
        } else {
            Log::write(INFO, "Success on uploading $images_closeup to $type $id pin closeup");
        }
        $images_thumbnail = $pinterest_pin->getImagesThumbnail();
        if(!upload_image(get_pinterest_upload_dst($type, $id, 'pin', 'thumbnail'), $images_thumbnail)) {
            $status = FAILED;
            Log::write(ERROR, "FAILED on uploading $images_thumbnail to $type $id pin thumbnail");
        } else {
            Log::write(INFO, "Success on uploading $images_thumbnail to $type $id pin thumbnail");
        }
        $images_board = $pinterest_pin->getImagesBoard();
        if(!upload_image(get_pinterest_upload_dst($type, $id, 'pin', 'board'), $images_board)) {
            $status = FAILED;
            Log::write(ERROR, "FAILED on uploading $images_board to $type $id pin board");
        } else {
            Log::write(INFO, "Success on uploading $images_board to $type $id pin board");
        }
        if($status === PROCESSED) {
            $pinterest_pin->setPicUploadTime(get_current_datetime());
            $pinterest_pin->save();
            Log::write(INFO, "Sucess on updating the pic_update_time for pinterest pin $id");
        }
    }

    // update status
    $job3->setStatus($status);
    $job3->save();
    if($status === PROCESSED) {
        Log::write(INFO, "DONE: type $job_type job $job_id marked as success");
    } else {
        Log::write(ERROR, "ERROR: type $job_type job $job_id marked as failure");
    }
    
}




