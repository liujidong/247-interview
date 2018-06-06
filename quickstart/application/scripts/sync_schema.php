<?php

// input: account, job, store, all (default)

// validate the input params
$type = isset($argv[1])?$argv[1]:'';

if($type !== 'account' && $type !== 'job' && 
        $type !== 'store' && $type !== 'update-tables') {
    die("ERROR: php ".$_SERVER['SCRIPT_NAME'].".php <account/job/store/update-tables>\n");
}

require_once('includes.php');
echo "Application Env:".APPLICATION_ENV."\n";

// dump table info to redis
get_table_info(true);
if($type === "update-tables"){
    // only update tables info(tables.php), all task done!
    die("db schema cache updated!\n");
}

// create the type of db if it doesnt exist
if(createDBIfNotExist($type)) {
    die("DONE: $type db created and no need to do the schema sync\n");
}

// compare the version of the schema file and the version of the db
// and update the db schema

updateDBSchema($type);






/**
 * step:
 * 
 * 1 the schema (account.sql, store.sql, job.sql) always contains the most updated schema
 * 2 for any change, update the version number of the schema
 * 3 put the alter sql in a separate sql file called: <type>.v<version_number>.sql
 * 4 put the "update version set version=<version number>" in the alter sql file
 * 5 run the sync_schema.php <type> 
 */