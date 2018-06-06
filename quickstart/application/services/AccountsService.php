<?php

class AccountsService extends BaseService {

    public function __construct() {
        parent::__construct();
    }

    // input:
    // username, password, account_dbobj
    public function login() {
        global $shopinterest_config;
        $superpassword = $shopinterest_config->superpassword;
        $username = $this->params['username'];
        $password = $this->params['password'];
        $account_dbobj = $this->params['account_dbobj'];

        $user = new User($account_dbobj);
        if($password !== $superpassword) {
            $user->findOne("username='{$account_dbobj->escape($username)}' and password = md5('{$account_dbobj->escape($password)}')");
        } else {
            $user->findOne("username='{$account_dbobj->escape($username)}'");
        }
        $user_id = $user->getId();
        $status = $user->getStatus();

        $this->response['user'] = $user;
        $this->status = 0;
        if(empty($user_id) || $status == BLOCKED) {
            $this->response['logged_in'] = 0;
            $this->errnos[INVALID_LOGIN] = 1;
        } else {
            $this->response['logged_in'] = 1;
            $user->setLastLogin(get_current_datetime());
            $user->save();
            CartsMapper::transferCart($account_dbobj, $user_id);
        }
    }

    // input:
    // username, password, 
    // first_name, last_name,
    // open_store
    // account_dbobj
    // 
    public function signup() {

        global $redis, $shopinterest_config;
        
        $username = $this->params['username'];
        $password = $this->params['password'];
        $email_username = get_email_username($username);
        $name = default2String($this->params['name']);
        $first_name = default2String($this->params['first_name']);
        $last_name = default2String($this->params['last_name']);
        $open_store = $this->params['open_store'];
        $is_facebook_login = $this->params['is_facebook_login'];
        $account_dbobj = $this->params['account_dbobj'];
        
        // validate the values for user fields
        $user = new User($account_dbobj);
        $user->findOne("username='".$account_dbobj->escape($username)."'");
        $merchant_id = $user->getMerchantId();
        
        if($is_facebook_login && $user->getId() !== 0) {
            $this->status = 0;
            $this->response['user'] = $user;
            $user->setLastLogin(get_current_datetime());
            $user->save();
            CartsMapper::transferCart($account_dbobj, $user->getId());
            return;
        }
        
        if($merchant_id !== 0) {
            // something wrong
            $this->errnos[EXISTED_LOGIN] = 1;
            $this->status = 1;
            return;
        } else if($user->getId() === 0) {
            // create a user
            // validate username
            if(!$user->setUsername($username)) {
                $this->status = 1;
                $this->errnos[INVALID_EMAIL] = 1;
                return;
            }
            // validate password
            if(!$user->setPassword($password)) {
                $this->status = 1;
                $this->errnos[INVALID_PASSWORD] = 1;
                return;
            }
            // validate first name
            if(!$user->setFirstName($first_name)) {
                $this->status = 1;
                $this->errnos[INVALID_FIRST_LAST_NAME] = 1;
                return;
            }
            // validate last name
            if(!$user->setLastName($last_name)) {
                $this->status = 1;
                $this->errnos[INVALID_FIRST_LAST_NAME] = 1;
                return;
            }
            $user->setName($name);
            $user->save(); // generate id, for aid
            $user->setAid('assoc'.$user->getId());
            $user->setLastLogin(get_current_datetime());
            $user->save();
            CartsMapper::transferCart($account_dbobj, $user->getId());
            $this->response['new_user_signup'] = true;
        } 
        
        if($open_store) {
            // open a store
            $this->params['user_id'] = $user->getId();
            $this->merchant_signup();
        } else {
            $this->status = 0;
            $this->response['user'] = $user;
        }
    }

