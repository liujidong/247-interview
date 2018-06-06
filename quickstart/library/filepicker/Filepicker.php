<?php

class Filepicker {
    
    private $_storeApiUrl = null;

    public function __construct() {  
        global $filepicker;
        $this->_storeApiUrl = substitute($filepicker->api->store->endpoint, array('key' => $filepicker->api->key));   
    }
    
    public function store_image($image_input) {
        //store a image from url
        if(validate($image_input, 'url')) {
            $postfields = array('url' => $image_input);
            return $response = http_method2($this->_storeApiUrl ,$return_json = true ,'POST', $cookie_file='', $postfields);
        }
        //store a image from local
        if(file_exists($image_input)) {
            $cmd = "curl -X POST -F fileUpload=@$image_input {$this->_storeApiUrl}";
            exec($cmd, $response);
            return json_decode($response[0], true);            
        }
        return false;
    } 

    private function _get_convert_api_url($image_resource) {
        $remote_image_url = $image_resource['url'];
        return $remote_image_url.'/convert';
    }
    
    //options here refer filepicker convert api options
    //e.g. : w, h, fit, crop ...
    //link : https://developers.inkfilepicker.com/docs/web/#inkblob-images  
    public function convert_image($image_resource, $options) {
        $convert_api_endpoint = $this->_get_convert_api_url($image_resource);
        $converted_image_url = http_build_url2($convert_api_endpoint, $options);
        return $converted_image_url;
    }
}