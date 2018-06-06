<?php

require_once('includes.php');

// test image uploader

$dst = '/db_web_data/db_data/brocoli.jpg';
$src = 'http://media-cache-ec5.pinterest.com/upload/232005818273357098_9HloJotp_b.jpg';

upload_image($dst, $src);