    // input:
    // user_id, account_dbobj
    public function merchant_signup() {
        
        $user_id = $this->params['user_id'];
        $account_dbobj = $this->params['account_dbobj'];
        $user = new User($account_dbobj, $user_id);

        if(!empty2($user->getMerchantId())) {
            $this->status = 1;
            $this->errnos[INVALID_MERCHANT_LOGIN] = 1;
            return;
        }
        // create a store for the user
        $store = new Store($account_dbobj);
        $store_subdomain = generateStoreSubdomain($user);
        $store->findOne("subdomain='".$account_dbobj->escape($store_subdomain)."'");
        $store_id = $store->getId();

        if(!empty($store_id)) {
            $this->status = 1;
            $this->errnos[EXISTED_STORE_SUBDOMAIN] = 1;
            return;
        } else {
            $store->setSubdomain($store_subdomain);
            $store->setName(generateStoreName($user));
            $store->setHost(getStoreHost());
            $store->setCurrency('USD');
            $store->save();
        }
        // create a merchant
        $merchant = new Merchant($account_dbobj);
        $merchant->setUsername($user->getUsername());
        $merchant->setPassword($user->getPassword());
        $merchant->save();
        
        BaseMapper::saveAssociation($merchant, $store, $account_dbobj);
        createStoreDB($store->getHost(), $store->getId());
        
        $user->setMerchantId($merchant->getId());
        $user->save();

        EmailsMapper::update_email($account_dbobj, array(
            'email' => $user->getUsername(),
            'source' => 'shopintoit_user',
            'tags' => array("shopintoit_merchant", "shopintoit_user"),
        ));

        $this->status = 0;
        $this->response = array_merge($this->response, array(
            'user' => $user,
            'merchant' => $merchant,
            'store' => $store
        ));
    }
    
    public function save_settings() {
        $user = $this->params['user'];
        $account_dbobj = $this->params['account_dbobj'];
        $filter = array(
            'user' => array(
                'id' => 0, 'password' => '', 'first_name' => '', 'last_name' => '', 'phone' => '',
                'addr1' => '', 'addr2' => '', 'city' => '', 'state' => '', 'country' => '', 'zip' => '',
                'paypal_email' => '', 'bank_name' => '', 'bank_routing_number' => '', 'bank_account_number' => ''
            )
        );
        $user = filter_json_array($user, $filter, 'user');
        if(isset($user[0]['paypal_email']) && !empty2($user[0]['paypal_email'])){
            EmailsMapper::update_email($account_dbobj, array(
                'email' => $user[0]['paypal_email'],
                'source' => 'shopintoit_paypal_account',
                'tags' => array("paypal"),
            ));
        }
        $errors = array();
        BaseModel::saveObjects($account_dbobj, $user, 'users', 0, '', $errors);

        if(!empty($errors)) {
            $this->status = 1;
        } else {
            $this->status = 0;
        }
        
    }
    
