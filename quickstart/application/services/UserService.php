<?php

class UserService extends BaseService {

    public static $user_categories = null;
    public static $email_tags = null;
    public static $user_category_queries = null;

    public static function getDataForEmail($email){
        global $dbconfig;
        $data = array(
            'user_email' => $email,
            'user_password' => '******',
            'site_url' => getSiteMerchantUrl(),
            'site_logo' => getSiteMerchantUrl(SHOPINTEREST_LOGO),
        );
        $user = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name.'.user?username=' . $email));
        if(empty($user)) return $data;
        $data['user_first_name'] = $user['first_name'];
        $data['user_last_name'] = $user['last_name'];
        $data['user_verification_link'] = get_verification_url($user['id'], $email);
        //$data[''] = $user[''];
        if(empty($user['store_id'])) return $data;
        $store = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name.'.store?id=' . $user['store_id']));
        $data['store_url'] = getStoreUrl($store['subdomain']);
        $data['store_logo'] = $store['converted_logo'];
        $data['store_name'] = $store['name'];
        $data['store_description'] = $store['description'];
        return $data;
    }

    // input: user_id, payment_account_id, paypal_username, account_dbobj
    public function add_payments() {

        global $redis;

        $user_id = $this->params['user_id'];
        $associate_id = !empty($this->params['associate_id']) ? $this->params['associate_id'] : 0;
        $payment_account_id = $this->params['payment_account_id'];
        $paypal_username = $this->params['paypal_username'];
        $account_dbobj = $this->params['account_dbobj'];

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
        $paypal->save();

        $payment_account = new PaymentAccount($account_dbobj);
        if(!empty($payment_account_id)) {
            $payment_account->findOne('id='.$payment_account_id);
        }
        $payment_account->setPaypalAccountId($paypal->getId());
        $payment_account->save();

        $user = new User($account_dbobj);
        $user->findOne('id='.$user_id);
        BaseMapper::saveAssociation($user, $payment_account, $account_dbobj);

        if(!empty($associate_id) && $is_active_associate = AssociatesMapper::is_active_associate($associate_id, $account_dbobj)) {
            $associate = new Associate($account_dbobj);
            $associate->findOne('id='.$associate_id);
            $associate->setStatus(ACTIVATED);
            $associate->save();
            $redis->set("associate::$associate_id:status", $associate->getStatus());
        }

        $this->response['paypal_account_id'] = $paypal->getId();
        $this->response['paypal_username'] = $paypal->getUsername();

        $this->status = 0;

        // warm up cache
        $redis->set("user:$user_id:payment_account_id", $payment_account->getId());
        $redis->set("payment_account:{$payment_account->getId()}:paypal_account_id",$paypal->getId());
        $paypal_account_id = $paypal->getId();
        $redis->set("paypal_account:$paypal_account_id:username", $paypal->getUsername());
        $redis->set("paypal_account:$paypal_account_id:status", $paypal->getStatus());
        $redis->set("paypal_account:$paypal_account_id:created", $paypal->getCreated());
        $redis->set("paypal_account:$paypal_account_id:updated", $paypal->getUpdated());


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
            $shopper_id = $orders[$i]['shopper_id'];
            $shopper = new Shopper($account_dbobj);
            $shopper->findOne('id='.$shopper_id);
            $shopper_username = $shopper->getUsername();
            $orders[$i]['shopper_username'] = $shopper_username;
            if(empty($orders[$i]['payment_status'])) {
                $orders[$i]['payment_status'] = 'UNKNOWN';
            }
            $orders[$i]['status'] = get_fulfillment_status($orders[$i]['status']);
        }
        $this->response = $orders;
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

    public function admin_send_email(){
        global $shopinterest_config;
        global $admin_account;

        $email_subject = $this->params['email_subject'];
        $email_content = $this->params['email_content'];
        $user_category = $this->params['user_category'];
        $account_dbobj = $this->params['account_dbobj'];
        $job_dbobj = $this->params['job_dbobj'];

        if(isset($this->params['email_template'])){
            $ck = CacheKey::q($GLOBALS['account_dbname'].'.email_template?type=' . $this->params['email_template']);
            $tpl = BaseModel::findCachedOne($ck);
            if(!empty($tpl)){
                $email_subject = $tpl['subject'];
                $email_content = $tpl['content'];
                if(isset($tpl['header']) && !empty($tpl['header'])){
                    $header_ck = CacheKey::q($GLOBALS['account_dbname'].'.email_template?type=' . $tpl['header']);
                    $header_tpl = BaseModel::findCachedOne($header_ck);
                    if(!empty($header_tpl)){
                        $email_content = $header_tpl['content'] . "\n" . $email_content;
                    }
                }
                if(isset($tpl['footer']) && !empty($tpl['footer'])){
                    $footer_ck = CacheKey::q($GLOBALS['account_dbname'].'.email_template?type=' . $tpl['footer']);
                    $footer_tpl = BaseModel::findCachedOne($footer_ck);
                    if(!empty($footer_tpl)){
                        $email_content = $email_content . "\n" . $footer_tpl['content'];
                    }
                }
            }
        }

        $email_query = NULL;
        if(isset(self::$user_categories[$user_category])){
            $email_query = self::$user_category_queries[$user_category];
        } else if(isset(self::$email_tags[$user_category])){
            if($user_category == "all_tags"){
                $email_query = "select
                                id, email as username, first_name, last_name
                                from emails";
            } else {
                $email_query = "select
                                id, email as username, first_name, last_name
                                from emails where tags like '%" . $user_category .  "%'";
            }
        }
        $records = null;
        if(empty($email_query)) {
            $records = array();
            foreach($admin_account as $user) {
                $records[] = array(
                    'id' => 0,
                    'username' => $user,
                    'first_name' => 'Admin',
                    'last_name' => ''
                );
            }
        } else {
            $records = BaseMapper::select_query($email_query, $account_dbobj);
        }
        $service = new EmailService();
        $total_count = 0;
        $service->setMethod('create_job');
        foreach($records as $record ) {
            $total_count++;
            $email_address = $record['username'];
            $data = self::getDataForEmail($email_address);
            $r_email_subject = substitute($email_subject, $data);
            $r_email_content = substitute($email_content, $data);
            $toname = empty($record['first_name'])?'':$record['first_name'];
            $service->setParams(array(
                    'to' => $email_address,
                    'from' => $shopinterest_config->support->email,
                    'subject' => $r_email_subject,
                    'text' => $r_email_content,
                    'toname' => $toname,
                    'fromname' => $shopinterest_config->support->email_from_name,
                    'job_dbobj' => $job_dbobj
            ));
            $service->call();
        }
        $this->response['total_emails'] = $total_count;
    }

    // status : BLOCKED, ACTIVATED
    public function update_account() {
        global $dbconfig;

        $user_id = $this->params['user_id'];
        $status = isset($this->params['status']) ? $this->params['status'] : BLOCKED;
        $account_dbobj = $this->params['account_dbobj'];
        $user_status = BLOCKED;
        $store_status = PENDING;

        if($status === ACTIVATED) {
            $user_status = CREATED;
            $store_status = PENDING;
        }

        // update users table
        $user_obj = new User($account_dbobj, $user_id);
        $user_obj->setStatus($status);
        $user_obj->save();

        $user = BaseModel::findCachedOne($dbconfig->account->name . ".user?id=$user_id");
        if(!empty($user['store_id'])){
            $store_id = $user['store_id'];
            // store need to manually launch
            $store_obj = new Store($account_dbobj, $store_id);
            if($store_obj->getId()) {
                $store_obj->setStatus($store_status);
                $store_obj->save();
                if($store_status != ACTIVATED){
                    GlobalProductsMapper::deleteProductsInStore($account_dbobj, $store_id);
                }
            }
        }
    }
}

