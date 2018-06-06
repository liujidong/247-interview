<?php

class PinterestBrowser {
    
    private static $browser = null;
    private $config = array();
    private $email = '';
    private $password = '';
    private $cookie_file = '';
    private $app_version = 'old';
    private $login_user = '';


    public function __construct($email, $password) {
        global $pinterest_config;
        $this->config = $pinterest_config;
        $this->email = $email;
        $this->password = $password;
        if(!empty($this->email)) {
            $this->cookie_file = $this->config->api->login->cookie_path.'/'.md5($this->email);
        }
        mkdir2($this->config->api->login->cookie_path, 0777);
    }
    
    public static function getInstance($email='', $password='') {
        if(!self::$browser) {
            self::$browser = new PinterestBrowser($email, $password);
        }
        return self::$browser;
    }
    
    // return:
    // pinterest_username -- login suceeds
    // false -- login fails
    public function login() {
        if(empty($this->cookie_file)) {
            return false;
        }
        if(!$csrftoken = $this->get_csrftoken()) {
            return false;
        }   
        $pinterest_username = '';
        $base_url = $this->config->api->login->endpoint;
        $homepage = $this->config->homepage;
        $cookie_file = $this->cookie_file;
        
        // old ui post params and headers
        $postfields['email'] = $this->email;
        $postfields['password'] = $this->password;
        $postfields['next'] = '/';
        $postfields['csrfmiddlewaretoken'] = $csrftoken;
        $headers = array(
            'Referer: '.$base_url          
        );
        
        // new ui post params and headers
        $postfields['data'] = json_encode(array(
            'options' => array(
                'username_or_email' => $this->email,
                'password' => $this->password
            ),
            'context' => array(
                'app_version' => '8fd5440'
            )
        ));
        $postfields['source_url'] = '/login/';
        $postfields['module_path'] = 'App()>LoginPage()>Login()>Button(class_name=primary, text=Log in, type=submit, tagName=button, size=large)';
        $headers = array(
            'Accept:application/json, text/javascript, */*; q=0.01',
            'Connection:keep-alive',
            'Content-Length:356',
            'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
            'Host:www.pinterest.com',
            'Origin:https://www.pinterest.com',
            'Referer:https://www.pinterest.com/login/',
            'X-CSRFToken:'.$csrftoken,
            'X-NEW-APP:1',
            'X-Requested-With:XMLHttpRequest'
        );

        $response = http_method('https://pinterest.com/resource/UserSessionResource/create/', 'POST', $cookie_file, $postfields, $headers);
        if($response['http_code'] !== 200) {
            return false;
        }

        $html_object = json_decode($response['html'], true);
        if(!empty($html_object) && isset($html_object['resource_response']['data']['username'])) {
            $pinterest_username = $html_object['resource_response']['data']['username'];
            if(!empty($pinterest_username)) {
                return $pinterest_username;
            }
        }
        // redirect to index page with cookies 
        $index = http_method($homepage, 'GET', $cookie_file);
        if($index['http_code'] !== 200) {
            return false;
        } 
        $dom = new Zend_Dom_Query($index['html']);

        foreach($dom->query('#UserNav a.nav') as $usernav_elem) {
            $pinterest_username = trim($usernav_elem->getAttribute('href'), '/');
        }
        // get username in new ui
        if(empty($pinterest_username)) {            
            //$pinterest_username = get_innerHTML($dom->query('.profileName'));
            $this->app_version = 'new';
            if(preg_match('/"username": "([^"${}]{3,})"/', $index['html'], $match)) {
                //$pinterest_username_parts = explode(':', $match[0]);
                //$pinterest_username = substr($pinterest_username_parts[1], 2, -1);
                $pinterest_username = $match[1];
            }
        }
        if(!empty($pinterest_username)) {
            $this->login_user = $pinterest_username;
            return $pinterest_username;
        }
        return false;
    }
    
    
    private function get_csrftoken() {
        $csrftoken = $this->get_csrftocken_from_cookie();
        if(!empty($csrftoken)) {
            return $csrftoken;
        }
        $base_url = $this->config->api->login->endpoint;
        $cookie_file = $this->cookie_file;
        $response = http_method($base_url, 'GET', $cookie_file);
        if($response['http_code'] !== 200 && $response['http_code'] !== 302) {
            return false;
        } 
        //pinterest new ui sets csrftocken in the cookie 
        $csrftoken = $this->get_csrftocken_from_cookie();
        if(!empty($csrftoken)) {
            $this->app_version = 'new';
            return $csrftoken;
        }
        $html = $response['html'];
        $dom = new Zend_Dom_Query($html);
        $token_elems = $dom->query('input[name="csrfmiddlewaretoken"]');
        foreach($token_elems as $token_elem) {
            $csrftoken = $token_elem->getAttribute('value');
            return $csrftoken;
        }
        
        return false;
    }
    