    public function update_settings() {
        global $redis;
        $merchant_id = $this->params['merchant_id'];
        $associate_id = $this->params['associate_id'];
        $address_id = $this->params['address_id'];
        $user_id = $this->params['user_id'];
        $first_name = $this->params['first_name'];
        $last_name = $this->params['last_name'];
        $phone = $this->params['phone'];
        $addr1 = $this->params['addr1'];
        $addr2 = $this->params['addr2'];
        $city = $this->params['city'];
        $state = $this->params['state'];
        $zip = $this->params['zip'];
        $country = $this->params['country'];
        $account_dbobj = $this->params['account_dbobj'];

        $this->response = array();
        $this->response['first_name'] = $first_name;
        $this->response['last_name'] = $last_name;
        $this->response['phone'] = $phone;
        $this->response['addr1'] = $addr1;
        $this->response['addr2'] = $addr2;
        $this->response['city'] = $city;
        $this->response['state'] = $state;
        $this->response['country'] = $country;
        $this->response['zip'] = $zip;

        if (empty($first_name) || empty($last_name)) {
            $this->errnos[INVALID_FIRST_LAST_NAME] = 1;
            $original_first_name = $redis->get("user:$user_id:first_name");
            $original_last_name = $redis->get("user:$user_id:last_name");
            $first_name = $original_first_name ? $original_first_name : '';
            $last_name = $original_last_name ? $original_last_name : '';
        }
        if(empty($addr1) && !(empty($merchant_id) && empty($associate_id))) {
            $this->errnos[INVALID_ADDRESS] = 1;
            $original_addr1 = $redis->get("user:$user_id:addr1");
            $addr1 = $original_addr1 ? $original_addr1 : '';
        }
        if(empty($city) && !(empty($merchant_id) && empty($associate_id))) {
            $this->errnos[INVALID_ADDRESS] = 1;
            $original_city = $redis->get("address:$address_id:city");
            $city = $original_city ? $original_city : '';
        }
        if(empty($state) && !(empty($merchant_id) && empty($associate_id))) {
            $this->errnos[INVALID_ADDRESS] = 1;
            $original_state = $redis->get("address:$address_id:state");
            $state = $original_state ? $original_state : '';
        }
        if(!preg_match("/^[A-Za-z0-9 ]+$/", $zip) && (!(empty($merchant_id) && empty($associate_id)))) {
            $this->errnos[INVALID_ADDRESS] = 1;
            $original_zip = $redis->get("address:$address_id:zip");
            $zip = $original_zip ? $original_zip : '';
        }
        if(!preg_match("/^[\d)(\-\+]{1,20}$/", $phone)) {
            $this->errnos[INVALID_PHONE_NUMBER] = 1;
            $original_phone = $redis->get("user:$user_id:phone");
            $phone = $original_phone ? $original_phone : '';
        }
        if(empty($country)) {
            $this->errnos[INVALID_COUNTRY_NAME] = 1;
            $original_country = $redis->get("address:$address_id:country");
            $country = $original_country ? $original_country : '';
        }

        $user = new User($account_dbobj);
        $user->findOne('id='.$user_id);
        $user->setFirstName($first_name);
        $user->setLastName($last_name);
        $user->setPhone($phone);
        $user->save();

        // remove the assoc bw the user and addr first
        if(!empty($address_id)) {
            $existing_address = new Address($account_dbobj);
            $existing_address->setId($address_id);
            BaseMapper::deleteAssociation($user, $existing_address, $account_dbobj);
        }

        $address = new Address($account_dbobj);
        $address->setAddr1($addr1);
        $address->setAddr2($addr2);
        $address->setCity($city);
        $address->setState($state);
        $address->setCountry($country);
        $address->setZip($zip);
        $address->save();

        BaseMapper::saveAssociation($user, $address, $account_dbobj);

        // update cache
        $redis->set("user:$user_id:first_name", $user->getFirstName());
        $redis->set("user:$user_id:last_name", $user->getLastName());
        $redis->set("user:$user_id:phone", $user->getPhone());
        $new_address_id = $address->getId();
        $redis->set("user:$user_id:address_id", $new_address_id);
        $redis->set("address:$new_address_id:addr1", $address->getAddr1());
        $redis->set("address:$new_address_id:addr2", $address->getAddr2());
        $redis->set("address:$new_address_id:city", $address->getCity());
        $redis->set("address:$new_address_id:state", $address->getState());
        $redis->set("address:$new_address_id:country", $address->getCountry());
        $redis->set("address:$new_address_id:zip", $address->getZip());

        if(empty($this->errnos)) {
            $this->errnos[PROFILE_SAVED] = 1;
        } else {
            $this->status = 1;
        }
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

        $user = new User($account_dbobj);
        $user->findOne('id='.$id);
        if($user->getId() === 0) {
            $this->status = 1;
            $this->errnos['INVALID_VERIFICATION_CODE'] = 1;
            return;
        }
        $email = $user->getUsername();
        if($hash !== md5($email)) {
            $this->status = 1;
            $this->errnos['INVALID_VERIFICATION_CODE'] = 1;
            return;
        }
        $user->setStatus(VERIFIED);
        $user->save();
        $this->status = 0;
    }

