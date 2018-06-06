<?php

require_once('includes.php');


$api = new \Cloudinary\Api();
$api->delete_resources_by_prefix("e-s");
die();

//$img = "https://www.filepicker.io/api/file/alwlyyGySx2lbcAeBM6z";
//$r = \Cloudinary\Uploader::upload($img, array("public_id" => "testing/my_name_1"));
//dddd($r);

$pid="e-s/s-s/s-1/m/533b925793c001396413015533b925793c3d.jpg";

echo cloudinary_url($pid,array("width" => 100, "height" => 150, "crop" => "fill"));
die();
/*

  Array
(
    [public_id] => wquub8qsmy15qnj1pvd7
    [version] => 1395810784
    [signature] => 5983df1ff4c3864455ff1b475207c4728460ce15
    [width] => 550
    [height] => 413
    [format] => jpg
    [resource_type] => image
    [created_at] => 2014-03-26T05:13:04Z
    [bytes] => 262739
    [type] => upload
    [etag] => d8060304c39a8522b22b7579d94735aa
    [url] => http://res.cloudinary.com/www-shopinterest-co/image/upload/v1395810784/wquub8qsmy15qnj1pvd7.jpg
    [secure_url] => https://res.cloudinary.com/www-shopinterest-co/image/upload/v1395810784/wquub8qsmy15qnj1pvd7.jpg
)

Array
(
    [public_id] => test/my_name
    [version] => 1395813672
    [signature] => fec729de8bf7d2c982a4f52b4d9c2aaf9ea2c410
    [width] => 550
    [height] => 413
    [format] => jpg
    [resource_type] => image
    [created_at] => 2014-03-26T06:01:12Z
    [bytes] => 262739
    [type] => upload
    [etag] => d8060304c39a8522b22b7579d94735aa
    [url] => http://res.cloudinary.com/www-shopinterest-co/image/upload/v1395813672/test/my_name.jpg
    [secure_url] => https://res.cloudinary.com/www-shopinterest-co/image/upload/v1395813672/test/my_name.jpg
)



 */