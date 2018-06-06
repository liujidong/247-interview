<?php

require_once('includes.php');

const SQL_FILE_PATH = '/home/wyixin/stores';

function list_files($dir = SQL_FILE_PATH) { 
    $root = scandir($dir); 
    
    foreach($root as $value) { 
        if($value === '.' || $value === '..') {continue;} 
        
        if(is_file("$dir/$value")) {
            $result[]="$dir/$value";
            continue;
        } 
    } 
    return $result; 
} 

function get_store_id_from_file_name($file_name) {
    $a = explode('_', $file_name);
    $b = explode('-', $a[1]);
    return $b[0];
}

function import_data($store_id, $sql_file) { 
    global $dbconfig;

    //$dbname = $dbconfig->store->name.'_'.$store_id;
    $dbname = 'store'.'_'.$store_id;
    $dbhost = $dbconfig->store->host;
    $command = "mysql -h $dbhost -uroot $dbname < $sql_file";
    exec($command,$output=array(), $results);
    if($results === 0) {
        return true;
    }
    return false;
}

$sql_files = list_files();


foreach ($sql_files as $sql_file) {

    $store_id = get_store_id_from_file_name($sql_file);
    
    if(import_data($store_id, $sql_file)) {
        echo "success import store data store_id : $store_id \n";
    } else {
        die("Failure store_id : $store_id");
    } 
}