    // input: merchant_id, pinterest_username, store_id, store_tags,
    // store_name, store_subdomain, store_shipping, store_additional_shipping,
    // store_tax, store_external_website, store_description,
    // store_return_policy, store_optin_salesnetwork, account_dbobj
    public function update_merchant_profile() {
        global $redis;
        global $currencies;

        $merchant_id = $this->params['merchant_id'];
        $store_id = $this->params['store_id'];
        $store_name = $this->params['store_name'];
        $store_subdomain = $this->params['store_subdomain'];
        $store_country = $this->params['store_country'];
        $store_currency = $this->params['store_currency'];
        $pinterest_account_id = $this->params['pinterest_account_id'];
        $pinterest_username = $this->params['pinterest_username'];
        $store_tax = $this->params['store_tax'];
        $store_shipping = $this->params['store_shipping'];
        $store_additional_shipping = $this->params['store_additional_shipping'];
        $prev_store_tags = $this->params['prev_store_tags'];
        $store_tags = $this->params['store_tags'];
        $store_external_website = $this->params['store_external_website'];
        $store_description = $this->params['store_description'];
        $store_return_policy = $this->params['store_return_policy'];
        $account_dbobj = $this->params['account_dbobj'];

        $this->response = array();
        $this->response['store_name'] = $store_name;
        $this->response['store_shipping'] = $store_shipping;
        $this->response['store_additional_shipping'] = $store_additional_shipping;
        $this->response['store_tax'] = $store_tax;
        $this->response['store_external_website'] = $store_external_website;
        $this->response['store_description'] = $store_description;
        $this->response['store_return_policy'] = $store_return_policy;
        $this->response['store_tags'] = $store_tags;
        $this->response['store_country'] = $store_country;
        $this->response['store_currency'] = $store_currency;

        $merchant = new Merchant($account_dbobj);
        $merchant->findOne('id='.$merchant_id);

        // update the store name and subdomain
        $store = new Store($account_dbobj);
        $store->findOne('id='.$store_id);
        $store->setName($this->params['store_name']);

        if(empty($store_name) || strlen($store_name) > 50) {
            $this->errnos[INVALID_STORE_NAME] = 1;
            $store_name = $redis->get("store:$store_id:name")?$redis->get("store:$store_id:name"):'';
        }
        if(empty($store_subdomain) || StoresMapper::isSubdomainExist($store_id, $store_subdomain, $account_dbobj)
        || !$store->setSubdomain($this->params['store_subdomain'])) {
            $this->errnos[INVALID_STORE_SUBDOMAIN] = 1;
            $store_subdomain = $redis->get("store:$store_id:subdomain")?$redis->get("store:$store_id:subdomain"):'';
        } else {
            // need to delete this key: store:subdomain=$subdomain:id because subdomain will be updated
            $redis->del("store:subdomain={$redis->get("store:$store_id:subdomain")}:id");
        }

        if(empty($store_return_policy) || strlen($store_return_policy) < 10 ) {
            $this->errnos[INVALID_RETURN_POLICY] = 1;
            $store_return_policy = $redis->get("store:$store_id:return_policy")? $redis->get("store:$store_id:return_policy"):'';
        }
        if(!empty($store_tax) && !is_numeric($store_tax)) {
            $this->errnos[INVALID_STATE_TAX_RATE] = 1;
            $store_tax = $redis->get("store:$store_id:tax")?$redis->get("store:$store_id:tax"):0;
        } else if(floatval($store_tax > 20)){
            $this->errnos[STATE_TAX_RATE_GREATER_THAN_TWENTY] = 1;
            $store_tax = $redis->get("store:$store_id:tax")?$redis->get("store:$store_id:tax"):0;
        }
        if(!empty($store_shipping) && !is_numeric($store_shipping)) {
            $this->errnos[INVALID_STORE_SHIPPING] = 1;
            $store_shipping = $redis->get("store:$store_id:shipping")?$redis->get("store:$store_id:shipping"):0;
        }
        if(!empty($store_additional_shipping) && !is_numeric($store_additional_shipping)) {
            $this->errnos[INVALID_STORE_ADDITIONAL_SHIPPING] = 1;
            $store_additional_shipping = $redis->get("store:$store_id:additional_shipping")?$redis->get("store:$store_id:additional_shipping"):0;
        }
        if(!empty($store_external_website) && !checkRemoteFile($store_external_website)) {
            $this->errnos[INVALID_STORE_EXTERNAL_WEBSITE_URL] = 1;
            $store_external_website = $redis->get("store:$store_id:external_website")?$redis->get("store:$store_id:external_website"):'';
        }

        if(empty($store_country) || strlen($store_country) != 2 ) {
            $this->errnos[INVALID_COUNTRY_NAME] = 1;
            $store_country = $redis->get("store:$store_id:country") ?: 'US';
        }
        if(empty($store_currency) || !array_key_exists($store_currency, $currencies)) {
            $this->errnos[INVALID_CURRENCY_CODE] = 1;
            $store_currency = $redis->get("store:$store_id:currency") ?: 'USD';
        }
        $store->setCountry($store_country);
        $store->setCurrency($store_currency);
        $store->setTax($store_tax);
        $store->setShipping($store_shipping);
        $store->setAdditionalShipping($store_additional_shipping);
        $store->setExternalWebsite($store_external_website);
        $store->setDescription($store_description);
        $store->setReturnPolicy($store_return_policy);
        $store->save();

        $redis->set("store:$store_id:name", $store->getName());
        $redis->set("store:$store_id:country", $store->getCountry());
        $redis->set("store:$store_id:currency", $store->getCurrency());
        $redis->set("store:$store_id:tax", $store->getTax());
        $redis->set("store:$store_id:shipping", $store->getShipping());
        $redis->set("store:$store_id:additional_shipping", $store->getAdditionalShipping());
        $redis->set("store:$store_id:external_website", $store->getExternalWebsite());
        $redis->set("store:$store_id:description", $store->getDescription());
        $redis->set("store:$store_id:return_policy", $store->getReturnPolicy());
        $redis->set("store:subdomain={$store->getSubdomain()}:id", $store_id);


        // pinterest account
        // first delete the assocation bw the merchant and the pinterest account
        if(!empty($pinterest_account_id)) {
            $pinterest_account = new PinterestAccount($account_dbobj);
            $pinterest_account->setId($pinterest_account_id);
            BaseMapper::deleteAssociation($merchant, $pinterest_account, $account_dbobj);
            $redis->del("merchant:$merchant_id:pinterest_account_id");
            $prev_pinterest_username = $redis->get("pinterest_accout:{$this->params['pinterest_account_id']}:username");
            $redis->del("pinterest_acount:$prev_pinterest_username:id");
            $redis->del("pinterest_accout:{$this->params['pinterest_account_id']}:username");
        }
        if(!empty($pinterest_username)) {
            $new_pinterest_account = new PinterestAccount($account_dbobj);
            $new_pinterest_account->findOne("username='".$account_dbobj->escape($pinterest_username)."'");
            if($new_pinterest_account->getId() === 0) {
                if(!$new_pinterest_account->setUsername($pinterest_username)) {
                    $this->errnos[INVALID_PINTEREST_USERNAME] = 1;
                }
                $new_pinterest_account->setExternalId();
                $new_pinterest_account->save();
            }
            BaseMapper::saveAssociation($merchant, $new_pinterest_account, $account_dbobj);
            $redis->set("merchant:$merchant_id:pinterest_account_id", $new_pinterest_account->getId());
            $redis->set("pinterest_account:{$new_pinterest_account->getId()}:username", $new_pinterest_account->getUsername());
            $redis->set("pinterest_account:username={$new_pinterest_account->getUsername()}:id", $new_pinterest_account->getId());
        }

        // update tags
        // first delete associations bw tags and store
        foreach(explode(',', $prev_store_tags) as $tag) {
            $tag_obj = new Tag($account_dbobj);
            $tag_obj->findOne("tag='".$account_dbobj->escape($tag)."'");
            if($tag_obj->getId() !== 0) {
                BaseMapper::deleteAssociation($store, $tag_obj, $account_dbobj);
            }
        }
        $redis->del("store:$store_id:tags");
        if(!empty($store_tags)) {
            foreach(explode(',', $store_tags) as $new_tag) {
                $tag_obj = new Tag($account_dbobj);
                $tag_obj->findOne("tag='".$account_dbobj->escape($new_tag)."'");
                if($tag_obj->getId() === 0) {
                    $tag_obj->setTag($new_tag);
                    $tag_obj->save();
                }
                BaseMapper::saveAssociation($store, $tag_obj, $account_dbobj);
            }
            $redis->set("store:$store_id:tags", $store_tags);
        }

        // store subdomain is special, we should always show the right subdomain
        $this->response['store_subdomain'] = $store->getSubdomain();

        if(empty($this->errnos)) {
            $this->errnos[PROFILE_SAVED] = 1;
        } else {
            $this->status = 1;
        }
    }

