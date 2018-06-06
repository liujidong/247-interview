<?php

class CustEmail extends BaseModel {

    public function setEmail($email) {
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->_email = $email;
            return true;
        } else {
            return false;
        }
    }
    
}