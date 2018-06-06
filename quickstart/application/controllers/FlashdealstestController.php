<?php

class FlashdealstestController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }
    
    public function indexAction() {
        if(!empty($_REQUEST['payment_submit']) && !empty($_REQUEST['coupon_code'])) {
            $coupon_code = $_REQUEST['coupon_code'];
            $coupon = new Coupon($this->account_dbobj);
            $coupon->findOne("code='".$this->account_dbobj->escape($coupon_code)."'".
                    ' and status='.ACTIVATED.
                    ' and scope='.PRODUCT.
                    ' and (price_offer_type='.PERCENTAGE_OFF.' or shipping_offer_type='.PERCENTAGE_OFF.')'.
                    ' and usage_limit > 0'.
                    ' and store_id !=0'.
                    ' and product_id !=0'.
                    ' and now()>start_time'.
                    ' and now()<end_time');
            if($coupon->getId() !== 0) {
                $service = PaypalService::getInstance();
                $service->setMethod('setup_flashdeal_payment');
                $service->setParams(array(
                    'coupon' => $coupon,
                    'account_dbobj' => $this->account_dbobj
                ));
                $service->call();
                
                if($service->getStatus() === 0) {
                    $response = $service->getResponse();
                    $this->shopper_session->order->order_id = $response['order_id'];
                    $this->shopper_session->order->products = $response['products'];
                    $this->shopper_session->order->total = $response['total'];
                    $this->shopper_session->order->price = $response['price'];
                    $this->shopper_session->order->tax = $response['tax'];
                    $this->shopper_session->order->shipping = $response['shipping'];
                    $this->shopper_session->order->total_quantity = $response['total_quantity'];
                    $this->shopper_session->order->paypal->token = $response['paypal_token'];
                    $this->shopper_session->order->service_paypal_username = $response['service_paypal_username'];
                    $this->shopper_session->order->paypal->payform = $response['payform'];
                    $this->shopper_session->order->merchant_paypal_username = $response['merchant_paypal_username'];
                    $this->shopper_session->order->coupon = $coupon;

                    Log::write(INFO, 'SESSION: '.json_encode($_SESSION));
                    
                    redirect($response['paypal_login_url'] );
                }
            }
        }
    }
        

}   

