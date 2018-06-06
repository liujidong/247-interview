<?php

class NativeCheckoutService extends BaseService {


    public static function fillAddresses($m, $data, $type = "shipping"){
        if($type !== "billing" && $type !== "shipping"){
            return;
        }
        $addr_fields = array(
            "first_name", "last_name",
            "addr1", "addr2", "country", "state", "city",
            "zip",
        );

        foreach($addr_fields as $f){
            $key = $type . '_' . $f;
            $m->set($key, $data[$key]);
        }
        if($type === "shipping"){
            $m->setShippingPhone($data['shipping_phone']);
            $m->setShippingEmail($data['shipping_email']);
        }
    }

    public function myorder_summary(){
        global $dbconfig;
        $params = $this->params;

        $account_dbobj = $params['account_dbobj'];
        $myorder_grp = $params['order_group'];
        $save_summary = isset($params['save_summary']) && $params['save_summary'];
        $return = array('errors'=>array());
        $return['order_id'] = $myorder_grp->getId();
        $dest = $myorder_grp->getShippingCountry();

        $coupon_free_shipping_info = array(FALSE, 0, 0); //is_free, store_id, product_id
        $grp_coupon = NULL;
        $left_grp_discount = 0;
        $coupon_used = FALSE; // now only one coupom per cart(order group)!
        if(!empty2($myorder_grp->getCouponCode())){
            $grp_coupon = BaseModel::findCachedOne(
                CacheKey::q($dbconfig->account->name . ".mycoupon?code=" . $myorder_grp->getCouponCode()));
            if($grp_coupon['price_offer_type'] == FLAT_VALUE_OFF){
                $left_grp_discount = $grp_coupon['price_off'];
            }
        }
        $orders = MyorderGroupsMapper::getOrders($account_dbobj, $myorder_grp->getId());
        $items_by_store = array();
        $summary_by_store = array();
        $order_ids_by_store = array();
        foreach($orders as $order){
            $order_coupon = NULL;
            $left_order_discount = 0;
            if(!empty2($order['coupon_code'])){
                $order_coupon = BaseModel::findCachedOne(
                    CacheKey::q($dbconfig->account->name . ".mycoupon?code=" . $order['coupon_code']));
                if($order_coupon['price_offer_type'] == FLAT_VALUE_OFF){
                    $left_order_discount = $order_coupon['price_off'];
                }
            }
            $order_store = BaseModel::findCachedOne(
                CacheKey::q($dbconfig->account->name . ".store?id=" . $order['store_id']));

            $order_items = MyordersMapper::getOrderItems($account_dbobj, $order['id']);
            $order_ids_by_store[$order['store_id']] = $order['id'];
            $summary_by_store[$order['store_id']]['order_id'] = $order['id'];
            $summary_by_store[$order['store_id']]['shipping'] = empty2($order['shipping']) ?  0 : $order['shipping'];
            //$summary_by_store[$order['store_id']]['price_total'] = $summary_by_store[$order['store_id']]['shipping'];
            $summary_by_store[$order['store_id']]['product_cnt'] = 0;
            $summary_by_store[$order['store_id']]['price_total'] = 0;
            $summary_by_store[$order['store_id']]['tax_total'] = 0;
            $summary_by_store[$order['store_id']]['product_ids'] = array();
            foreach($order_items as $item){
                if(empty($return['currency_symbol'])){
                    $return['currency_symbol'] = $item['currency_symbol'];
                }

                $item['subtotal'] = $item['product_price'] * $item['product_quantity'];
                $item['tax'] = ((int)($item['subtotal'] * $order_store['tax'])) / 100.0;

                // coupon
                if(!$coupon_used){
                    if($grp_coupon && $grp_coupon['price_offer_type'] == FLAT_VALUE_OFF){
                        $dis = min($item['product_price'], $left_grp_discount); // only one product
                        $item['discount'] = $return['currency_symbol'] . $dis;
                        $item['subtotal'] = $item['subtotal'] - $dis;
                        $left_grp_discount = $left_grp_discount - $dis;
                        $coupon_used = TRUE;
                        if($grp_coupon['free_shipping']){
                            $coupon_free_shipping_info = array(TRUE, $item['store_id'], $item['product_id']);
                        }
                    } else if($grp_coupon && $grp_coupon['price_offer_type'] == PERCENTAGE_OFF){
                        $item['discount'] = $grp_coupon['price_off']."%";
                        $item['subtotal'] = $item['subtotal'] - ((int)($item['product_price'] * $grp_coupon['price_off'])) /100.0;
                        $coupon_used = TRUE;
                        if($grp_coupon['free_shipping']){
                            $coupon_free_shipping_info = array(TRUE, $item['store_id'], $item['product_id']);
                        }
                    } else if($order_coupon && $order_coupon['price_offer_type'] == FLAT_VALUE_OFF){
                        $dis = min($item['product_price'], $left_order_discount);
                        $item['discount'] = $return['currency_symbol'] . $dis;
                        $item['subtotal'] = $item['subtotal'] - $dis;
                        $left_grp_discount = $left_grp_discount - $dis;
                        $coupon_used = TRUE;
                        if($order_coupon['free_shipping']){
                            $coupon_free_shipping_info = array(TRUE, $item['store_id'], $item['product_id']);
                        }
                    } else if($order_coupon && $order_coupon['price_offer_type'] == PERCENTAGE_OFF){
                        $item['discount'] = $order_coupon['price_off']."%";
                        $item['subtotal'] = $item['subtotal'] - ((int)($item['product_price'] * $order_coupon['price_off'])) /100.0;
                        $coupon_used = TRUE;
                        if($order_coupon['free_shipping']){
                            $coupon_free_shipping_info = array(TRUE, $item['store_id'], $item['product_id']);
                        }
                    } else if(!empty($item['coupon_code'])){
                        $item_coupon = BaseModel::findCachedOne(
                            CacheKey::q($dbconfig->account->name . ".mycoupon?code=" . $item['coupon_code']));
                        if($item_coupon && $item_coupon['price_offer_type'] == FLAT_VALUE_OFF){
                            $dis = min($item['product_price'], $item_coupon['price_off']);
                            $item['discount'] = $return['currency_symbol'] . $dis;
                            $item['subtotal'] = $item['subtotal'] - $dis;
                            $coupon_used = TRUE;
                            if($item_coupon['free_shipping']){
                                $coupon_free_shipping_info = array(TRUE, $item['store_id'], $item['product_id']);
                            }
                        } else if($item_coupon && $item_coupon['price_offer_type'] == PERCENTAGE_OFF){
                            $item['discount'] = $item_coupon['price_off']."%";
                            $item['subtotal'] = $item['subtotal'] - ((int)($item['product_price'] * $item_coupon['price_off'])) /100.0;
                            $coupon_used = TRUE;
                            if($item_coupon['free_shipping']){
                                $coupon_free_shipping_info = array(TRUE, $item['store_id'], $item['product_id']);
                            }
                        }
                    } else {
                        $item['discount'] = "-";
                    }
                }
                // coupon end

                $items_by_store[$item['store_id']][] = $item;
                if(empty($return['currency_symbol'])){
                    $return['currency_symbol'] = $item['currency_symbol'];
                }
                $summary_by_store[$item['store_id']]['price_total'] += $item['subtotal'];
                $summary_by_store[$item['store_id']]['tax_total'] += $item['tax'];
                //$summary_by_store[$item['store_id']]['price_total'] += $item['product_shipping'];
                $summary_by_store[$item['store_id']]['product_ids'][] = $item['product_id'];
                $summary_by_store[$item['store_id']]['product_cnt'] += $item['product_quantity'];
            }

            $price = $summary_by_store[$order['store_id']]['price_total'];
            $shipping = $summary_by_store[$order['store_id']]['shipping'];
            $tax = $summary_by_store[$order['store_id']]['tax_total'];
            $summary_by_store[$order['store_id']]['total'] = $price + $shipping + $tax;

            if($save_summary) {
                //save total price into myorder
                $mo2save = new Myorder($account_dbobj, $order['id']);
                $mo2save->setPrice($price);
                $mo2save->setShipping($shipping);
                $mo2save->setTax($tax);
                $mo2save->setTotal($price + $shipping + $tax);
                $mo2save->save();
            }
        }
        // get shipping options for stores
        foreach($summary_by_store as $store_id => $store){
            $store_dbobj = DBObj::getStoreDBObjById($store_id);
            // Domestic, International
            $so_dest = "Domestic";
            $real_store =  BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name . ".store?id=" . $store_id));
            if($dest != $real_store['country']){
                $so_dest = "International";
                if($real_store['no_international_shipping']){
                    // no suitable shipping options
                    $summary_by_store[$store_id]['shipping_options'] = array();
                    $return['errors'][] = "No international shipping options available for products from store " . $real_store['name'];
                    continue;
                }
            }
            $shipping_options = ShippingOptionsMapper::getShippingOptionsForOrder(
                $store_dbobj, $store['product_ids'], $so_dest);
            $cnt = $summary_by_store[$store_id]['product_cnt'];
            foreach($shipping_options as $idx => $so){
                $shipping_options[$idx]['shipping_price'] = $so['base'] + $so['additional'] * ($cnt-1);
                //if(empty2($summary_by_store[$order['store_id']]['shipping'])){
                if($so['name'] == 'Standard'){
                    $summary_by_store[$store_id]['shipping'] = $shipping_options[$idx]['shipping_price'];
                }
            }
            $summary_by_store[$store_id]['shipping_options'] = $shipping_options;
            if(empty($summary_by_store[$store_id]['shipping_options'])){
                $return['errors'][] = "No available shipping options for products from store " . $real_store['name'];
            }
        }

        //$return['items'] = $order_items;
        $return['items_by_store'] = $items_by_store;
        $return['store_summaries'] = $summary_by_store;
        $return['order_ids_by_store'] = $order_ids_by_store;
        $return['shipping_total'] = array_reduce(
            $summary_by_store,
            function($l, $r){return $l + $r['shipping'];});
        $return['price_total'] = array_reduce(
            $summary_by_store,
            function($l, $r){return $l + $r['price_total'];}, 0);
        $return['tax_total'] = array_reduce(
            $summary_by_store,
            function($l, $r){return $l + $r['tax_total'];}, 0);
        $return['total'] = $return['price_total'] + $return['shipping_total'] + $return['tax_total'];

        if($save_summary) {
            $myorder_grp->setTotal($return['total']);
            $myorder_grp->setPrice($return['price_total']);
            $myorder_grp->setTax($return['tax_total']);
            $myorder_grp->setShipping($return['shipping_total']);
            $myorder_grp->save();
        }

        $this->status = 0;
        $this->response = $return;
    }

    public static function send_receipt_email($account_dbobj, $job_dbobj, $myorder_grp) {
        global $shopinterest_config, $dbconfig;

        $user = BaseModel::findCachedOne($dbconfig->account->name . ".user?id=" . $myorder_grp->getUserId());

        // prepare data
        $service = NativeCheckoutService::getInstance();
        $service->setMethod("myorder_summary");
        $params = array(
            'account_dbobj' => $account_dbobj,
            'order_group' => $myorder_grp,
        );
        $service->setParams($params);
        $service->call();
        $response = $service->getResponse();

        $data_mustache = array();
        foreach ($response['items_by_store'] as $store_id => $items){
            $row = array();
            $row['store_id'] = $store_id;
            $row['store_url'] = $items[0]['store_url'];
            $row['store_name'] = $items[0]['store_name'];
            $row['items'] = $items;
            $row['price_total'] = $response['store_summaries'][$store_id]['price_total'];
            $data_mustache[] = $row;
        }

        // send email to shopper
        $service = new EmailService();
        $service->setMethod('create_job');
        $service->setParams(array(
            'to' => $user['username'],
            'from' => $shopinterest_config->support->email,
            'type' => NATIVE_CHECKOUT_RECEIPT,
            'data' => array(
                'site_url' => getURL(),
                'order_id' => $myorder_grp->getId(),
                'first_name' => _u($user['first_name']),
                'order_num' => $myorder_grp->getOrderNum(),
                'order' => $myorder_grp->data(),
                'currency_symbol' => $response['currency_symbol'],
                //'items_by_store' => $response['items_by_store'],
                //'store_summaries' => $response['store_summaries'],
                'items_data' => $data_mustache,
                'price_total' => $response['price_total'],
                'shipping_total' => $response['shipping_total'],
                'tax_total' => $response['tax_total'],
                'total' => $response['total'],
            ),
            'job_dbobj' => $job_dbobj,
        ));
        $service->call();

        // send email to merchants
        foreach ($response['items_by_store'] as $store_id => $items) {
            $store = BaseModel::findCachedOne($dbconfig->account->name . ".store?id=" . $store_id);
            $merchant = BaseModel::findCachedOne($dbconfig->account->name . ".user?id=" . $store['uid']);
            $order_id = $response['store_summaries'][$store_id]['order_id'];
            $shipping = $response['store_summaries'][$store_id]['shipping'];
            $order = BaseModel::findCachedOne($dbconfig->account->name . ".myorder?id=" . $order_id);
            $so_id = 0;
            if(empty($so_id)){
                $shipping_opt = array("name" => "Standard");
            }else{
                $shipping_opt = BaseModel::findCachedOne($dbconfig->store->name . "_" . $store_id . ".shipping_option?id=" . $so_id);
            }
            $service = new EmailService();
            $service->setMethod('create_job');
            $service->setParams(array(
                'to' => $merchant['username'],
                'from' => $shopinterest_config->support->email,
                'type' => NATIVE_CHECKOUT_MERCHANT_RECEIPT,
                'data' => array(
                    'site_url' => getURL(),
                    'order_url' => getURL("/selling/orders/detail?id=" . $order_id),
                    'store_url' => $items[0]['store_url'],
                    'store_name' => $items[0]['store_name'],
                    'order' => $order,
                    'store_id' => $store_id,
                    'shipping' => $shipping,
                    'shipping_option' => $shipping_opt,
                    'items' => $items,
                    'first_name' => _u($merchant['first_name']),
                    'order_grp' => $myorder_grp->data(),
                    'currency_symbol' => $response['currency_symbol'],
                    'store_summaries' => $response['store_summaries'][$store_id],
                ),
                'job_dbobj' => $job_dbobj,
            ));
            $service->call();
        }
    }
}
