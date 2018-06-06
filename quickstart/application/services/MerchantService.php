<?php

class MerchantService extends BaseService {
    
    
    // input: merchant_id, paypal_account_id, paypal_username, account_dbobj
    public function add_payments() {
        
        $merchant_id = $this->params['merchant_id'];
        $paypal_username = $this->params['paypal_username'];
        $account_dbobj = $this->params['account_dbobj'];
        $paypal_account_id = $this->params['paypal_account_id'];
        
        // validate paypal username
        $paypal = new PaypalAccount($account_dbobj);
        $paypal->findOne("username='".$account_dbobj->escape($paypal_username)."'");
        if($paypal->getId() === 0) {
            if(!$paypal->setUsername($paypal_username)) {
                $this->errnos[INVALID_PAYPAL_ACCOUNT] = 1;
                $this->status = 1;
                return;
            }
        }
        
        $merchant = new Merchant($account_dbobj);
        $merchant->findOne('id='.$merchant_id);
        
        // delete the association bw merchants and paypal_accounts first
        if(!empty($paypal_account_id)) {
            $paypal_account = new PaypalAccount($account_dbobj);
            $paypal_account->findOne('id='.$paypal_account_id);
            BaseMapper::deleteAssociation($merchant, $paypal_account, $account_dbobj);
            Log::write(INFO, 'Deleted the association bw merchants and paypal accounts '.$merchant_id.' '.$paypal_account_id);
        }
        // create a paypal account
        $paypal->save();
        Log::write(INFO, 'Created a paypal account '.$paypal->getId().' '.$paypal_username);
        // create the association bw merchants and paypal_accounts
        BaseMapper::saveAssociation($merchant, $paypal, $account_dbobj);
        Log::write(INFO, 'Created association bw merchants and paypal accounts '.$merchant_id.' '.$paypal->getId());
        
        $this->response['paypal_account_id'] = $paypal->getId();
        $this->response['paypal_username'] = $paypal->getUsername();
        
        $this->status = 0;
        
        
    }
    
    // input: profile, merchant_id, account_dbobj
    public function fb_connect() {
        
        $profile = $this->params['profile'];
        $merchant_id = $this->params['merchant_id'];
        $account_dbobj = $this->params['account_dbobj'];
        
        $fb_user = new FbUser($account_dbobj);
        $fb_user->findOne('id='.$merchant_id);
        $fb_user->setAbount($pofile['about']);
        $fb_user->setBio($profile['bio']);
        $fb_user->setBirthday($profile['birthday']);
        $fb_user->setEducation($profile['education']);
        $fb_user->setEmail($profile['email']);
        $fb_user->setFirstName($profile['first_name']);
        $fb_user->setGender($profile['gender']);
        $fb_user->setExternalId($profile['external_id']);
        $fb_user->setLastName($profile['last_name']);
        $fb_user->setLink($profile['link']);
        $fb_user->setLocale($profile['locale']);
        $fb_user->setName($profile['name']);
        $fb_user->setQuotes($profile['quotes']);
        $fb_user->setTimezone($profile['timezone']);
        $fb_user->setUpdatedTime($profile['setUpdatedTime']);
        $fb_user->setUsername($profile['username']);
        $fb_user->setVerified($profile['verified']);
        $fb_user->setWork($profile['work']);
        $fb_user->save();
        
        $merchant = new Merchant($account_dbobj);
        BaseMapper::saveAssociation($merchant, $fb_user, $account_dbobj);
        
        $this->status = 0;
    }
    
