<?php

require_once('includes.php');

$service = AmazonSearchService::getInstance();
$service->setMethod('search');
$service->setParams(array(
    'keywords' => 'NIKE FUELBAND'
));
$service->call();

dddd($service->getResponse());


//use ApaiIO\Configuration\GenericConfiguration;
//use ApaiIO\Operations\Search;
//use ApaiIO\ApaiIO;
//
//global $amazonconfig;
//
//
//$conf = new GenericConfiguration();
//$conf
//    ->setCountry('com')
//    ->setAccessKey($amazonconfig->api->access_key)
//    ->setSecretKey($amazonconfig->api->secret)
//    ->setAssociateTag($amazonconfig->api->affiliate_id);
//
//$apaiIO = new ApaiIO($conf);
//
//$search = new Search();
//$search->setCategory('All');
//$search->setKeywords('NIKE FUELBAND');
//$search->setResponseGroup(array(\AmazonResponseGroups::Images, \AmazonResponseGroups::ItemAttributes));
//
//$formattedResponse = $apaiIO->runOperation($search);
//
//$xml = simplexml_load_string($formattedResponse);
//
//$json = json_decode(json_encode($xml), true);
//
//dddd($json);




