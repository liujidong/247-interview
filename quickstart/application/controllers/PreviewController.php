<?php

class PreviewController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function indexAction() {
        
        global $redis;
        
        $user_id = $this->user_session->user_id;
        $merchant_id = $this->user_session->merchant_id;
        $account_dbobj = $this->account_dbobj;
        $store_id = $redis->get("merchant:$merchant_id:store_id");
        $store_dbobj = $this->store_dbobj;
        $store_subdomain = $redis->get("store:$store_id:subdomain");
        $store_url = getStoreUrl($store_subdomain);
        $store_name = $redis->get("store:$store_id:name");
        $store_logo = $redis->get("store:$store_id:logo");
        
        $return_products =  ProductsMapper::getProducts($store_dbobj, 1, '', 0, 'active', CONVERTED192);   
        
        $products = array();
        for($i=0;$i<PRODUCT_NUM_PER_PAGE;$i++){
           $p = array_shift($return_products);
           if(empty($p))
               break;
           if($i % 3 == 0)
               $products[0][] = $p;
           if($i % 3 == 1)
               $products[1][] = $p;
           if($i % 3 == 2)
               $products[2][]= $p;
        }
        
        $this->view->products = $products;
        
        $store_service = new StoreService();
        $store_service->setMethod('storelaunchable');
        $store_service->setParams(array(
            'store_id' => $store_id,
            'user_id' => $user_id,            
            'store_dbobj'  => $store_dbobj,
            'account_dbobj' => $account_dbobj            
        ));
        $store_service->call();
        $status = $store_service->getStatus();
        if($status === 1) {           
            $errnos = array_keys($store_service->getErrnos());
            $this->view->errnos = array_merge($this->view->errnos, $errnos);
        }      
        
        if(isset($_GET['launch']) && $_GET['launch'] == 1 && empty($errnos)){
            $store_obj = new Store($account_dbobj);
            $store_obj->findOne('id='.$store_id);
            $store_obj->setStatus(ACTIVATED);
            $store_obj->save();

            // update cache
            $redis->set("store:$store_id:status", ACTIVATED);
        }
        if((isset($_GET['unlaunch']) && $_GET['unlaunch'] == 1) ||!empty($errnos)){
            $store_obj = new Store($account_dbobj);
            $store_obj->findOne('id='.$store_id);
            $store_obj->setStatus(PENDING);
            $store_obj->setOptinSalesnetwork(CREATED);
            $store_obj->setPaymentSolution(PROVIDER_PAYPAL);                
            $store_obj->save();

            //update cache
            $redis->set("store:$store_id:status", PENDING); 
            $redis->set("store:$store_id:optin_salesnetwork", CREATED);
            $redis->set("store:$store_id:payment_solution", PROVIDER_PAYPAL);            
            
        }
        $this->view->store_name = $store_name;
        $this->view->store_url = empty($store_url) ? '' : $store_url;
        $this->view->store_logo = $store_logo;
        $this->view->store_status = $redis->get("store:$store_id:status");
    }
        

}   

