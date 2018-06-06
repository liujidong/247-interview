<?php

class AssociateController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }
    
    public function loginAction() {
        
        global $redis;
        
        if(isset($_REQUEST['submit'])) {
            
            $username = trim($_REQUEST['username']);
            $password = trim($_REQUEST['password']);
            
            if(empty($username)) {
                $this->view->errnos[INVALID_ASSOCIATE_LOGIN] = 1;
                return;            
            }
            if(empty($password)) {
                $this->view->errnos[INVALID_ASSOCIATE_LOGIN] = 1;
                return;           
            }
            
            $service = AccountsService::getInstance();
            $service->setMethod('login');
            $service->setParams(array('username'=>$username, 'password'=>$password, 
                            'account_dbobj'=>$this->account_dbobj));
            $service->call();
            $status = $service->getStatus();

            if($status === 0) {
                $response = $service->getResponse();

                if($response['logged_in'] === 1) {
                    $user = $response['user'];
                    $user_id = $user->getId();
                    $this->user_session->user_id = $user_id;
                    $this->user_session->merchant_id = $user->getMerchantId();
                    $this->user_session->associate_id = $redis->get("user:$user_id:associate_id");
                    
                    if(!empty($_GET['next'])) {
                        redirect($_GET['next']);
                    }                    
                    
                    if($this->user_session->associate_id !== 0) {
                        redirect('/associate/profile');
                    } else if($this->user_session->merchant_id !== 0) {
                        redirect(getSiteMerchantUrl('/profile'));
                    } else if($this->user_session->user_id !== 0) {
                        redirect('/me/settings');
                    }
                } 
            }

            $this->view->errnos[INVALID_ASSOCIATE_LOGIN] = 1; 
        }
        
    }

    public function profileAction() {

        global $redis;
        $user_id = $this->user_session->user_id;
        $associate_id = $this->user_session->associate_id;
        $account_dbobj = $this->account_dbobj;
        $pinterest_account_id = $redis->get("associate:$associate_id:pinterest_account_id");
        $account_status = $redis->get("associate:$associate_id:status");
        $products_cnt = SalesMapper::getProductCnt($associate_id, $account_dbobj, $description = '', $commission_start = '', $commission_end = '', $price_start = '', $price_end = '', $page_num = 0);
        $payment_account_id = $redis->get("user:$user_id:payment_account_id");
        $external_website_url = $redis->get("associate:$associate_id:external_website");
        $external_website_name = $redis->get("associate:$associate_id:external_website_name");        
        $external_website_content_options = array('Arts & Entertainment', 'News', 'How-to', 'Retail',
            'Personal Blog', 'Business Blog', 'Health & Beauty', 'Parenting', 'Social Media', 'Political', 'Social'
            );
        $marketing_channel_options = array('Web site / Online Content', 'Search Engine Marketing', 'Email Marketing', 'Software',
            'Affiliate Network', 'Mobile Network', 'Social Network', 'Other'
            );        
        
        if(!empty($_REQUEST['submit'])) {
           
            $service = AccountsService::getInstance();
            $service->setMethod('associate_update_profile');
            $service->setParams(array(
                'user_id' => $user_id,
                'associate_id' => $associate_id,
                'pinterest_account_id' => $pinterest_account_id,
                'pinterest_username' => $_REQUEST['pinterest_username'],
                'external_website' => !empty($_REQUEST['external_website_url']) ? $_REQUEST['external_website_url'] : $external_website_url,
                'external_website_name' => !empty($_REQUEST['external_website_name']) ? $_REQUEST['external_website_name'] : $external_website_name,
                'external_website_content' => $_REQUEST['external_website_content'],
                'external_website_description' => $_REQUEST['external_website_description'],
                'external_website_monthly_unique_visitors' => $_REQUEST['external_website_monthly_unique_visitors'],
                'marketing_channel' => $_REQUEST['marketing_channel'],
                'account_dbobj' => $this->account_dbobj,
            ));
            
            $service->call();
            $status = $service->getStatus(); 
            
            if($status === 0) {
                
                $associate_status = $redis->get("associate:$associate_id:status");
                
                if(empty($associate_status)) {
                    if(empty($payment_account_id)) {
                        redirect('/account/payments');                       
                    }
                    redirect('/me/settings');
                } 
                redirect('/associate/search');
		
            } else {
                $this->view->errnos = $service->getErrnos();
            }
        }
        $this->view->external_website_content_options = $external_website_content_options;
        $this->view->marketing_channel_options = $marketing_channel_options;
        $this->view->external_website_url = $external_website_url;
        $this->view->external_website_name = $external_website_name;        
        $this->view->account_status = $account_status;   
        $this->view->products_cnt = $products_cnt;
        $this->view->pinterest_account_id = $redis->get("associate:$associate_id:pinterest_account_id");
        $this->view->pinterest_username = $redis->get("pinterest_account:$pinterest_account_id:username");
        $this->view->aid = $redis->get("associate:$associate_id:aid");
        $this->view->external_website_content = $redis->get("associate:$associate_id:external_website_content");
        $this->view->external_website_description = $redis->get("associate:$associate_id:external_website_description");
        $this->view->external_website_monthly_unique_visitors = $redis->get("associate:$associate_id:external_website_monthly_unique_visitors");
        $this->view->marketing_channel = $redis->get("associate:$associate_id:marketing_channel");   

    }
    
    public function productsAction() {

        global $redis;
        $associate_id = $this->user_session->associate_id;
        $aid = $redis->get("associate:$associate_id:aid");
        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page'])? (int)$_REQUEST['page'] : 1;
        $price_range = array('100','200','300','400','500');
        $commission_range = array('10','20','30','40','50');
        
        $description = !empty($_REQUEST['description']) ? trim($_REQUEST['description']) : '';
        $price_end = !empty($_REQUEST['price']) ? (int)$_REQUEST['price'] : 0;
        $commission_end = !empty($_REQUEST['commission']) ? (int)$_REQUEST['commission'] : 0;
        $price_start = $price_end - 100;
        $commission_start = $commission_end - 10;
        
        $response = SalesMapper::getProducts($associate_id, $account_dbobj, $description, $commission_start, $commission_end, $price_start, $price_end, $page_num);
        $products_cnt = SalesMapper::getProductCnt($associate_id, $account_dbobj, $description, $commission_start, $commission_end, $price_start, $price_end, $page_num);
        
        $this->view->description = $description;
        $this->view->price_end = $price_end;
        $this->view->commission_end = $commission_end;
        $this->view->price_range = $price_range;
        $this->view->commission_range = $commission_range;        
        $this->view->sales_products = $response;
        $this->view->aid = $aid;
        $this->view->searchd_products = $response;        
        $this->view->total_rows = $products_cnt;
        $this->view->rows_per_page = SALESNETWORK_PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;   
        $this->view->extra_params = array(
            'description' => $description,
            'price' => $price_end,
            'commission' => $commission_end
        );        
    }

    public function searchAction() {
        
        $associate_id = $this->user_session->associate_id;      
        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page'])? (int)$_REQUEST['page'] : 1;
        $price_range = array('100','200','300','400','500');
        $commission_range = array('10','20','30','40','50');
        
        $description = !empty($_REQUEST['description']) ? trim($_REQUEST['description']) : '';
        $price_end = !empty($_REQUEST['price']) ? (int)$_REQUEST['price'] : 0;
        $commission_end = !empty($_REQUEST['commission']) ? (int)$_REQUEST['commission'] : 0;
        $price_start = $price_end - 100;
        $commission_start = $commission_end - 10;
           
        $response = SearchProductsMapper::searchProducts($associate_id, $account_dbobj, $description, $commission_start, $commission_end, $price_start, $price_end, $page_num);
        $count = SearchProductsMapper::searchProductCnt($associate_id, $account_dbobj, $description, $commission_start, $commission_end, $price_start, $price_end);

        $this->view->description = $description;
        $this->view->price_end = $price_end;
        $this->view->commission_end = $commission_end;
        $this->view->price_range = $price_range;
        $this->view->commission_range = $commission_range;
        $this->view->searchd_products = $response;        
        $this->view->total_rows = $count;
        $this->view->rows_per_page = SALESNETWORK_PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;  
        $this->view->extra_params = array(
            'description' => $description,
            'price' => $price_end,
            'commission' => $commission_end
        );
    }
    
    public function salesAction() {
        global $redis;
        $associate_id = $this->user_session->associate_id;      
        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page'])? (int)$_REQUEST['page'] : 1;
        $aid = $redis->get("associate:$associate_id:aid");
       
        $service= SalesNetworkService::getInstance();
        $service->setMethod('getSalesReport');
        $service->setParams(array('associate_id'=>$associate_id,
            'account_dbobj'=>$account_dbobj,
            'page_num'=>$page_num
        ));
        $service->call();
        $status = $service->getStatus();

        if($status === 0) {
            $response = $service->getResponse();
            $count = (int)SalesMapper::getSalesCnt($associate_id, $account_dbobj);
        }
        
        $this->view->sales_products = $response;
        $this->view->aid = $aid;
        $this->view->total_rows = $count;
        $this->view->rows_per_page = SALESNETWORK_PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;
    }
    
    public function verifyAction() {
        
        if(empty($_REQUEST['code'])) {
            redirect(getURL());
        }
        
        $service = AssociateService::getInstance();
        $service->setMethod('verify');
        $service->setParams(array(
            'code' => $_REQUEST['code'],
            'account_dbobj' => $this->account_dbobj
        ));
        $service->call();
        $this->view->status = $service->getStatus();
        if($this->is_associate()) {
            $this->view->next_page = '/associate/profile';
        } else {
            $this->view->next_page = '/associate/login';
        }        
    } 
 
    public function fqasAction(){ }
}   

