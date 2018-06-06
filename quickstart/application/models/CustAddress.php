<?php

class CustAddress extends BaseModel {
    
    public function setFormatted($formatted) {
        $this->_formatted = trim($formatted);
    }
    
    public function setStreet($street) {
        $this->_street = trim($street);
    }
    
    public function setCity($city) {
        $this->_city = trim($city);
    }
    
    public function setRegion($region) {
        $this->_region = $region;
    }
    
    public function setCountry($country) {
        $this->_country = $country;
    }
    
    public function setPostalCode($postal_code) {
        $this->_postal_code = $postal_code;
    }
}