    private function get_csrftocken_from_cookie(){
        $csrftocken = '';
        $filename = $this->cookie_file;
        if(!$file_in = @fopen($filename, 'r')) {
            return $csrftocken;
        } 
        while(!feof($file_in)){
            $line =  fgets($file_in);
            if(!strncmp($line,'.pinterest.com', 14) || !strncmp($line,'pinterest.com', 13)) {
                $arr=  stristr($line, 'csrftoken');
                $csrftocken=str_replace('csrftoken', ' ', $arr);
                $csrftocken=  trim($csrftocken);
                break;
            }
        }
        return $csrftocken;
    }
    
    // postfields:
    // - board (external_pinterest_board_id)
    // - details
    // - link (source url)
    // - img_url
    // - tags
    // - buyable ($10)
    // - csrfmiddlewaretoken
    public function upload_pin($postfields) {
        if(empty($this->cookie_file)) {
            return false;
        }
        $postfields['csrfmiddlewaretoken'] = $this->get_csrftoken();
        if(!$external_pinterest_pin_id = $this->_upload_pin($postfields)) {
            $this->login();
            if(!$external_pinterest_pin_id = $this->_upload_pin($postfields)) {
                return false;
            } else {
                return $external_pinterest_pin_id;
            }
        } else {
            return $external_pinterest_pin_id;
        }
        
    }
//data:{"options":{"board_id":"466544911336903274","description":"test upload pin ","link":"","image_url":"http://assets7.pinimg.com/previews/O8I2SOoX.jpeg","method":"uploaded"},
//"context":{"app_version":"375c"}}    
    
    private function _upload_pin($postfields) {
        $base_url = $this->config->api->uploadpin->endpoint;
        $cookie_file = $this->cookie_file;
        $headers = array();
        
        $post_fields = array(
            'data' => json_encode(array(
                'options' => array(
                    'board_id' => $postfields['board'],
                    'description' => isset($postfields['details'])?$postfields['details']:'',
                    'link' => isset($postfields['link'])?$postfields['link']:'',
                    'image_url' => $postfields['img_url'],
                    'method' => 'uploaded'
                ),
                'context' => array(
                    'app_version' => '3ecc'
                )   
            )),
            'source_url' => "/$this->login_user/pins/",
            'module_path' => ''
        );        

        $base_url = $this->config->api->createpin->newendpoint;
        $referrer_url = substitute($this->config->api->editpin->newendpoint, array('pinterest_username' => $this->login_user));

        $headers = array(
            'Accept:application/json, text/javascript, */*; q=0.01',
            'Cache-Control:no-cache',
            'Connection:keep-alive',
            'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
            'Host:www.pinterest.com',
            'Origin:http://www.pinterest.com',
            'Pragma:no-cache',
            'Referer:http://www.pinterest.com/',
            'X-Pinterest-Referrer:'."$referrer_url",
            'X-CSRFToken:'.$postfields['csrfmiddlewaretoken'],
            'X-NEW-APP:1',
            'X-Requested-With:XMLHttpRequest'
        );              

        $response = http_method($base_url,'POST', $cookie_file, $post_fields, $headers);
        $return = json_decode($response['html'], true); // [html] => {"status": "success", "url": "/pin/80431543316610626/", "message": "posted."}

        if($response['http_code'] === 200) {    
            if(is_array($return) && isset($return['resource_response'])) {
                return $return['resource_response']['data']['id'];
            }
            if($return['status'] === 'success') {
                return getPinIdFromUrl($return['url']);       
            }
            return false;
        }
        return false;
    }
    
