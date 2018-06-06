<?php

class FeedbackController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function indexAction() {
        $request = $this->getRequest();
        $store_dbobj = $this->store_dbobj;
        
        $account_dbobj = $this->account_dbobj;
        if(isset($_REQUEST['submit'])){
            
            $post_data = $request->getPost();
            $review_score = $post_data['review_score'];
            $review_content = $post_data['review_content'];
            $order_id = $post_data['order_id'];
            $shopper_id = $post_data['shopper_id'];
            
            $service = FeedbackService::getInstance();
            $service->setMethod('createReview');
            $service->setParams(array ('store_dbobj' => $store_dbobj, 'shopper_id' => $shopper_id, 
                                        'review_score' => $review_score, 'review_content' => $review_content, 'order_id' => $order_id));
            if(!empty($order_id) && !empty($shopper_id)){
                $service->call();
                $errnos = $service->getErrnos();
                if(empty($errnos)){
                    $this->view->submit = true;
                }
                else{
                    $this->view->error = $GLOBALS['errors'][INVALID_FEEDBACK_ERROR]['msg'];
                    $this->view->shopper_id = $shopper_id;
                    $this->view->order_id = $order_id;
                }  
            }
            
            
        }else{
            $queryString = $this->getRequest()->getServer('QUERY_STRING');
            
            $order_info = explode('=', base64_decode($queryString));
            
            if($order_info[0] == 'order_id' && isset($order_info[1]) && is_numeric($order_info[1])) // data validation 
            {    
                
                $order_id = $order_info[1];
                $shopper_info = ShoppersMapper::getShopperByOrder($store_dbobj, $order_id);
                $shopper_id =  $shopper_info['shopper_id'];
                $this->view->shopper_id = $shopper_id;
                $this->view->order_id = $order_id;
                
                $review_obj = new Review($store_dbobj);
                $review_obj->findOne('order_id='.$order_id);
                if($review_obj->getId()){
                    $this->view->reviewed = true;
                }
            }
            
        }
        
        $store_rating = StoresMapper::getStoreRating($store_dbobj);
        $service = FeedbackService::getInstance();
        $service->setMethod('getReview');
        $service->setParams(array ('store_dbobj' => $store_dbobj ) );
        $service->call();
        $reviews = $service->getResponse();
        $this->view->reviews = $reviews;
        $this->view->store_rating = $store_rating;
        $this->view->store_id = $this->shopper_session->store_id;
        $this->view->store_name = $this->shopper_session->store_name;
        $this->view->store_logo = $this->shopper_session->store_logo;
        $this->view->store_description = nl2br($this->shopper_session->store_description);
        $this->view->store_external_website = preg_replace('!\b((https?|http)://)\b!', '', $this->shopper_session->store_external_website);
        $this->view->store_return_policy = nl2br($this->shopper_session->store_return_policy);
        
    }
        
}   

