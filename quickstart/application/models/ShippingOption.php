<?php

class ShippingOption extends BaseModel {

    function format($data){
        json2AssocArray($data['shipping_destinations']);

        return $data;
    }
}