    // postfields:
    // - board (external_pinterest_board_id)
    // - details
    // - link (source url)
    // - img_url
    // - tags
    // - buyable ($10)
    // - csrfmiddlewaretoken
    // - external_pinterest_pin_id
    public function edit_pin($postfields) {
        if(empty($this->cookie_file)) {
            return false;
        }
        $postfields['csrfmiddlewaretoken'] = $this->get_csrftoken();
        if(!$this->_edit_pin($postfields)) {
            $this->login();
            if(!$this->_edit_pin($postfields)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }    
    }

    private function _edit_pin($postfields) {
        $base_url = substitute($this->config->api->editpin->endpoint, $postfields);
        $cookie_file = $this->cookie_file;
        $headers = array();
        $extra_fields = array(
            'data' => json_encode(array(
                'options' => array(
                    'board_id' => $postfields['board'],
                    'description' => isset($postfields['details'])?$postfields['details']:'',
                    'link' => isset($postfields['link'])?$postfields['link']:'',
                    'id' => $postfields['external_pinterest_pin_id']
                ),
                'context' => array(
                    'app_version' => '3ecc'
                )   
            )),
            'source_url' => "/$this->login_user/pins/",
            'module_path' => ''
        );        
        //if($this->app_version === 'new') {
            $base_url = $this->config->api->uploadpin->newendpoint;
            $referrer_url = substitute($this->config->api->editpin->newendpoint, array('pinterest_username' => $this->login_user));
            $headers = array(
                'X-Pinterest-Referrer:'."$referrer_url",
                "X-CSRFToken:".$postfields['csrfmiddlewaretoken'],
                'X-Requested-With:XMLHttpRequest'
            ); 
            $postfields = $extra_fields;
        //}        
        
        $response = http_method($base_url,'POST', $cookie_file, $postfields, $headers);
        if($response['http_code'] === 200){
            return true;
        } else {
            return false;
        }
    }

    // postfields:
    // - external_pinterest_pin_id or 
    // - csrfmiddlewaretoken
    public function delete_pin($postfields) {
        if(empty($this->cookie_file)) {
            return false;
        }
        $postfields['csrfmiddlewaretoken'] = $this->get_csrftoken();
        if(!$this->_delete_pin($postfields)) {
            $this->login();
            if(!$this->_delete_pin($postfields)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    
    private function _delete_pin($postfields) {
        $base_url = substitute($this->config->api->deletepin->endpoint, $postfields);
        $referrer_url = substitute($this->config->api->editpin->endpoint, $postfields);
        $cookie_file = $this->cookie_file;
        $post_datas = array();
        //if($this->app_version === 'new') {
            $base_url = $this->config->api->deletepin->newendpoint;
            $referrer_url = substitute($this->config->api->editpin->newendpoint, array('pinterest_username' => $this->login_user));   
            $post_datas['data'] = json_encode(array(
                'options' => array(
                    'id' => $postfields['external_pinterest_pin_id']
                ),
                'context' => array(
                    'app_version' => '375c'
                )
            ));
            $post_datas['source_url'] = "/$this->login_user/pins";
            $post_datas['module_path'] = '';
        //}
        
        $headers = array(
            'X-Pinterest-Referrer:'."$referrer_url",
            "X-CSRFToken:".$postfields['csrfmiddlewaretoken'],
            'X-Requested-With:XMLHttpRequest'
        );
        $response = http_method($base_url,'POST', $cookie_file, $post_datas, $headers);
        if($response['http_code'] === 200) {
            return true;
        } else {
            return false;
        }
    }
    
    // postfields
    // - name
    // - category
    // - csrfmiddlewaretoken
    // return
    // {"url": "/liangdev/a-test-board/", "status": "success", "name": "a test board", "id": "80431612034094377"}
    // false: error happens
    public function create_board($postfields) {
        if(empty($this->cookie_file)) {
            return false;
        }
        $postfields['csrfmiddlewaretoken'] = $this->get_csrftoken();
        $return = $this->_create_board($postfields);
        if($return === -1) {
            return false;
        } else if(!$return) {
            // try again
            $this->login();
            $return = $this->_create_board($postfields);
            if($return === -1 || !$return) {
                return false;
            } else {
                return $return;
            }
        } else {
            return $return;
        }
        
        if(!$return = $this->_create_board($postfields)) {
            $this->login();
            if(!$return = $this->_create_board($postfields)) {
                return false;
            } else {
                return $return;
            }
        } else {
            return $return;
        }
    }
    
    // return
    // false: not authenticated
    // -1: fatal error, no need to login and try again
    // return: succeed and the return results
    private function _create_board($postfields) {
        $base_url = $this->config->api->createboard->endpoint;
        $cookie_file = $this->cookie_file;
        $headers = array(
            'Accept:application/json, text/javascript, */*; q=0.01',
            'Cache-Control:no-cache',
            'Connection:keep-alive',
            'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
            'Host:www.pinterest.com',
            'Origin:http://www.pinterest.com',
            'Pragma:no-cache',
            'Referer:http://www.pinterest.com/',
            'X-CSRFToken:'.$postfields['csrfmiddlewaretoken'],
            'X-NEW-APP:1',
            'X-Requested-With:XMLHttpRequest'
        ); 
          
        $postfields = array(
            'data' => json_encode(array(
                'options' => array(
                    'name' => $postfields['name'],
                    'category' => isset($postfields['category'])?$postfields['category']:'other',
                    'description' => isset($postfields['description'])?$postfields['description']:'',
                    'privacy' => 'public'
                ),
                'context' => array(
                    'app_version' => '312c707'
                )   
            )),
            'source_url' => "/$this->login_user/boards/",
            'module_path' => 'App()>Header()>DropdownButton()>Dropdown()>AddPin()>ShowModalButton(module=BoardCreate)#Modal(module=BoardCreate())'
        );

        $base_url = $this->config->api->createboard->newendpoint;

        // [html] => {"url": "/liangdev/a-test-board/", "status": "success", "name": "a test board", "id": "80431612034094377"}
        $response = http_method($base_url, 'POST', $cookie_file, $postfields, $headers);
        $return = json_decode($response['html'], true);     
        if($response['http_code'] === 200) {   
            if(is_array($return) && isset($return['resource_response'])) {
                return array(
                    'url'=>$return['resource_response']['data']['url'],
                    'status'=> 'success',
                    'name' => $return['resource_response']['data']['name'],
                    'id' => $return['resource_response']['data']['id']
                );                  
            }
            if($return['status'] === 'success') {
                return $return;  
            }
            return false;  
        }
        return false;   
    }
    
}
