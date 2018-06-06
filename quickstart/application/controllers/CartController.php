<?php

class CartController extends BaseController {

    public function init() {
        /* Initialize action controller here */

    }

    public function indexAction() {
        global $dbconfig;
        $user_id = $this->user_session->user_id;
        $currency = NULL;
        $coupon = NULL;
        $coupon_used = FALSE;

        $cart_id = CartsMapper::findCurrentCartForUser($this->account_dbobj, $user_id);
        $cart = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name . ".cart?id=$cart_id"));
        $this->view->cart = $cart;
        if(!empty($cart['coupon_code'])){
            $coupon = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name . ".mycoupon?code=" . $cart['coupon_code']));
        }

        $products = CartsMapper::getProductsInCart($this->account_dbobj, $cart_id);
        if(!empty($products) && $products[0]['dealer'] == 'amazon'){
            $this->view->dealer = 'amazon';
        } else {
            $this->view->dealer = 'local';
        }
        $this->view->products = $products;
        $products_by_store = array();
        $total_price = 0;

        if(!empty($coupon) && $coupon['scope'] == SITE){
            if($coupon['price_offer_type'] == PERCENTAGE_OFF){
                foreach($products as $p) {
                    if($coupon_used) break; // 1 coupon per cart
                    if(empty($currency)) $currency = $p['currency'];
                    $p['discount'] =  $coupon['price_off'] ."%";
                    $st = $p['price'] * (100 - $coupon['price_off']) / 100.0;
                    $st = $st + ($p['price'] * ($p['quantity'] - 1));
                    $p['subtotal'] = $st;
                    $products_by_store[$p['store_id']][] = $p;
                    $total_price = $st + $total_price;
                    $coupon_used = TRUE;
                }
            } else if($coupon['price_offer_type'] == FLAT_VALUE_OFF) {
                $left_dis = $coupon['price_off'];
                foreach($products as $p) {
                    if($coupon_used) break; // 1 coupon per cart
                    if(empty($currency)) $currency = $p['currency'];
                    $st = $p['price'] * $p['quantity'];
                    /*
                    $dis = min($st, $left_dis);
                    $p['discount'] = currency_symbol($currency) . $dis;
                    $st = $p['price'] * $p['quantity'] - $dis;
                    */
                    $dis = min($p['price'], $left_dis);
                    $p['discount'] = currency_symbol($currency) . $dis;
                    $st = $st - $dis;

                    $left_dis = $left_dis - $dis;
                    $p['subtotal'] = $st;
                    $products_by_store[$p['store_id']][] = $p;
                    $total_price = $st + $total_price;
                    $coupon_used = TRUE;
                }
            }
        } else if(!empty($coupon) && $coupon['scope'] == STORE){
            if($coupon['price_offer_type'] == PERCENTAGE_OFF){
                foreach($products as $p) {
                    if($coupon_used) break; // 1 coupon per cart
                    if(empty($currency)) $currency = $p['currency'];
                    $p['discount'] = "0";
                    if($coupon['store_id'] == $p['store_id']){
                        $p['discount'] =  $coupon['price_off'] ."%";
                        $st = $p['price'] * (100 - $coupon['price_off']) / 100.0;
                        $st = $st + ($p['price'] * ($p['quantity'] - 1));
                        $coupon_used = TRUE;
                    }else{
                        $st = $p['price'] * $p['quantity'];
                    }
                    $p['subtotal'] = $st;
                    $products_by_store[$p['store_id']][] = $p;
                    $total_price = $st + $total_price;
                }
            } else if($coupon['price_offer_type'] == FLAT_VALUE_OFF) {
                $left_dis = $coupon['price_off'];
                foreach($products as $p) {
                    if($coupon_used) break; // 1 coupon per cart
                    if(empty($currency)) $currency = $p['currency'];
                    $st = $p['price'] * $p['quantity'];
                    $p['discount'] = "0";
                    if($coupon['store_id'] == $p['store_id']){
                        $dis = min($p['price'], $left_dis);
                        $p['discount'] = currency_symbol($currency) . $dis;
                        $st = $p['price'] * $p['quantity'] - $dis;
                        $left_dis = $left_dis - $dis;
                        $coupon_used = TRUE;
                    }
                    $p['subtotal'] = $st;
                    $products_by_store[$p['store_id']][] = $p;
                    $total_price = $st + $total_price;
                }
            }
        } else { // maybe no coupon or product coupon
            foreach($products as $p) {
                if(empty($currency)) $currency = $p['currency'];
                if(!empty($p['coupon_code'])){
                    $coupon = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name . ".mycoupon?code=" . $p['coupon_code']));
                    if($coupon['price_offer_type'] == PERCENTAGE_OFF){
                        if(!$coupon_used){ // 1 coupon per cart
                            $p['discount'] =  $coupon['price_off'] ."%";
                            $st = $p['price'] * (100 - $coupon['price_off']) / 100.0;
                            $st = $st + ($p['price'] * ($p['quantity'] - 1));
                            $p['subtotal'] = $st;
                            $coupon_used = TRUE;
                        }
                    } else if($coupon['price_offer_type'] == FLAT_VALUE_OFF){
                        if(!$coupon_used){ // 1 coupon per cart
                            $st = $p['price'] * $p['quantity'];
                            $dis = min($p['price'], $coupon['price_off']);
                            $p['discount'] = currency_symbol($currency) . $dis;
                            $st = $p['price'] * $p['quantity'] - $dis;
                            $coupon_used = TRUE;
                        }
                    }
                    $products_by_store[$p['store_id']][] = $p;
                    $total_price = $st + $total_price;
                } else {
                    $p['discount'] = 0;
                    $st = $p['price'] * $p['quantity'];
                    $p['subtotal'] = $st;
                    $products_by_store[$p['store_id']][] = $p;
                    $total_price = $st + $total_price;
                }
            }
        }

        $this->view->products_by_store = $products_by_store;
        $this->view->total_price =  currency_symbol($currency) . $total_price;
        $this->view->coupon = $coupon;
    }

}