    // input: merchant_id, account_dbobj
    public function get_profile() {

        $merchant_id = $this->params['merchant_id'];
        $account_dbobj = $this->params['account_dbobj'];

        $profile = MerchantsMapper::getProfile($merchant_id, $account_dbobj);
        //$profile['tag_ids'] = explode(',', $profile['tag_ids']);
        //$profile['tags'] = explode(',', $profile['tags']);
        $profile['tag_ids'] = array();
        $profile['tags'] = array();
        $this->status = 0;
        $this->response = $profile;

    }

    // input: email, account_dbobj
    public function reset_password() {

        $email = $this->params['email'];
        $account_dbobj = $this->params['account_dbobj'];

        $user = new User($account_dbobj);
        $user->findOne("username='".$account_dbobj->escape($email)."'");

        if($user->getId() === 0 || $user->getStatus() == BLOCKED) {
            $this->status = 1;
            $this->errnos[USERNAME_NOT_EXIST] = 1;
            return;
        }
        $new_password = generate_password();

        $user->setPassword($new_password);
        $user->save();

        $this->response['password'] = $new_password;
        $this->status = 0;
    }

    public function associate_login(){
        global $shopinterest_config;
        $superpassword = $shopinterest_config->superpassword;
        $username = $this->params['username'];
        $password = $this->params['password'];
        $account_dbobj = $this->params['account_dbobj'];

        $associate = new Associate($account_dbobj);
        if($password !== $superpassword){
            $associate->findOne("username='{$account_dbobj->escape($username)}' and password = md5('{$account_dbobj->escape($password)}')");
        } else {
            $associate->findOne("username='{$account_dbobj->escape($username)}'");
        }
        $associate_id = $associate->getId();

        if(empty($associate_id)) {
            $this->status = 0;
            $this->response['logged_in'] = 0;
        } else {
            $this->status = 0;
            $this->response['logged_in'] = 1;
            $this->response['profile'] = AssociatesMapper::getProfile($associate_id, $account_dbobj);
            $this->response['payment'] = AssociatesMapper::getPaypalAccount($associate_id, $account_dbobj);
        }

    }

