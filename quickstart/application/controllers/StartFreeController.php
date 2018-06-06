<?php

class StartFreeController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function indexAction()
    {
        if($this->is_merchant()) {
            redirect(getSiteMerchantUrl('/preview'));
        }

        global $redis;
        
        $this->view->request = $_REQUEST;
        
        if(isset($_REQUEST['submit'])) {
            
            if(empty($_REQUEST['email'])) {
                $this->view->errnos[INVALID_EMAIL] = 1;
                return;
            }
            if(empty($_REQUEST['password'])) {
                $this->view->errnos[INVALID_PASSWORD] = 1;
                return;
            }
            
            $username = trim($_REQUEST['email']);
            $password = trim($_REQUEST['password']);
            $pinterest_username = !empty($_REQUEST['pinterest_username'])?trim($_REQUEST['pinterest_username']):'';
            
            $service = AccountsService::getInstance();
            $service->setMethod('signup');
            $service->setParams(array('username'=>$username, 'password'=>$password, 'type' => MERCHANT,
                'pinterest_username'=>$pinterest_username, 'account_dbobj'=>$this->account_dbobj));
            $service->call();
            
            $status = $service->getStatus();
            
            if($status === 0) {
            	$response = $service->getResponse();
                $user = $response['user'];
                $user_id = $user->getId();
                $username = $redis->get("user:$user_id:username");
                $merchant_id = $redis->get("user:$user_id:merchant_id");
                $store_id = $redis->get("merchant:$merchant_id:store_id");
                $pinterest_account_id = $redis->get("merchant:$merchant_id:pinterest_account_id")?
                        $redis->get("merchant:$merchant_id:pinterest_account_id"):0;
                
                // setup session
                $this->user_session->user_id = $user_id;
                $this->user_session->merchant_id = $merchant_id;
                
                // send an email
                global $shopinterest_config;
                $service = new EmailService();
                $service->setMethod('create_job');
                $service->setParams(array(
                    'to' => $username,
                    'from' => $shopinterest_config->support->email,
                    'type' => USER_REGISTER,
                    'data' => array(
                        'site_url' => getURL(),
                        'link' => get_verification_url($user->getId(), $user->getUsername())
                    ),
                    'job_dbobj' => $this->job_dbobj
                ));
                $service->call();
                
                Log::write(INFO, 'Welcome email to '.$user->getUsername());

                redirect('/me/settings');
                
            } else {
                $this->view->errnos = array_append($this->view->errnos, $service->getErrnos());
            }
            
        }
    }
        

}   

