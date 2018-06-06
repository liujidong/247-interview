<?php

class Coupon extends BaseModel {

    public function setOfferDetails($offer_details) {
        $this->_offer_details = json_encode($offer_details);
        return true;
    }
    
    public function getOfferDetails() {
        return json_decode($this->_offer_details, true);
    }
    
    
    
    public function getPricePercentageOff() {
        // validate coupon: offer details
        $price_offer_type = $this->_price_offer_type;
        $offer_details = $this->_offer_details;
        $price_percentage_off = 0;
        if($price_offer_type == PERCENTAGE_OFF) {
            $price_percentage_off = floatval($offer_details['price']['percentage_off']);
            if(empty($offer_details['price']['percentage_off']) || $price_percentage_off<=0) {
                $price_percentage_off = 0;
            }
        }
        return $price_percentage_off;
    }
    
}

//id
//status
//code
//scope: site, store, product
//category: electronics, clothes
//price_offer_type: percentage_off, flat_value_off, bundle
//shipping_offer_type: percentage_off, flat_value_off, bundle 
//offer_details:
//{
//   price: {
//       percentage_off: 
//       flat_value_off:
//       bundle: {
//           min_quantity: 2
//           {
//               price: -1 // original price
//           },
//           {
//               price: 0
//           }
//
//       }
//   }
//   shipping: {
//       percentage_off:
//       flat_value_off:
//       bundle: {
//           min_quantity: 1
//           {
//               shipping: 0
//           }
//       } 
//   }
//}
//offer_description
//free_shipping
//quantity
//store_id
//product_id
//start_time
//end_time
//created
//updated