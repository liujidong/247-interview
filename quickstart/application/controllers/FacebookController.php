<?php

class FacebookController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function fbregisterAction() {

        global $shopinterest_config, $facebookconfig;
        $response = array('status' => 'failure', 'errors' => array());    

        if(!empty($_REQUEST['signed_request'])) {
            
            // facebook register
            $signed_request = $_REQUEST['signed_request'];
            $parse_signed_response = parse_signed_request($_REQUEST['signed_request'], $shopinterest_config->facebook->secret);

            if(!$parse_signed_response) {
                $this->view->errnos[INVALID_FACEBOOK_SIGNED_REQUESR] = 1;
            }
            foreach($parse_signed_response['registration'] as $key=>$val) {
                if($key === 'birthday') {
                    $timestamp = strtotime($val);
                    $_REQUEST['birth_day'] = date('d', $timestamp);
                    $_REQUEST['birth_month'] = date('m', $timestamp);
                    $_REQUEST['birth_year'] = date('Y', $timestamp);
                } else if($key === 'email') {
                    $_REQUEST['username'] = $val;
                } else {
                    $_REQUEST[$key] = $val;
                }
            }

            $_REQUEST['fb_user_id'] = isset($parse_signed_response['user_id']) ? $parse_signed_response['user_id'] : 0;    

        } else if(!empty($_REQUEST['access_token'])) {
            $short_lived_access_token = $_REQUEST['access_token'];
            $this->facebook->setAccessToken($short_lived_access_token);
            try {
                $fb_user = $this->facebook->api('/me','GET');
                // get the long lived access token
                $graph_api_url = $facebookconfig->api->graph->url;
                $endpoint = '/oauth/access_token';
                $params = array(
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => $shopinterest_config->facebook->app_id,
                    'client_secret' => $shopinterest_config->facebook->secret,
                    'fb_exchange_token' => $short_lived_access_token
                );
                $url = $graph_api_url.$endpoint.'?'.http_build_query($params);
                parse_str(file_get_contents($url), $parts);
                $long_lived_access_token = $parts['access_token'];
                // params for the account signup service
                $_REQUEST['fb_user'] = $fb_user;               
                $_REQUEST['username'] = $fb_user['email'];
                $_REQUEST['password'] = uniqid();
                if($birthday = default2String($fb_user['birthday'])) {
                    $timestamp = strtotime($birthday);
                    $_REQUEST['birth_day'] = date('d', $timestamp);
                    $_REQUEST['birth_month'] = date('m', $timestamp);
                    $_REQUEST['birth_year'] = date('Y', $timestamp);                    
                }

                $_REQUEST['gender'] = default2String($fb_user['gender']);
                $_REQUEST['name'] = default2String($fb_user['name']);
                $_REQUEST['first_name'] = default2String($fb_user['first_name']);
                $_REQUEST['last_name'] = default2String($fb_user['last_name']);

                $_REQUEST['fb_user']['short_lived_access_token'] = $short_lived_access_token;
                $_REQUEST['fb_user']['long_lived_access_token'] = $long_lived_access_token;
            } catch(FacebookApiException $e) {
                $this->view->errnos[FACEBOOKAPIEXCEPTION] = 1;
            }
        } else {
            $this->view->errnos[INVALID_FACEBOOK_CALL] = 1;
        }
        
        
        if(empty($this->view->errnos)) {
            $_REQUEST['account_dbobj'] = $this->account_dbobj;
            $_REQUEST['job_dbobj'] = $this->job_dbobj;
            // call the AccountService::signup to create the user record
            $service = new AccountsService();
            $service->setMethod('signup');
            $service->setParams($_REQUEST);
            $service->call();
            $status = $service->getStatus();
            if($status === 0) {
                $response['status'] = 'success'; 

                $response = $service->getResponse();
                $user = $response['user'];       
                // session
                $this->user_session->user_id = $user->getId();
                $this->user_session->merchant_id = $user->getMerchantId();
                $this->user_session->associate_id = $user->getAssociateId();
                $this->shopper_session->shopper_id = $user->getId();
                redirect($this->user_session->referrer);
            } else {
                $this->view->errnos = array_append($this->view->errnos, $service->getErrnos());
            }
        }
        
        $this->view->retry_link = $this->user_session->referrer;
    }
    
    public function indexAction() {
        $this->_helper->layout->disableLayout();        
        
    }
    
    public function testAction() {
        $this->_helper->layout->disableLayout();    
        
    }
    
}   

