<?php

class Mycoupon extends BaseModel {

    public static function format($data){

        switch ($data['scope']) {
        case SITE:
            $scope = "Site";
            break;
        case STORE:
            $scope = "Store";
            break;
        case PRODUCT:
            $scope = "Product";
            break;
        case AMAZON_PRODUCT:
            $scope = "Amazon Product";
            break;
        default:
            $scope = "";
        }

        $data['formatted_scope'] = $scope;

        switch ($data['price_offer_type']) {
        case 1:
            $type = "Percentage Off";
            break;
        case 2:
            $type = "Flat Value Off";
            break;
        case 3:
            $type = "Bundle";
            break;
        default:
            $type = "";
        }

        $data['formatted_price_offer_type'] = $type;

        if($data['free_shipping']) {
            $data['formatted_free_shipping'] = "Y";
        } else {
            $data['formatted_free_shipping'] = "N";
        }

        if($data['is_deal']) {
            $data['formatted_is_deal'] = "Y";
        } else {
            $data['formatted_is_deal'] = "N";
        }

        $data['start_time'] = format_data_time($data['start_time']);
        $data['end_time'] = format_data_time($data['end_time']);
        return $data;
    }

    public function setEndTime($end_time) {
        $this->_end_time = $end_time ." 23:59:59";
        return true;
    }
}

