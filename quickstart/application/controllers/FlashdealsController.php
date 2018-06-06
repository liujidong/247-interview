<?php

class FlashdealsController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function indexAction() {
        
        $account_dbobj = $this->account_dbobj;
        if(isset($_REQUEST['payment_submit']) && !empty($_REQUEST['coupon_code'])) {
            $coupon_code = $_REQUEST['coupon_code'];
            $coupon = new Coupon($account_dbobj);
            $coupon->findOne("code='".$account_dbobj->escape($coupon_code)."'".
                    ' and (status='.ACTIVATED.' or status='.CREATED.')'.
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
                    'account_dbobj' => $account_dbobj
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
                    $this->shopper_session->order->merchant_username = $response['merchant_username'];
                    $this->shopper_session->order->coupon = $coupon;
                    $this->shopper_session->payform = $response['payform'];

                    Log::write(INFO, 'SESSION: '.json_encode($_SESSION));
                    
                    redirect($response['paypal_login_url'] );
                }
            }
        }
        
        if(!empty($_REQUEST['submit'])) {
            $shopper_username = $_REQUEST['shopper_username'];
            $shopper = new Shopper($account_dbobj);
            if($shopper->setUsername($shopper_username)) {
                $shopper->findOne("username='".$account_dbobj->escape($shopper_username)."'");
                $shopper->setUsername($shopper_username);
                $shopper->setReferredBy('flashdeals');
                $shopper->save();
                $this->view->thankyou = true;
            }
        }
        
        $deals = CouponsMapper::getActiveDeals($account_dbobj, array(ACTIVATED), 1);

        foreach($deals as $id => $deal){
            $store_id = $deal['store_id'];
            $product_id = $deal['product_id'];
            $store_obj = new Store($account_dbobj);
            $store_obj->findOne('id='.$store_id);
            $store_host = $store_obj->getHost();
            $store_name = $store_obj->getName();
            $store_dbobj = DBObj::getStoreDBObj($store_host,$store_id);
            $product_obj = new Product($store_dbobj);
            $product_obj->findOne('id='.$product_id.' and status!='.DELETED);
            $product_name = $product_obj->getName();
            $product_description = $product_obj->getDescription();
            $product_regular_price = $product_obj->getPrice();
            $product_pictures = ProductsMapper::getPictureByProductId($product_id, $store_dbobj);
            $offer_details = $deal['offer_details'];
            //clean the start and end double quote
            $offer_details = trim($offer_details,'"');
            
            $off = json_decode(str_replace('\"', '"', $offer_details), true);
            
            $subdomain = $store_obj->getSubdomain();
            $store_url = getStoreUrl($subdomain);
            $store_logo = $store_obj->getLogo();
            $store_shipping = $store_obj->getShipping();
            $product_shipping = $product_obj->getShipping();
            $shipping = $store_shipping+$product_shipping;
            $price = $product_regular_price;
            
            if(!empty($off['shipping']['percentage_off'])){    
                $shipping = number_format(floatval($shipping)*floatval(100-$off['shipping']['percentage_off'])/100,2);
            }
            if(!empty($off['price']['percentage_off'])){    
                $price = number_format(floatval($product_regular_price)*floatval((100-$off['price']['percentage_off']))/100,2);
                $deals[$id]['percentage_off'] = $off['price']['percentage_off'];
            }
            else
                $deals[$id]['percentage_off'] = 0;
            
            $deals[$id]['product_name'] = $product_name;
            $deals[$id]['product_description'] = $product_description;
            $deals[$id]['product_regular_price'] = $product_regular_price;
            $deals[$id]['product_price'] = $price;
            $deals[$id]['store_name'] = $store_name;
            $deals[$id]['product_pics'] = $product_pictures;
            $deals[$id]['product_url'] = $store_url."/products/item?id=".$product_id;
            $deals[$id]['store_url'] = $store_url;
            $deals[$id]['store_logo'] = $store_logo;
            $deals[$id]['product_shipping'] = $shipping;
            $deals[$id]['currency_symbol'] = currency_symbol($deal['currency']);
            
            // ways what to/how to show deals: grayed out/button text
            if($deal['usage_limit'] <= 0) {
                $deals[$id]['grayed_out'] = true;
                $deals[$id]['button_text'] = 'SOLD OUT';
                $deals[$id]['button_disabled'] = true;
                $deals[$id]['priority'] = 20;
            } else if(strtotime2($deal['end_time'])<strtotime2(get_current_datetime()) || $product_obj->getId() === 0) {
                $deals[$id]['grayed_out'] = true;
                $deals[$id]['button_text'] = 'Sale Ends';
                $deals[$id]['button_disabled'] = true;
                $deals[$id]['priority'] = 10;
            } else if(strtotime2($deal['start_time'])>strtotime2(get_current_datetime())) {
                $deals[$id]['grayed_out'] = false;
                $deals[$id]['button_text'] = 'Sale starts '.date('ga Mj T', strtotime($deal['start_time']));
                $deals[$id]['button_disabled'] = true;
                $deals[$id]['priority'] = 30;
            } else {
                $deals[$id]['grayed_out'] = false;
                $deals[$id]['button_text'] = 'Get it Now';
                $deals[$id]['button_disabled'] = false;
                $deals[$id]['priority'] = 40;
            }
        }
        $deals = array_sort($deals, 'priority');
        $this->view->deals = $deals;
    }
        

}   

