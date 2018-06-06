<?php
/**
 * Let bash can use php function 
 * Input: name of the php function want be called , params 
 * 
 * @author wyixin
 */
require_once('includes.php');

$argv_array=array();
$argv_array=array_slice($argv,2,$argc-2);
call_user_func_array($argv[1],$argv_array);

