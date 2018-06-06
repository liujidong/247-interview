<?php

class PaypalAccount extends BaseModel {

    public function setUsername($username) {
        if(filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $this->_username = $username;
            return true;
        } else {
            return false;
        }
    }
    
}