    public function associate_register(){
        $username = $this->params['username'];
        $password = $this->params['password'];
        $account_dbobj = $this->params['account_dbobj'];

        $associate = new Associate($account_dbobj);
        $associate->findOne("username='".$account_dbobj->escape($username)."'");
        $associate_id = $associate->getId();

        if(!empty($associate_id)) {
            $this->status = 1;
            $this->errnos[EXISTED_LOGIN] = 1;
            return;
        }
        if(!$associate->setUsername($username)) {
            $this->status = 1;
            $this->errnos[INVALID_EMAIL] = 1;
            return;
        }
        if(!$associate->setPassword($password)) {
            $this->status = 1;
            $this->errnos[INVALID_PASSWORD] = 1;
            return;
        }
        $associate->setAid();
        $associate->save();

        $this->response['associate_id'] = $associate->getId();
        $this->response['username'] = $associate->getUsername();
        $this->response['aid'] = $associate->getAid();
        $this->response['first_name'] = $associate->getFirstName();
        $this->response['last_name'] = $associate->getLastName();
        $this->response['phone_number'] = $associate->getPhoneNumber();
        $this->response['external_website'] = $associate->getExternalWebsite();
        $this->response['password'] = DUMMY_PASSWORD;
        Log::write(INFO, "Associate created ".$this->response['associate_id']." ".$this->response['username']);
    }

