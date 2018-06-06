<?php
if(!isset($argv[1])) {
    die("give me a store id!");
}

$store_id = $argv[1];

require_once('includes.php');

StoresMapper::forceDeleteStore($account_dbobj, $store_id);
