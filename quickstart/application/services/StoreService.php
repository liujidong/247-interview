<?php

class StoreService extends BaseService {

    // input: store_id, board_ids, account_dbobj
    public function save_selected_boards() {

        $store_id = $this->params['store_id'];
        $board_ids = $this->params['board_ids'];
        $account_dbobj = $this->params['account_dbobj'];

        $store = new Store($account_dbobj);
        $store->findOne('id='.$store_id);

        // delete the association bw the stores and selected boards first
        StoresMapper::deleteSelectedBoards($store_id, $account_dbobj);
        Log::write(INFO, 'Deleted all associations bw the stores and selected boards store_id: '.$store_id);

        foreach($board_ids as $board_id) {
            $board = new PinterestBoard($account_dbobj);
            $board->findOne('id='.$board_id);

            BaseMapper::saveAssociation($store, $board, $account_dbobj);
            Log::write(INFO, 'Created the association bw stores and boards '.$store_id.' '.$board_id);
        }

        $this->status = 0;

    }

    // input: store_id
    public function get_selected_boards_nonselected_pins() {
        $store_id = $this->params['store_id'];
        $page = $this->params['page'];
        $account_dbobj = $this->params['account_dbobj'];

        $pins = StoresMapper::getSelectedBoardsNonSelectedPins($store_id, $account_dbobj, $page);

        if(count($pins) === 0) {
            // import pins

            // get selected boards

            // get board page and subpage
            $page_subpage = getBoardPageSubPage($page);
        }
    }

    // input: store_id, pins, category, account_dbobj, store_dbobj
    public function add_products() {
        $store_id = $this->params['store_id'];
        $pins = $this->params['pins'];
        $account_dbobj = $this->params['account_dbobj'];
        $store_dbobj = $this->params['store_dbobj'];
        $store_optin_salesnetwork = empty($this->params['store_optin_salesnetwork'])?0:$this->params['store_optin_salesnetwork'];
        $response = array('new_product_cnt'=>0);
        $store = new Store($account_dbobj);
        $store->findOne('id='.$store_id);

        foreach($pins as $pin_id=>$pin_info) {

            // mandatory fields: name, price, at least one image
            $product_name=  trim($pin_info['name']);
            $product_description = empty($pin_info['description'])?'':$pin_info['description'];
            $product_shipping = empty($pin_info['shipping'])?0:$pin_info['shipping'];
            $product_quantity = empty($pin_info['quantity'])?0:$pin_info['quantity'];
            $product_commission = empty($pin_info['product_commission'])?0:$pin_info['product_commission'];
            $product_images = $pin_info['image'];

            if(empty($product_name)){
                $this->status = 1;
                $this->errnos[PRODUCT_NAME_ERROR] = 1;
                continue;
            }

            $product_price = floatval2($pin_info['price']);
            if(empty($product_price)){
                $this->status = 1;
                $this->errnos[PRODUCT_PRICE_ERROR] = 1;
                continue;
            }

            if(!is_numeric($product_shipping)){
                $this->status = 1;
                $this->errnos[PRODUCT_SHIPPING_ERROR] = 1;
                continue;
            } else {
                $product_shipping = floatval2($product_shipping);
            }

            if(($store_optin_salesnetwork === 2) && (!is_numeric($product_commission) || ($product_commission*$product_price/100<1))) {
                $this->status = 1;
                $this->errnos[PRODUCT_COMMISSION_ERROR] = 1;
                continue;
            }

            if(count($product_images) == 0){
                $this->status = 1;
                $this->errnos[PRODUCT_PIC_ERROR] = 1;
                continue;
            }

            $product_quantity = intval2($product_quantity);
            $product_commission = intval2($product_commission);

            // create a product
            $product = new Product($store_dbobj);
            $product->setName($product_name);
            $product->setDescription($product_description);
            $product->setPrice($product_price);
            $product->setShipping($product_shipping);
            $product->setQuantity($product_quantity);
            $product->setPinterestPinId($pin_id);
            $product->setCommission($product_commission);
            $product->save();
            $response['new_product_cnt']++;
            Log::write(INFO, 'Created a product '.$product->getId());

            $picture = new Picture($store_dbobj);
            // get borads image here
            $image = $product_images[0];
            $picture->setType('board');
            $picture->setUrl($image);
            $picture->setSource('pinterest');
            $picture->setPinterestPinId($pin_id);
            $picture->save();
            Log::write(INFO, 'Created a picture '.$picture->getId().' '.$image);
            BaseMapper::saveAssociation($product, $picture, $store_dbobj);
            Log::write(INFO, 'Created the association bw products and pictures '.$product->getId().' '.$picture->getId());

            $service = new ProductPhotosService();
            $service->setMethod('create_product_photo');
            $service->setParams(array(
                'store_id' => $store_id,
                'product_id' => $product->getId(),
                'picture_ids' => array($picture->getId()),
                'store_dbobj' => $store_dbobj
            ));
            $service->call();

            // create an assoc bw the store and the pin
            $pin = new PinterestPin($account_dbobj);
            $pin->findOne('id='.$pin_id);
            BaseMapper::saveAssociation($store, $pin, $account_dbobj);
            Log::write(INFO, 'Created the association bw stores and pins '.$store_id.' '.$pin_id);

            // category
            if(!empty($pin_info['category'])) {
                $category_obj = new Category($store_dbobj);
                $category_obj->setId($pin_info['category']);
                BaseMapper::saveAssociation($product, $category_obj, $store_dbobj);
                Log::write(INFO, 'Created the association bw products and categories '.$product->getId().' '.$pin_info['category']);
            }
            $this->errnos[PRODUCTS_PARTIAL_SAVED] = 1;
        }
        $this->status = 0;
        $this->response = $response;
    }

