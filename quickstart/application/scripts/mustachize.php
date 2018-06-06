<?php

require_once 'includes.php';

$file_path = $argv[1];

$string = file_get_contents($file_path);

$data = array(
    'account_dbname' => $dbconfig->account->name
);

$output = substitute($string, $data);

dddd($output);




