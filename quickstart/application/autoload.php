<?php

function __autoload($class) {
    $paths = array(APPLICATION_PATH.'/controllers', APPLICATION_PATH.'/models', APPLICATION_PATH.'/services', 
    APPLICATION_PATH.'/utils', APPLICATION_PATH . '/../library/s3', APPLICATION_PATH . '/../library/pinterest', 
    APPLICATION_PATH . '/../library/sphinx', APPLICATION_PATH . '/../library/pagination', APPLICATION_PATH . '/../library/pagination2',
    APPLICATION_PATH . '/../library/filepicker', APPLICATION_PATH . '/../library/firebase');
    
    foreach($paths as $path) {
        $file_path = $path.'/'.$class.'.php';
        if(file_exists($file_path)) {
            require_once("$file_path");
            break;
        }
    } 
}
