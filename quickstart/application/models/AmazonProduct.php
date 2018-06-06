<?php

class AmazonProduct extends BaseModel {

    public static function format($data, $ck){
        $data['short_name'] = substr($data['name'], 0, 10) . " ...";
        json2AssocArray($data['pictures']);
        $data['thumb_picture'] = reset($data['pictures']['45']);
        switch($data['featured']){
        case 1:
            $data['featured_name'] = "Slider Featured";
            break;
        case 2:
            $data['featured_name'] = "Category Featured";
            break;
        case 3:
            $data['featured_name'] = "Ad Featured";
            break;
        case 0:
        default:
            $data['featured_name'] = "Not Featured";
            break;
        }
        $data['store_name'] = "Marketplace";
        $data['store_url'] = getSiteMerchantUrl("/store/marketplace");
        $data['product_url'] = getSiteMerchantUrl("/store/marketplace/products/item?ASIN=" . $data['asin']);
        return $data;
    }
}