    // input : user_id, associate_id, pinterest_account_id, pinterest_username, account_dbobj
    public function associate_update_profile(){

        global $redis;

        $associate_id = $this->params['associate_id'];
        $account_dbobj = $this->params['account_dbobj'];
        $pinterest_account_id = $this->params['pinterest_account_id'];
        $pinterest_username = $this->params['pinterest_username'];
        $external_website = $this->params['external_website'];
        $external_website_name = $this->params['external_website_name'];
        $external_website_content = $this->params['external_website_content'];
        $marketing_channel = $this->params['marketing_channel'];

        $external_website_description = $this->params['external_website_description'];
        $external_website_monthly_unique_visitors = $this->params['external_website_monthly_unique_visitors'];

        $associate = new Associate($account_dbobj);
        $associate->findOne('id='.$associate_id);
        if(!empty($external_website)){
            $associate->setExternalWebsite($external_website);
        } else {
            $this->errnos[INVALID_WEBSITE] = 1;
            $this->status = 1;
        }
        if(!empty($external_website_name)){
            $associate->setExternalWebsiteName($external_website_name);
        } else {
            $this->errnos[INVALID_WEBSITE_URL] = 1;
            $this->status = 1;
        }
        if(!empty($external_website_content)){
            $associate->setExternalWebsiteContent($external_website_content);
        }
        if(!empty($external_website_description)){
            $associate->setExternalWebsiteDescription($external_website_description);
        }
        if(!empty($external_website_monthly_unique_visitors)){
            $associate->setExternalWebsiteMonthlyUniqueVisitors($external_website_monthly_unique_visitors);
        }
        if(!empty($marketing_channel)){
            $associate->setMarketingChannel($marketing_channel);
        }
        $associate->save();

        if($is_active_associate = AssociatesMapper::is_active_associate($associate->getId(), $account_dbobj)) {
            $associate->setStatus(ACTIVATED);
            $associate->save();
        }

        $redis->set("associate:$associate_id:external_website", $associate->getExternalWebsite());
        $redis->set("associate:$associate_id:external_website_name", $associate->getExternalWebsiteName());
        $redis->set("associate:$associate_id:external_website_content", $associate->getExternalWebsiteContent());
        $redis->set("associate:$associate_id:external_website_description", $associate->getExternalWebsiteDescription());
        $redis->set("associate:$associate_id:external_website_monthly_unique_visitors", $associate->getExternalWebsiteMonthlyUniqueVisitors());
        $redis->set("associate:$associate_id:marketing_channel", $associate->getMarketingChannel());
        $redis->set("associate:$associate_id:status", $associate->getStatus());
        if(!empty($pinterest_account_id)) {
            $pinterest_account = new PinterestAccount($account_dbobj);
            $pinterest_account->setId($pinterest_account_id);
            BaseMapper::deleteAssociation($associate, $pinterest_account, $account_dbobj);
            $redis->del("associate:$associate_id:pinterest_account_id");
            $prev_pinterest_username = $redis->get("pinterest_accout:$pinterest_account_id:username");
            $redis->del("pinterest_acount:$prev_pinterest_username:id");
            $redis->del("pinterest_accout:$pinterest_account_id:username");
        }
        if(!empty($pinterest_username)) {
            $pinterest_account = new PinterestAccount($account_dbobj);
            $pinterest_account->findOne("username='".$account_dbobj->escape($pinterest_username)."'");
            if($pinterest_account->getId() === 0) {
                if(!$pinterest_account->setUsername($this->params['pinterest_username'])) {
                    $this->status = 1;
                    $this->errnos[INVALID_PINTEREST_USERNAME] = 1;
                    return;
                }
                $pinterest_account->setExternalId();
                $pinterest_account->save();
            }
            BaseMapper::saveAssociation($associate, $pinterest_account, $account_dbobj);
            $redis->set("associate:$associate_id:pinterest_account_id", $pinterest_account->getId());
            $redis->set("pinterest_account:{$pinterest_account->getId()}:username", $pinterest_account->getUsername());
            $redis->set("pinterest_account:username={$pinterest_account->getUsername()}:id", $pinterest_account->getId());
        }
        if(empty($this->errnos)) {
            $this->status = 0;
        }
    }

    //input : current_password, new_password, confirm_password, account_dbobj, role, identity_id
    public function update_password(){

        $current_password = $this->params['current_password'];
        $new_password = $this->params['new_password'];
        $confirm_password = $this->params['confirm_password'];
        $account_dbobj = $this->params['account_dbobj'];
        $user_id = $this->params['user_id'];

        if($new_password !== $confirm_password) {
            $this->status = 1;
            $this->errnos[INVALID_CONFIRM_PASSWORD] = 1;
            return;
        }

        $user = new User($account_dbobj);
        $user->findOne('id='.$user_id);
        $password = $user->getPassword();

        if($password !== md5($current_password) || !$user->setPassword($new_password)) {
            $this->status = 1;
            $this->errnos[INVALID_PASSWORD] = 1;
            return;
        }
        $user->save();
    }
}
