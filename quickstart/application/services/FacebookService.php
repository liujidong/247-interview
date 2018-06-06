<?php

class FacebookService extends BaseService {
    
    // input: method, access_token
    public function me() {
        
        global $facebookconfig;
        
        $params = array(
            'method' => $this->params['method'],
            'access_token' => $this->params['access_token']
        );
        
        $url = http_build_url2($facebookconfig->api->graph->me->url, $params);
        
        $this->response = json_decode(curl_post($url, $params), true);
        
        if(isset($this->response['error'])) {
            $this->status = 1;
        } else {
            $this->status = 0;
        }
        
    }
    
}


