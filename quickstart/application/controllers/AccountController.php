<?php

class AccountController extends BaseController {
    
    public function init() {
        /* Initialize action controller here */
    }
    
    public function settingsAction() {
        global $redis;
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $associate_id = $this->user_session->associate_id;
        $address_id = $redis->get("user:$user_id:address_id");
        $keys = array(
            "user:$user_id:first_name",
            "user:$user_id:last_name",
            "user:$user_id:phone",
            "user:$user_id:username",
            "address:$address_id:addr1",
            "address:$address_id:addr2",
            "address:$address_id:city",
            "address:$address_id:state",
            "address:$address_id:country",
            "address:$address_id:zip"   
        );        
        $values = $redis->mget2($keys);
        
        // initialize the view object using the redis caching data
        $this->view->first_name = $values["user:$user_id:first_name"] ? $values["user:$user_id:first_name"] : '';
        $this->view->last_name = $values["user:$user_id:last_name"] ? $values["user:$user_id:last_name"] : '';
        $this->view->phone = $values["user:$user_id:phone"] ? $values["user:$user_id:phone"] : '';
        $this->view->addr1 = $values["address:$address_id:addr1"] ? $values["address:$address_id:addr1"] : '';
        $this->view->addr2 = $values["address:$address_id:addr2"] ? $values["address:$address_id:addr2"] : '';
        $this->view->city = $values["address:$address_id:city"] ? $values["address:$address_id:city"] : '';
        $this->view->state = $values["address:$address_id:state"] ? $values["address:$address_id:state"] : '';
        $this->view->country = $values["address:$address_id:country"] ? $values["address:$address_id:country"] : '';
        $this->view->zip = $values["address:$address_id:zip"] ? $values["address:$address_id:zip"] : '';
        $this->view->username = $values["user:$user_id:username"] ? $values["user:$user_id:username"] : '';
        
        if(isset($_REQUEST['submit'])) {
            $service = AccountsService::getInstance();
            $service->setMethod('update_settings');
            $service->setParams(array(
                'merchant_id' => $merchant_id,
                'associate_id' => $associate_id,
                'address_id' => $address_id,
                'user_id' => $user_id,
                'first_name' => $_REQUEST['first_name'],
                'last_name' => $_REQUEST['last_name'],
                'phone' => $_REQUEST['phone'],
                'addr1' => $_REQUEST['addr1'],
                'addr2' => $_REQUEST['addr2'],
                'city' => $_REQUEST['city'],
                'state' => $_REQUEST['state'],
                'country' => $_REQUEST['country'],
                'zip' => $_REQUEST['zip'],
                'account_dbobj' => $this->account_dbobj
            ));
            $service->call();
            $status = $service->getStatus();     
            $this->view->errnos = $service->getErrnos();
            $response = $service->getResponse();
            // overwrite some of the view data using the data returned from service
            $this->view->first_name = $response['first_name'];
            $this->view->last_name = $response['last_name'];
            $this->view->phone = $response['phone'];
            $this->view->addr1 = $response['addr1'];
            $this->view->addr2 = $response['addr2'];
            $this->view->city = $response['city'];
            $this->view->state = $response['state'];
            $this->view->country = $response['country'];
            $this->view->zip = $response['zip'];
        }
    }
    
    public function paymentsAction() {
        
        global $redis;
        $user_id = $this->user_session->user_id;
        $associate_id = $this->user_session->associate_id;    
        $merchant_id = $this->user_session->merchant_id;
        $payment_account_id = $redis->get("user:$user_id:payment_account_id");
        $account_status = $redis->get("associate:$associate_id:status");        
        $this->view->paypal_account_username = '';
        if($payment_account_id) {
            $paypal_account_id = $redis->get("payment_account:$payment_account_id:paypal_account_id");
            $paypal_account_username = $redis->get("paypal_account:$paypal_account_id:username");
            if($paypal_account_username) {
                $this->view->paypal_username = $paypal_account_username;
            }
        }
        $account_dbobj = $this->account_dbobj;
        
        if(isset($_REQUEST['submit'])) {
            
            if(empty($_REQUEST['paypal_username'])) {
                $this->view->errnos[INVALID_PAYPAL_ACCOUNT] = 1;
                return;
            }

            $service = UserService::getInstance();
            $service->setMethod('add_payments');
            $service->setParams(array('user_id'=>$user_id, 
                'associate_id' => $associate_id,
                'payment_account_id' => $payment_account_id,
                'paypal_username'=>$_REQUEST['paypal_username'], 
                'account_dbobj'=>$account_dbobj));
            $service->call();
            
            if($service->getStatus() === 1) {
                $this->view->errnos = $service->getErrnos();
                return;
            } else {
                $response = $service->getResponse();      
                
                $associate_status = $redis->get("associate::$associate_id:status"); 
                if($associate_status == ACTIVATED && $this->subdomain_type === 'salesnetwork') {
                    redirect ('/associate/search');    
                }
                
                if(!empty($merchant_id) && $this->subdomain_type === 'merchant') {
                    redirect('/preview');                    
                }
            }
            
            $this->view->paypal_account_id = $response['paypal_account_id'];
            $this->view->paypal_username = $response['paypal_username'];

        }
    }
    
    public function verifyAction() {
        
        if(empty($_REQUEST['code'])) {
            redirect(getURL());
        }
        
        $service = AccountsService::getInstance();
        $service->setMethod('verify');
        $service->setParams(array(
            'code' => $_REQUEST['code'],
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
            
        if($this->is_user()) {
            $next_page = '/me/settings';
        } else {
            $subdomain_type = $this->subdomain_type;

            switch ($subdomain_type) {                
                case 'salesnetwork':
                    $next_page = getSiteAssociateUrl('/associate/login');
                    break;
                case 'merchant':
                    $next_page = getSiteMerchantUrl('/login');
                    break;
                default:
                    $next_page = getURL();
            }
            
        }
        
        $this->view->status = $service->getStatus();
        $this->view->next_page = $next_page;        
    }
}   