    // input: profile, merchant_id, account_dbobj
    public function twitter_connect() {
        $profile = $this->params['profile'];
        $merchant_id = $this->params['merchant_id'];
        $account_dbobj = $this->params['account_dbobj'];
        
        $twitter_user = new TwitterUser($account_dbobj);
        $twitter_user->findOne('id='.$merchant_id);
        $twitter_user->setProfileImageUrl($profile['profile_image_url']);
        $twitter_user->setUrl($profile['url']);
        $twitter_user->setLocation($profile['location']);
        $twitter_user->setName($profile['name']);
        $twitter_user->setScreenName($profile['screen_name']);
        $twitter_user->setId($profile['id']);
        $twitter_user->save();
        
        $merchant = new Merchant($account_dbobj);
        BaseMapper::saveAssociation($merchant, $twitter_user, $account_dbobj);
        
        $this->status = 0;
    }
    
    // input: account_dbobj, store_dbobj
    public function get_orders() {
        $account_dbobj = $this->params['account_dbobj'];
        $store_dbobj = $this->params['store_dbobj'];
        
        $orders = OrdersMapper::getOrders($store_dbobj);
        $orders_count = sizeof($orders);
        for($i=0;$i<$orders_count;$i++) {
            $user_id = $orders[$i]['user_id'];
            $user = new User($account_dbobj);
            $user->findOne('id='.$user_id);
            $user_username = $user->getUsername();
            $orders[$i]['shopper_username'] = $user_username;
            if(empty($orders[$i]['payment_status'])) {
                $orders[$i]['payment_status'] = 'UNKNOWN';
            }
            $orders[$i]['status'] = get_fulfillment_status($orders[$i]['status']);
        }
        $this->response = $orders;
    }
    
    // input: order_id, account_dbobj, store_dbobj
    public function get_order_details() {
        $order_id = $this->params['order_id'];
        $account_dbobj = $this->params['account_dbobj'];
        $store_dbobj = $this->params['store_dbobj'];
        
        $order_details = OrdersMapper::getOrderDetails($order_id, $store_dbobj);
        
        $order_status = $order_details[0]['order_status'];
        $address_id = $order_details[0]['order_to_address_id'];
        $currency_code = $order_details[0]['currency_code'];
        $address = new Address($account_dbobj);
        $address->findOne('id='.$address_id);

        $this->response['currency_code'] = $currency_code;
        $this->response['order_details'] = $order_details;
        $this->response['address'] = $address;
        $this->response['order_status'] = $order_status;
    }
    
    // input: code, account_dbobj
    public function verify() {
        $code = $this->params['code'];
        $account_dbobj = $this->params['account_dbobj'];
        
        $id_hash = explode('_', $code);
        if(sizeof($id_hash) !== 2) {
            $this->status = 1;
            $this->errnos['INVALID_VERIFICATION_CODE'] = 1;
            return;
        }
        $id = $id_hash[0];
        $hash = $id_hash[1];
        
        $merchant = new Merchant($account_dbobj);
        $merchant->findOne('id='.$id);
        if($merchant->getId() === 0) {
            $this->status = 1;
            $this->errnos['INVALID_VERIFICATION_CODE'] = 1;
            return;
        }
        $email = $merchant->getUsername();
        if($hash !== md5($email)) {
            $this->status = 1;
            $this->errnos['INVALID_VERIFICATION_CODE'] = 1;
            return;
        }
        $this->status = 0;
        $merchant->setEmailVerified(VERIFIED);
        $merchant->save();
    }
    