    // input: store_id, name, description, price, shipping, quantity, picture_ids,
    // pin, account_dbobj, store_dbobj
    public function create_product() {

        $product = new Product($this->params['store_dbobj']);
        $product->setName($this->params['name']);
        $product->setDescription($this->params['description']);
        $product->setPrice($this->params['price']);
        $product->setShipping($this->params['shipping']);
        $product->setQuantity($this->params['quantity']);
        $product->save();

        if(!empty($this->params['picture_ids'])) {
            foreach($this->params['picture_ids'] as $picture_id) {
                $picture = new Picture($this->params['store_dbobj']);
                $picture->setId($picture_id);
                BaseMapper::saveAssociation($product, $picture, $this->params['store_dbobj']);
            }
        } else if(!empty($this->params['pin'])) {
            foreach($this->params['pin']['image'] as $i=>$image) {
                $picture = new Picture($store_dbobj);
                if($i === 0) {
                    $image_type = 'board';
                } else if($i === 1) {
                    $image_type = 'mobile';
                } else if($i === 2) {
                    $image_type = 'closeup';
                } else if($i === 3) {
                    $image_type = 'thumbnail';
                }
                $picture->setType($image_type);
                $picture->setUrl($image);
                $picture->setSource('pinterest');
                $picture->save();
                BaseMapper::saveAssociation($product, $picture, $this->params['store_dbobj']);
            }
            // create an assoc bw the store and the pin
            $pin_obj = new PinterestPin($this->params['account_dbobj']);
            $pin_obj->findOne('id='.$this->params['pin']['id']);
            BaseMapper::saveAssociation($store, $this->params['pin'], $account_dbobj);
        }

    }

    // input: store_id, store_dbobj
    public function get_products() {
        $store_id = $this->params['store_id'];
        $store_dbobj = $this->params['store_dbobj'];
        $page_num = isset($this->params['page_num']) ? $this->params['page_num'] : 1;
        $query = isset($this->params['query']) ? $this->params['query'] : '';

        $products = StoresMapper::getProducts($store_id, $store_dbobj, $page_num, $query);
        $this->response = $products;
    }

