<?php
//run script this under su
require_once('includes.php');

global $shopinterest_config, $fileuploader_config, $amazonconfig;

$dbbackupdir = $shopinterest_config->dbbackup->dir;
$bucket = $fileuploader_config->store_bucket; 
$backup_folder = $amazonconfig->s3->databasebackup_folder;

//send to daily file to S3
$dailyfiles = getFileByCreateTime($dbbackupdir);
foreach ($dailyfiles as $dailyfile) {
    //check out the path
    $filename = basename($dailyfile);
    $path = $backup_folder.$filename;
    upload_file_to_s3($dailyfile, $bucket, $path, S3::ACL_PRIVATE);
}