<?php

class Shopper extends BaseModel {
    
    public function setUsername($username) {
        if(filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $this->_username = $username;
            return true;
        } else {
            return false;
        }
    }
    
    public function setPassword($password) {
        if(validate($password, 'password')) {
            $this->_password = md5($password);
            return true;
        } else {
            return false;
        }
    }
    
}


