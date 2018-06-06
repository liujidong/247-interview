<?php

require_once('includes.php');

$fh = fopen('quickstart/application/scripts/data/urls.csv', 'r');
$filepicker = new Filepicker();
$i=0;
while($url = trim(fgets($fh))) {
    $resource = $filepicker->store_image($url);
    $converted_url = $filepicker->convert_image($resource, array(
        'w' => 192, 
        'format' => 'jpg', 
        'quality' => '100',
        'fit' => 'max'
    ));
    
    ddd($converted_url);

}
