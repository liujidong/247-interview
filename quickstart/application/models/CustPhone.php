<?php

class CustPhone extends BaseModel {

    public function setNumber($number) {
        if(!empty(trim($number))) {
            $this->_number = trim($number);
        } else {
            return false;
        }
        
    }
    
}