UserService::$user_categories = array(
    'TEST_ACCOUNTS' => "Test Accounts",
    'ALL_MERCHANTS' => "All Merchants",
    'ALL_SHOPPERS' => "All Shoppers",
    'MERCHANTS_WITH_STORE_ACTIVE' => "Merchants whose stores status are active",
    'MERCHANTS_WITH_STORE_PENDING' => "Merchants whose stores status are pending",
    'MERCHANTS_WITH_STORE_CREATED' => "Merchants whose stores status are created",
    'MERCHANTS_WITH_STORE_DELETED' => "Merchants whose stores status are deleted",
    'ALL_USERS' => "All Users",
);

UserService::$email_tags = array(
    'all_tags' => "TAG: All Emails",
    'shopintoit_user' => "TAG: All shopintoit users",
    'shopintoit_merchant' => "TAG: All shopintoit merchant",
    'shopintoit_shopper' => "TAG: All shopintoit shopper",
    'pintics_user' => "TAG: All pintics email",
    'paypal' => "TAG: All Paypal Accounts",
    'ga_account' => "TAG: All GA Accounts",
);

UserService::$user_category_queries = array(
    'TEST_ACCOUNTS' => '',
    'ALL_MERCHANTS' => "select m.id, m.username, m.first_name, m.last_name," .
    " s.name, s.subdomain, s.description, s.return_policy, s.converted_logo, s.tax," .
    " s.external_website, s.payment_solution, s.transaction_fee_waived, s.country, s.currency from merchants m" .
    " join merchants_stores ms on (m.id = ms.merchant_id) join stores s on (ms.store_id = s.id)" ,

    'ALL_SHOPPERS' => "select id, username, first_name, last_name from shoppers",

    'MERCHANTS_WITH_STORE_ACTIVE' => "select m.id, m.username, m.first_name, m.last_name," .
    " s.name, s.subdomain, s.description, s.return_policy, s.converted_logo, s.tax," .
    " s.external_website, s.payment_solution, s.transaction_fee_waived, s.country, s.currency from merchants m" .
    " join merchants_stores ms on (m.id = ms.merchant_id) join stores s on (ms.store_id = s.id)" .
    " where s.status = " . ACTIVATED,

    'MERCHANTS_WITH_STORE_PENDING' => "select m.id, m.username, m.first_name, m.last_name," .
    " s.name, s.subdomain, s.description, s.return_policy, s.converted_logo, s.tax," .
    " s.external_website, s.payment_solution, s.transaction_fee_waived, s.country, s.currency from merchants m" .
    " join merchants_stores ms on (m.id = ms.merchant_id) join stores s on (ms.store_id = s.id)" .
    " where s.status = " . PENDING,

    'MERCHANTS_WITH_STORE_CREATED' => "select m.id, m.username, m.first_name, m.last_name," .
    " s.name, s.subdomain, s.description, s.return_policy, s.converted_logo, s.tax," .
    " s.external_website, s.payment_solution, s.transaction_fee_waived, s.country, s.currency  from merchants m" .
    " join merchants_stores ms on (m.id = ms.merchant_id) join stores s on (ms.store_id = s.id)" .
    " where s.status = " . CREATED,

    'MERCHANTS_WITH_STORE_DELETED' => "select m.id, m.username, m.first_name from merchants m" .
    " join merchants_stores ms on (m.id = ms.merchant_id) join stores s on (ms.store_id = s.id)" .
    " where s.status = " . DELETED,

    'ALL_USERS' => "select id, username, first_name, last_name from users"
);
