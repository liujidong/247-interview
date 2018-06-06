<?php

require_once('includes.php');

$size = 6000;

for($i=0;$i<$size;$i++) {
    $code = substr(uniqid(), 8, 5);
    
    $code_obj = new Code($account_dbobj);
    $code_obj->setCode($code);
    $code_obj->save();
}




