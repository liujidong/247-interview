<?php

require_once('includes.php');

/* aws command line tool: http://krypted.com/business/amazon-s3-from-the-command-line/
 * s3 db backup folder structure
 * staging: /shopinterest_stage/backup/db/<date>/*.gz
 * production: /shopinterest_production/backup/db/<date>/*.gz
 * 
 * steps:
 * 1. mysqldump account db, upload the dumped sql file to s3
 * 2. loop through the store dbs and mysqldump them, upload the dumped sql files one by one to s3
 * 3. rotate the logs table in the job db
 * 4. rotate the jobs table in the job db (insert unprocessed jobs into newly created job table)
 */

global $dbconfig;

Log::write(INFO, 'start DB Backup', true);

Log::write(INFO, '1. mysqldump account db, upload the dumped sql file to s3', true);

// 1. mysqldump account db, upload the dumped sql file to s3
$db = DB::getInstance($dbconfig->account->host, $dbconfig->account->name, $dbconfig->account->user, $dbconfig->account->password);
$db->backup();

Log::write(INFO, 'finish back up account db', true);

Log::write(INFO, '2. loop through the store dbs and mysqldump them, upload the dumped sql files one by one to s3', true);

// 2. loop through the store dbs and mysqldump them, upload the dumped sql files one by one to s3
$stores = StoresMapper::getAllStores($account_dbobj);
foreach($stores as $store) {
    $db = DB::getInstance($store['host'], $dbconfig->store->name.'_'.$store['id'], $dbconfig->store->user, $dbconfig->store->password);
    $db->backup(); 
    
    Log::write(INFO, 'finish backup store db '.$store['id'], true);
    
}

// 3. rotate the logs table in the job db
rotate_logs();

// 4. rotate the jobs table in the job db (insert unprocessed jobs into newly created job table)
rotate_jobs();