    // input: 
    // - contacts
    //    Array
    //    (
    //        [0] => stdClass Object
    //            (
    //                [address] => Array
    //                    (
    //                    )
    //
    //                [last_name] => Auerbach
    //                [phone] => Array
    //                    (
    //                    )
    //
    //                [first_name] => Steve
    //                [email] => Array
    //                    (
    //                        [0] => stdClass Object
    //                            (
    //                                [address] => sauerbach@peakhosting.com
    //                                [type] => 
    //                                [selected] => 1
    //                            )
    //
    //                    )
    //
    //            )
    //      ...
    //
    //    )
    // - store_dbobj
    // - output:
    // status: 0/1
    // 
    public function save_contacts(){
        $store_dbobj = $this->params['store_dbobj'];
        $contacts = $this->params['contacts'];

        $i = 0;
        foreach($contacts as $contact) {
            
            // check if the customer already exists
            $customer_id = 0;
            foreach($contact['email'] as $email) {
                $customer_id = CustEmailMapper::getCustomerIdByEmail($email['address'], $store_dbobj);
                if($customer_id !== 0) {
                    break;
                }
            }
            if($customer_id === 0) {
                // save first_name, last_name
                $customer = new Customer($store_dbobj);
                $customer->setFirstName($contact['first_name']);
                $customer->setLastName($contact['last_name']);
                $customer->save();
                $this->response[$i]['first_name'] = $customer->getFirstName();
                $this->response[$i]['last_name'] = $customer->getLastName();
                $this->response[$i]['id'] = $customer->getId();
            } else {
                // no need to import this contact because it has existed
                continue;
            }   
            
             
            // save emails
            $j = 0;
            foreach($contact['email'] as $email) {
                $cust_email = new CustEmail($store_dbobj);
                $cust_email->findOne("email='".$store_dbobj->escape($email['address'])."'");
                if($cust_email->getId() === 0) {
                    // save a new email
                    if($cust_email->setEmail($email['address'])) {
                        $cust_email->setType($email['type']);
                        $cust_email->save();
                    } else {
                        // if the email if not valid, skip it
                        continue;
                    }
                }
                // save the association
                BaseMapper::saveAssociation($customer, $cust_email, $store_dbobj);
                
//                $this->response[$i]['emails'][$j]['email'] = $cust_email->getEmail();
//                $this->response[$i]['emails'][$j]['type'] = $cust_email->getType();
//                $j++;
                if($j === 0) {
                    $this->response[$i]['email'] = $cust_email->getEmail();
                }
            }
            
            // save phones
            $k = 0;
            foreach($contact['phone'] as $phone) {
                $cust_phone = new CustPhone($store_dbobj);
                $cust_phone->findOne("number='".$store->dbobj->escape($phone['number'])."'");
                if($cust_phone->getId() === 0) {
                    // save a new phone
                    if($cust_phone->setNumber($$phone['number'])) {
                        $cust_phone->setType($phone['type']);
                        $cust_phone->save();
                    } else {
                        // if the phone if not valid, skip it
                        continue;
                    }
                }
                // save the association
                BaseMapper::saveAssociation($customer, $cust_phone, $store_dbobj);
                
//                $this->response[$i]['phones'][$k]['number'] = $cust_phone->getNumber();
//                $this->response[$i]['phones'][$k]['type'] = $cust_phone->getType();
//                $k++;
            }
            
            // save addresses
//            $m = 0;
            foreach($contact['address'] as $address) {
                $cust_address = new CustAddress($store_dbobj);
                $cust_address->setFormatted($address['formatted']);
                $cust_address->setStreet($address['street']);
                $cust_address->setCity($address['city']);
                $cust_address->setRegion($address['region']);
                $cust_address->setCountry($address['country']);
                $cust_address->setType($address['type']);
                $cust_address->save();
                
                // save the association
                BaseMapper::saveAssociation($customer, $cust_address, $store_dbobj);
                
//                $this->response[$i]['addresses'][$m]['formatted'] = $cust_address->getFormatted();
//                $this->response[$i]['addresses'][$m]['street'] = $cust_address->getStreet();
//                $this->response[$i]['addresses'][$m]['city'] = $cust_address->getCity();
//                $this->response[$i]['addresses'][$m]['region'] = $cust_address->getRegion();
//                $this->response[$i]['addresses'][$m]['country'] = $cust_address->getCountry();
//                $this->response[$i]['addresses'][$m]['type'] = $cust_address->getType();
//                $m++;
            }
            $i++;
        } 
        $this->status = 0;
        
    }
    

    // input: toemail, subject, content
    public function send_email() {
        $toemail = $this->params['toemail'];
        $subject = $this->params['subject'];
        $content = $this->params['content'];
    }
    
}