    // input: store_id, $amount, $fee, $account_dbobj
    // output: paykey
    public function parallel_pay() {
        $store_id = $this->params['store_id'];
        $amount = $this->params['amount'];
        $fee = $this->params['fee'];
        $account_dbobj = $this->params['account_dbobj'];

        $store = new Store($account_dbobj);
        $store->findOne('id='.$store_id);
        $store_name = $store->getName();
        $paypal = StoresMapper::getPaypalAccount($store_id, $account_dbobj);
        $merchant_paypal_email = $paypal['username'];
        $store_url = 'http://'.$_SERVER['HTTP_HOST'].'/store/'.$store_name;

        // post fields
        $data = array(
            'actionType' => 'PAY',
            'cancelUrl' => $store_url,
            'feesPayer' => 'EACHRECEIVER',
            'memo' => 'Payments-to-both-the-store-and-shopinterest',
            'receiverList.receiver(0).amount' => "$amount",
            'receiverList.receiver(0).email' => 'redboo_1341979770_biz@gmail.com',
            'receiverList.receiver(0).primary' => 'false',
            'receiverList.receiver(1).amount' => '1.00',
            'receiverList.receiver(1).email' => 'shopin_1341979853_biz@gmail.com',
            'receiverList.receiver(1).primary' => 'false',
            'requestEnvelope.errorLanguage' => 'en_US',
            'returnUrl' => $store_url
        );
        $query = urldecode(http_build_query($data));

        // headers
        $headers = array(
            'X-PAYPAL-SECURITY-USERID:sell1_1337294519_biz_api1.gmail.com',
            'X-PAYPAL-SECURITY-PASSWORD:1337294544',
            'X-PAYPAL-SECURITY-SIGNATURE:AxeDM5CBme1UxyARHqIkNHGjcCaTA6sEt7NQpqdYwI7o-CqOPCkaOqN4',
            'X-PAYPAL-REQUEST-DATA-FORMAT:NV',
            'X-PAYPAL-RESPONSE-DATA-FORMAT:NV',
            'X-PAYPAL-APPLICATION-ID:APP-80W284485P519543T'
        );
        print_r($query);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://svcs.sandbox.paypal.com/AdaptivePayments/Pay');
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "actionType=PAY&cancelUrl=$store_url&currencyCode=USD&feesPayer=EACHRECEIVER&memo=Payments-to-both-the-store-and-shopinterest&receiverList.receiver(0).amount=$amount&receiverList.receiver(0).email=redboo_1341979770_biz@gmail.com&receiverList.receiver(0).primary=false&receiverList.receiver(1).amount=$fee&receiverList.receiver(1).email=shopin_1341979853_biz@gmail.com&receiverList.receiver(1).primary=false&requestEnvelope.errorLanguage=en_US&returnUrl=$store_url&reverseAllParallelPaymentsOnError=true");
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);

        $httpResponse = curl_exec($ch);ddd($httpResponse);
        parse_str($httpResponse, $queries);
        $paykey= $queries['payKey'];
        $this->status = 0;
        $this->response['paykey'] = $paykey;
    }

    // input: profile, customer_id, store_dbobj
    public function fb_connect() {
        $profile = $this->params['profile'];
        $customer_id = $this->params['merchant_id'];
        $store_dbobj = $this->params['account_dbobj'];

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

        $customer = new Customer($store_dbobj);
        BaseMapper::saveAssociation($cusomter, $fb_user, $store_dbobj);

        $this->status = 0;

    }

    // input: profile, customer_id, store_dbobj
    public function twitter_connect() {
        $profile = $this->params['profile'];
        $customer_id = $this->params['customer_id'];
        $store_dbobj = $this->params['store_dbobj'];

        $twitter_user = new TwitterUser($account_dbobj);
        $twitter_user->findOne('id='.$merchant_id);
        $twitter_user->setProfileImageUrl($profile['profile_image_url']);
        $twitter_user->setUrl($profile['url']);
        $twitter_user->setLocation($profile['location']);
        $twitter_user->setName($profile['name']);
        $twitter_user->setScreenName($profile['screen_name']);
        $twitter_user->setId($profile['id']);
        $twitter_user->save();

        $customer = new Customer($store_dbobj);
        BaseMapper::saveAssociation($cusomter, $twitter_user, $store_dbobj);

        $this->status = 0;

    }

    // input: username, password, store_dbobj
    public function register() {
        $username = $this->params['username'];
        $password = $this->params['password'];
        $store_dbobj = $this->params['store_dbobj'];

        // validate the customer
        $customer = new Customer($store_dbobj);
        $customer->findOne("username='".$username."'");
        $customer_id = $customer->getId();

        if(!empty($customer_id)) {
            $this->status = 1;
            array_push($this->errors, 'ERROR: this email has already registered.');
            return $return;
        }
        if(!$customer->setUsername($username)) {
            $this->status = 1;
            array_push($this->errors, 'ERROR: this email is not valid.');
            return $return;
        }
        if(!$customer->setPassword($password)) {
            $this->status = 1;
            array_push($this->errors, 'ERROR: this password is not valid');
            return $return;
        }

        $customer->save();
        $this->response['customer_id'] = $customer->getId();

        $this->status = 0;
    }

    // input: username, password, store_dbobj
    public function login() {
        $username = $this->params['username'];
        $password = $this->params['password'];
        $store_dbobj = $this->params['store_dbobj'];

        $customer = new Customer($store_dbobj);
        $customer->findOne("username='$username' and password = md5('$password')");
        $customer_id = $customer->getId();
        if(empty($customer_id)) {
            $this->status = 0;
            $this->response['logged_in'] = 0;
        } else {
            $this->status = 0;
            $this->response['logged_in'] = 1;
            $this->response['customer_id'] = $merchant_id;

        }
    }

    // input: customer_id, order_id, $store_dbobj
    public function open_review() {

        $customer_id = $this->params['customer_id'];
        $order_id = $this->params['order_id'];
        $store_dbobj = $this->params['store_dbobj'];

        $review = new Review($store_dbobj);
        $review->setCustomerId($customer_id);
        $review->setOrderId($order_id);
        $review->save();

        $this->status = 0;
    }

    // input: customer_id, order_id, score, text, $store_dbobj
    public function write_review() {
        $customer_id = $this->params['customer_id'];
        $order_id = $this->params['order_id'];
        $score = $this->params['score'];
        $text = $this->params['text'];
        $store_dbobj = $this->params['store_dbobj'];

        $review = new Review($store_dbobj);
        $review->setCustomerId($customer_id);
        $review->setOrderId($order_id);
        $review->setScore($score);
        $review->setText($text);
        $review->save();

        $this->status = 0;
    }

    public function get_store_by_name_or_tag(){
        $content = $this->params['content'];
        $account_dbobj = $this->params['account_dbobj'];
        $output = array();
        $res = StoresMapper::getStoreByNameOrTag($account_dbobj,$content);
        foreach ($res as $key => $record){
            $label= $record['name'];
            if(!empty($record['tag'])){
                $label .= "(".$record['tag'].")";
            }
            $output[] = array($label,$record['url']);
        }
        $this->response = $output;
    }

    /**
     * Get info for all stores. This is used for the admin.
     */
    public function get_all_stores_info(){
        $account_db = $this->params['account_dbobj'];
        $page_num = isset($this->params['page_num']) ? $this->params['page_num'] : 0;
        $merchant_email = isset($this->params['merchant_email']) ? $this->params['merchant_email'] : '';
        $store_subdomain = isset($this->params['store_subdomain']) ? $this->params['store_subdomain'] : '';

        $stores = StoresMapper::getAllStores($account_db, $page_num, array(
                'merchant_email' => $merchant_email,
                'store_subdomain' => $store_subdomain
        ));
        $return = array();
        foreach ($stores as $store){
            $store_host = $store['host'];
            $store_id = $store['id'];
            $merchant_id = $store['merchant_id'];
            $user_id = $store['user_id'];
            $store_dbobj = DBObj::getStoreDBObj($store_host,$store_id);
            if($store_dbobj->is_db_existed()){
                $return[$store_id] = $store;
                switch ($store['status']){
                    case '2':
                        $return[$store_id]['status'] = 'Active';
                        break;
                    case '1':
                        $return[$store_id]['status'] = 'Pending';
                        break;
                    case '0':
                        $return[$store_id]['status'] = 'Created';
                        break;
                }
                $payment_info = UsersMapper::getPaymentAccount($user_id, $account_db);
                if(!empty($payment_info['paypal_account_username']))
                    $return[$store_id]['paypal'] = 'Y';
                else
                    $return[$store_id]['paypal'] = 'N';

                if(!empty($store['pinterest_username']))
                   $return[$store_id]['pinterest_url'] = 'http://pinterest.com/'.$store['pinterest_username'];

                $activated_product_cnt = ProductsMapper::getProductsCnt($store_dbobj);
                if(empty($activated_product_cnt)) {
                    $return[$store_id]['status'] = 'Pending';
                }
                $return[$store_id]['product_cnt'] = $activated_product_cnt;
                $return[$store_id]['transaction_cnt'] = count(OrdersMapper::getOrders($store_dbobj));
            }
        }
        $this->response = $return;
    }


    public function create_products() {
        $products = $this->params['products'];
        $store_dbobj = $this->params['store_dbobj'];
        if(empty($products)) {
            $this->status = 1;
            return;
        }

        $filter = array(
            'product' => array('id' => 0, 'status' => 0, 'name' => '', 'description' => '', 'size' => '',
            'quantity' => 0, 'price' => 0, 'shipping' => 0, 'free_shipping' => 0, 'pinterest_pin_id' => 0,
            'ext_ref_id' => 0, 'ext_ref_url' => '','brand' => '', 'misc' => '', 'start_date' => '',
            'end_date' => '', 'commission' => '','global_category_id' => '', 'resell' => 0, 'purchase_url'=>'', 'categories' => array(),
            'pictures' => array(), 'shipping_options' => array(), 'fields' => array()),

            'category' => array('id' => 0, 'status' => 0, 'category' => '', 'description' => ''),

            'picture' => array('id' => 0, 'status' => 0, 'name' => '', 'description' => '', 'type' => '',
                'size' => '', 'width' => 0, 'height' => 0, 'orderby' => 0, 'url' => '', 'source' => '',
                'pinterest_pin_id' => '', 'pic_upload_time' => ''),

            'shipping_option' => array('id' => 0, 'status' => 0, 'name' => '', 'shipping_destinations' => array()),

            'shipping_destination' => array('id' => 0, 'status' => 0, 'from' => '', 'to' => '',
                'base' => '', 'additional' => '', 'arrival_time' => ''),

            'field' => array('id' => 0, 'status' => 0, 'name' => '', 'type' => 0, 'required' => 0,
                'field_values' => array()),

            'field_value' => array('id' => 0, 'status' => 0, 'value' => '')
        );

        $input_products = filter_json_array($products, $filter, 'products');

        // generate name for pic
        foreach($input_products as $i0 => $p){
            if(!isset($p['pictures']) || empty($p['pictures'])) continue;
            $pictures = $p['pictures'];
            foreach($pictures as $i1 => $pic){
                if(!empty($pic['id'])) continue;
                $input_products[$i0]['pictures'][$i1]['name'] = uuid();
            }
        }

        $changed_cats = array();
        foreach($input_products as $p){
            if(!isset($p['global_category_id'])) continue;
            $changed_cats[] = $p['global_category_id'];
            $pid =$p['id'];
            $op = new Product($store_dbobj, $pid);
            $changed_cats[] = $op->getGlobalCategoryId();
        }

        foreach($input_products as $pindex => $product) {

            $commission = default2Int($product['commission']);
            if($commission < 5) {
                $input_products[$pindex]['commission'] = 5;
            }

            if(isset($product['categories']) && !empty($product['categories'])) {
                if(!empty($product['id'])) {
                    CategoriesMapper::deleteCategoryByProductId($store_dbobj, $product['id']);
                }
                foreach($product['categories'] as $cindex => $category) {
                    if(isset($category['category'])){
                        if(strlen($category['category']) > LIMITATION_TAG_MAX_LENGTH) {
                            unset($input_products[$pindex]['categories'][$cindex]);
                        } else if(empty($category['description'])) {
                            $input_products[$pindex]['categories'][$cindex]['description'] = $category['category'];
                        }
                    }
                }
                $input_products[$pindex]['categories'] = array_values($input_products[$pindex]['categories']);
            }
        }

        $error = array();
        BaseModel::saveObjects($store_dbobj, $input_products, 'products', 0, '', $error);

        $store_dbname = $store_dbobj->getDBName();
        foreach($changed_cats as $cat_id){
            $ck = CacheKey::q($store_dbname . ".store_global_category?id=" . $cat_id);
            $old_data = DAL::get($ck);
            DAL::s($ck, $old_data);
        }
        //force update tags
        DAL::delete(lck_store_tags($store_dbobj->getDBName()));
        Store::clearCache($store_dbobj->getStoreId());

        // upload picture to clodinary
        $store_id = $store_dbobj->getStoreId();
        foreach($input_products as $i0 => $p){
            if(!isset($p['pictures']) || empty($p['pictures'])) continue;
            $pictures = $p['pictures'];
            foreach($pictures as $i1 => $pic){
                if(!isset($pic['url'])) continue;
                $folder = cloudinary_store_product_ns($store_id, $p['id']);
                try {
                    $r = \Cloudinary\Uploader::upload(
                        $pic['url'],
                        array("public_id" => $folder . $pic['name'], 'format' => 'jpg',)
                    );
                } catch(Exception $x){
                    Log::write(WARN, "cloudinary upload error : " . $folder . $pic['name']);
                }
            }
        }

        if(!empty($error)) {
            $this->errnos[PRODUCTS_PARTIAL_SAVED] = 1;
            $this->status = 1;
        } else {
            $this->response = $input_products;
        }
    }

    public function get_products2() {
        $store_dbobj = $this->params['store_dbobj'];

        $page = isset($this->params['page']) ? $this->params['page'] : 0;
        $global_category_id = isset($this->params['category']) ? $this->params['category'] : 0;
        $search = isset($this->params['search']) ? $this->params['search'] : '';
        $product_status = isset($this->params['product_status']) ? $this->params['product_status'] : 'active';

        $products = ProductsMapper::getProducts($store_dbobj, $page, $search, $global_category_id, $product_status);
        //$product_cnt = ProductsMapper::getProductsCnt($store_dbobj);
        $this->response = $products;
        //$this->response['product_cnt'] = $product_cnt;
    }

    // input:
    // array(
    //  'store' => array(...),
    //  'account_dbobj' => $account_dbobj
    // )
    public function save_settings() {
        global $dbconfig;

        $store = $this->params['store'];
        $account_dbobj = $this->params['account_dbobj'];
        $pinterest_username = isset($store['pinterest_username']) ? $store['pinterest_username'] : '';
        $filter = array(
            'store' => array(
                'id' => 0, 'subdomain' => '', 'name' => '', 'description' => '',
                'return_policy' => '', 'logo' => '', 'converted_logo' => '',
                'tax' => 0, 'external_website' => '', 'country' => '', 'currency' => '',
                'tags' => '', 'no_international_shipping'=> 0,
            )
        );
        $store_id = $store['id'];
        $store_ck = CacheKey::q($dbconfig->account->name . ".store?id=" . $store_id);
        $old_store = BaseModel::findCachedOne($store_ck);

        $old_info = BaseModel::findCachedOne($dbconfig->account->name . ".store?id=".$store_id);
        if(
            isset($store['currency']) && !empty($store['currency'])
            && $store['currency'] != $old_info['currency']
        ){
            // clear cart and orders
            CartsMapper::clearStoreItems($account_dbobj, $store_id);
            MyordersMapper::clearStoreItems($account_dbobj, $store_id);
        }
        $store = filter_json_array($store, $filter, 'store');
        $errors = array();
        $ret = BaseModel::saveObjects($account_dbobj, $store, 'stores', 0, '', $errors);
        DAL::delete(CacheKey::q($dbconfig->account->name . ".store?subdomain=" . $old_store['subdomain']));

        if(!empty($errors)) {
            $this->status = 1;
            return;
        } else {
            $this->status = 0;
        }
        if(empty($pinterest_username) || empty($store_id)) { // bug: can not save pn for new store
            return;
        }
        $pa = new PinterestAccount($account_dbobj);
        $pa->findOne("username = '" . $account_dbobj->escape($pinterest_username) . "'");
        if($pa->getId()<1){
            $ret = $pa->setUsername($pinterest_username);
            if(!$ret) return;
            $pa->save();
        }

        $store = BaseModel::findCachedOne($store_ck);

        $old_pa = new PinterestAccount($account_dbobj);
        $old_pa->findOne("username = '" . $account_dbobj->escape($store['pinterest_username']) . "'");

        $m = new Merchant($account_dbobj);
        $m->setId($store['merchant_id']);

        BaseMapper::deleteAssociation($m, $old_pa, $account_dbobj);
        BaseMapper::saveAssociation($m, $pa, $account_dbobj);

        DAL::delete($store_ck);
    }

    // input: url, store_id, account_dbobj
    public function save_avatar() {
        $url = $this->params['url'];
        $store_id = $this->params['store_id'];
        $account_dbobj = $this->params['account_dbobj'];

        $filename = uuid();
        $folder = cloudinary_store_misc_ns($store_id);
        try {
            $r = \Cloudinary\Uploader::upload(
                $url,
                array("public_id" => $folder . $filename, 'format' => 'jpg',)
            );

            //$store_logo_url = $r['url'];
            $options = array("width" => 140, "height" => 140, "crop" => "fill");
            $store_logo_url = cloudinary_url($folder . $filename . ".jpg", $options);

            // store the avatar to mysql
            $store = new Store($account_dbobj, $store_id);
            if(!$store->setLogo($store_logo_url)) {
                $this->status = 1;
            } else {
                $store->setConvertedLogo($filename);
                $store->save();
                $this->status = 0;
                $this->response['logo_url'] = $store_logo_url;
            }
        } catch (Exception $e){
            $this->status = 1;
        }
    }

    public function delete_products() {
        $target = $this->params['target'];
        $store_id = $this->params['store_id'];
        if($target === 'all') {
            // clear db
            $sql = "update products set status = 127";
            $dbobj = DBObj::getStoreDBObjById($store_id);
            $dbobj->query($sql);

            // clear cache
            $active_product_ck = lck_store_active_products(getStoreDBName($store_id));
            $object_keys = DAL::get($active_product_ck);
            $object_keys = is_array($object_keys) ? $object_keys : array();
            foreach($object_keys as $k) {
                DAL::delete(CacheKey::q($k));
            }
            DAL::delete($active_product_ck);

            $inactive_product_ck = lck_store_inactive_products(getStoreDBName($store_id));
            $object_keys = DAL::get($inactive_product_ck);
            $object_keys = is_array($object_keys) ? $object_keys : array();
            foreach($object_keys as $k) {
                DAL::delete(CacheKey::q($k));
            }
            DAL::delete($inactive_product_ck);

            // clear images
            $prefix = cloudinary_store_product_ns($store_id, 0); //"$env/s-s/s-$store_id/p/p-0/"
            $prefix = substr($prefix, 0, -4);
            try{
                $api = new \Cloudinary\Api();
                $api->delete_resources_by_prefix($prefix);
            }catch(Exception $e){
            }
        } else if($target === 'inactive'){
            // TODO
            $this->status = 1;
        } else if(preg_match("/\d+/", $target)){
            // TODO
            $this->status = 1;
        } else {
            // bad target
            $this->status = 1;
        }
    }

}
