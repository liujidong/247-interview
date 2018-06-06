<?php

$site_version = 2;

define('APPLICATION_PATH', __DIR__.'/../');

require_once APPLICATION_PATH.'/autoload.php';
require_once APPLICATION_PATH.'/constants.php';
require_once APPLICATION_PATH.'/errors.php';
require_once APPLICATION_PATH.'/utils/utils.php';

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once __DIR__.'/../../library/Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->pushAutoloader('__autoload');
require_once (__DIR__."/../../library/Zend/Dom/Query.php");
require_once (__DIR__."/../../library/Zend/Config/Ini.php");
require_once (__DIR__."/../../library/Zend/View.php");
require_once (__DIR__."/../../library/s3/S3.php");
$htaccess = file(realpath(dirname(__FILE__)) . '/../../public/.htaccess');
foreach ($htaccess as $line) {
    if (preg_match('/^\s*SetEnv\s+APPLICATION_ENV\s+(.*?)\s*$/', trim($line), $matches)) {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', $matches[1]);
        break;
    }
}
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

$application_config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', APPLICATION_ENV, true);

// setup site(domain, version, https) info
$site_versions = $application_config->site->versions;
list($site_domain, $site_merchant_url, $site_version) = getSiteVersionInfo($site_versions);
$site_https_enable = $application_config->site->https->enable;

// set version sepcific config
$config_version_key = "v$site_version";
foreach($application_config->$config_version_key as $k => $v) {
    if(isset($application_config, $k)) {
        $application_config->$k->merge($v);
    } else {
        $application_config->$k = $v;
    }
}
$application_config->setReadOnly();

// get config items
$dbconfig = $application_config->database;
$paypalconfig = $application_config->paypal;
$facebookconfig = $application_config->facebook;
$fileuploader_config = $application_config->fileuploader;
$amazonconfig = $application_config->amazon;
$transaction_config = $application_config->transaction_config;
$shopinterest_config = $application_config->shopinterest;
$pinterest_config=$application_config->pinterest;
$sphinx_config=$application_config->sphinx;
$redis_config = $application_config->redis;
$sendgridconfig = $application_config->sendgrid;
$filepicker = $application_config->filepicker;
$googleconfig = $application_config->google;
$cloudinary_config = $application_config->cloudinary;

$account_dbobj = DBObj::getAccountDBObj();
$job_dbobj = DBObj::getJobDBObj();

// load mustache
require APPLICATION_PATH.'/../library/mustache/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

// load google client api
require APPLICATION_PATH.'/../library/google-api-php-client/src/Google_Autoloader.php';
Google_Autoloader::register();

// load paypal rest api
require APPLICATION_PATH.'/../library/paypal/rest/vendor/autoload.php';
require APPLICATION_PATH.'/../library/paypal/rest/common.php';
require_once APPLICATION_PATH.'/globals.php';

// load composer installed libarries: cloudinary, amazon product advertising api
require APPLICATION_PATH.'/../library/vendor/autoload.php';
\Cloudinary::config(array(
    "cloud_name" => $cloudinary_config->api->cloud_name,
    "api_key" => $cloudinary_config->api->key,
    "api_secret" => $cloudinary_config->api->secret,
));

// connect to redis
$redis = new RedisCache($account_dbobj);
$redis->connect($redis_config->server->host, $redis_config->server->port);

if(APPLICATION_ENV != 'production' && APPLICATION_ENV != 'staging'){
    // update table info on every request under dev env, overide data in tables.php
    get_table_info(true);
}
require_once APPLICATION_PATH.'/cachekey_lists.php';