<?php

class SalesnetworkController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function indexAction() {
        
        global $redis, $shopinterest_config;
        
        if($this->is_associate()) {
            redirect('/me/settings');
        }
        
        $session_user_id = empty($this->user_session->user_id)?0:$this->user_session->user_id;
        if($session_user_id !== 0) {
            $this->view->session_username = $redis->get("user:$session_user_id:username");
            $this->view->session_password = DUMMY_PASSWORD;
        }
        
        //salesnetwork signup logic
        if(isset($_REQUEST['submit'])){
            
            if($this->is_user()) {
                $_REQUEST['email'] = $this->view->session_username;
                $_REQUEST['password'] = $this->view->session_password;
            }
            
            $username = trim($_REQUEST['email']);
            $password = trim($_REQUEST['password']);
            
            if(empty($username)) {
                $this->view->errnos[INVALID_EMAIL] = 1;
                return;    
            }
            if(empty($password)) {
                $this->view->errnos[INVALID_PASSWORD] = 1;
                return;    
            }
            $service = AccountsService::getInstance();
            $service->setMethod('signup');
            $service->setParams(array('username'=>$username, 'password'=>$password, 'type' => ASSOCIATE,
                            'user_id' => $session_user_id, 'account_dbobj'=>$this->account_dbobj));
            $service->call();
            $status = $service->getStatus();

            if($status === 0) {
                $response = $service->getResponse();

                $user = $response['user'];
                $user_id = $user->getId();
                $associate_id = $redis->get("user:$user_id:associate_id");
                
                $this->user_session->associate_id = $associate_id;
                if($session_user_id === 0) {
                    // setup session
                    $this->user_session->user_id = $user_id;
                    
                    // send an email
                    $service = new EmailService();
                    $service->setMethod('create_job');
                    $service->setParams(array(
                        'to' => $user->getUsername(),
                        'from' => $shopinterest_config->support->email,
                        'type' => ASSOCIATE_REGISTER,
                        'data' => array(
                            'site_url' => getURL(),
                            'link' => get_verification_url($user->getId(), $user->getUsername())
                        ),
                        'job_dbobj' => $this->job_dbobj
                    ));
                    $service->call();   
                }             
                
                redirect('/me/settings');
            } else {
                $this->view->errnos = array_append($this->view->errnos, $service->getErrnos());
            }
            
        }
    }

}   

