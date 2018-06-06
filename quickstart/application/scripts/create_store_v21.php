<?php

require_once 'includes.php';

$store_v21_tpl = file_get_contents('quickstart/application/scripts/store.v21.tpl');
$store_sql_file = 'quickstart/application/configs/store.sql';
$store_temp_sql_file = "quickstart/application/configs/store.".APPLICATION_ENV.".sql";
$store_v21_dst = "quickstart/application/configs/store.v21.".APPLICATION_ENV.".sql";

$data = array(
    'account_dbname' => $dbconfig->account->name
);

$output = substitute($store_v21_tpl, $data);

file_put_contents($store_v21_dst, $output);

$lines = file($store_sql_file);

do{
    $line = array_pop($lines);
    if(startsWith($line, 'insert')) {
        break;
    }
}while(1);

file_put_contents($store_temp_sql_file, $lines);

file_put_contents($store_temp_sql_file, $output, FILE_APPEND);
