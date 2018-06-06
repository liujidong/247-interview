<?php

require_once('includes.php');

if($argc > 2) {
    echo "ERROR: php ".$_SERVER['SCRIPT_NAME'].".php [option name] \n";
    exit(1);
}

$options = $argv[1];

$optin_array = explode('.', $options);
$deep = count($optin_array);

$config = $application_config;

for($i=0; $i<$deep; $i++) {
    
    if($config = @$config->$optin_array[$i]) {
        if($i === $deep-1) {
            echo $config;
        }
    } else {
        ddd("Error: $optin_array[$i]");
        exit(1);
    }    
}