<?php

require_once('includes.php');

use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\CartCreate;
use ApaiIO\Operations\CartAdd;
use ApaiIO\Operations\CartClear;
use ApaiIO\ApaiIO;


$conf = new GenericConfiguration();
$conf
    ->setCountry('com')
    ->setAccessKey($amazonconfig->api->access_key)
    ->setSecretKey($amazonconfig->api->secret)
    ->setAssociateTag($amazonconfig->api->affiliate_id);
$apaiIO = new ApaiIO($conf);

$cc = new CartCreate();
$cc->addItem('B002KHN23S', 1, true);
$formattedResponse = $apaiIO->runOperation($cc);
$xml = simplexml_load_string($formattedResponse);
$json = json_decode(json_encode($xml), true);

dddd($formattedResponse);