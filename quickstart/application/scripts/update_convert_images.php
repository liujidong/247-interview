<?php

require_once('includes.php');

$fh_src = fopen('quickstart/application/scripts/data/converted_urls.csv', 'r');
$fh_dst = fopen('quickstart/application/scripts/data/urls.csv', 'r');

while($src = trim(fgets($fh_src))) {
    
    $dst = str_replace('https://s3.amazonaws.com', '', str_replace('http://s3.amazonaws.com', '', trim(fgets($fh_dst))));
    upload_image($dst, $src);
    ddd("src:".$src);
    ddd("dst:".$dst);
}
