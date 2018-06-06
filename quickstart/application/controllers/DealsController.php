<?php

class DealsController extends BaseController {

    public function init() {

    }

    public function indexAction() {
        $featured_deal_coupons = MycouponsMapper::get_deal_coupons($this->account_dbobj, 'admin');
        foreach($featured_deal_coupons as $i => $coupon){
            $ck = getStoreDBName($coupon['store_id']) . ".product?id=" . $coupon['product_id'];
            $featured_deal_coupons[$i]['product'] = BaseModel::findCachedOne($ck);

            $featured_deal_coupons[$i]['original_price'] = format_price(
                $coupon['currency'],
                $featured_deal_coupons[$i]['product']['price']
            );
            if($coupon['price_offer_type'] == FLAT_VALUE_OFF){
                $featured_deal_coupons[$i]['discount_price'] = $featured_deal_coupons[$i]['product']['price'] - $coupon['price_off'];
                $featured_deal_coupons[$i]['literal_off'] = format_price($coupon['currency'], $coupon['price_off']);
            } else { // PERCENTAGE_OFF
                $featured_deal_coupons[$i]['discount_price'] = format_price(
                    $coupon['currency'],
                    $featured_deal_coupons[$i]['product']['price'] *(100 - $coupon['price_off']) / 100.00
                );
                $featured_deal_coupons[$i]['literal_off'] = $coupon['price_off'] . " %";
            }
        }
        $this->view->featured_deals = $featured_deal_coupons;
        //dddd($featured_deal_coupons);

        $merchant_deal_coupons = MycouponsMapper::get_deal_coupons($this->account_dbobj, 'merchant');
        foreach($merchant_deal_coupons as $i => $coupon){
            $ck = getStoreDBName($coupon['store_id']) . ".product?id=" . $coupon['product_id'];
            $merchant_deal_coupons[$i]['product'] = BaseModel::findCachedOne($ck);

            $merchant_deal_coupons[$i]['original_price'] = format_price(
                $coupon['currency'],
                $merchant_deal_coupons[$i]['product']['price']
            );
            if($coupon['price_offer_type'] == FLAT_VALUE_OFF){
                $merchant_deal_coupons[$i]['discount_price'] = format_price(
                    $coupon['currency'],
                    $merchant_deal_coupons[$i]['product']['price'] - $coupon['price_off']
                );
                $merchant_deal_coupons[$i]['literal_off'] = format_price($coupon['currency'], $coupon['price_off']);
            } else { // PERCENTAGE_OFF
                $merchant_deal_coupons[$i]['discount_price'] = format_price(
                    $coupon['currency'],
                    $merchant_deal_coupons[$i]['product']['price'] *(100 - $coupon['price_off']) / 100.00
                );
                $merchant_deal_coupons[$i]['literal_off'] = $coupon['price_off'] . " %";
            }

        }
        $this->view->merchant_deals = $merchant_deal_coupons;
    }

}
