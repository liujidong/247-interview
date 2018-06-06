<?php

require_once('includes.php');

global $dbconfig, $shopinterest_config;
$timestamp = get_timestamp();

$job_host = $dbconfig->job->host;
$job_user = $dbconfig->job->user;
$job_password = $dbconfig->job->password;
$job_dbname = $dbconfig->job->name;

$dbbackupdir = $shopinterest_config->dbbackup->dir;

mkdir2($dbbackupdir);

$conn = get_conn($job_host, $job_user, $job_password);
//cold backup, archive and copy file to backup dir
backupjobdb($dbbackupdir);
select_db($job_dbname, $conn);

//backup jobs table
//rename jobs table to jobs_<timestamp>, create now jobs table
$jobs_table_schema = get_table_schema('jobs');
$jobs_backup_table_name = "jobs_$timestamp";
mysql_query("rename table jobs to $jobs_backup_table_name", $conn);
mysql_query($jobs_table_schema, $conn);
//insert the unprocessed job to new jobs table 
$sql = "insert into jobs select * from $jobs_backup_table_name where status=0 or status=4";
mysql_query($sql, $conn);

//backup logs table
$logs_table_schema = get_table_schema('logs');
$logs_backup_table_name = "logs_$timestamp";
mysql_query("rename table logs to $logs_backup_table_name", $conn);
mysql_query($logs_table_schema, $conn);

//delete tables older than 7 days
drop_old_table($job_dbname, 'jobs', 'logs');

unset($conn);