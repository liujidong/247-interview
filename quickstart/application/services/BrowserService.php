<?php

class BrowserService extends BaseService {

    private $email = '';
    private $password = '';
    private $cookie_path = '';
    private $cookie_file = '';
    
    public static function getInstance($email, $password, $cookie_path) {
        $inst = parent::getInstance();
        
        $inst->email = $email;
        $inst->password = $password;
        $inst->cookie_path = $cookie_path;
        $inst->cookie_file = $cookie_path.'/'.md5($inst->email);
        
        mkdir2($inst->cookie_path, 0777);
        
        return $inst;
    }
    
    public function makeRequest($url, $method='GET', $postfields=array(), $headers=array()) {
        
        $check_login_url = getURL('/api/is-login');;
        
        $response = http_method($check_login_url, 'GET', $this->cookie_file);

        if($response['http_code'] !== 200 || $response['html'] !== 'success') {
            // login 
            $login_url = getURL('/api/login');
            $login_postfields = array(
                'username' => $this->email,
                'password' => $this->password
            );
            $response = http_method($login_url, 'POST', $this->cookie_file, $login_postfields);
            $json = json_decode($response['html'], true);

            if($response['http_code'] !== 200 || $json['status'] !== 'success') {
                return false;
            }
        }
        
        return http_method($url, $method, $this->cookie_file, $postfields, $headers);
        
    }
    
    
}
