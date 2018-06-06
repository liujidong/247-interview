<?php

class PinterestAccount extends BaseModel {

    public function setUsername($username) {
        $username = $this->sanitizeUsername($username);
        if(validate($username, 'pinterest_username')) {
            $this->_username = $username;
            return true;
        } else {
            return false;
        }
    }
    
    private function sanitizeUsername($username) {
        return strtolower(join('_', explode(' ', $username)));
    }
    
    public function setExternalId() {
        $this->_external_id = uniqid();
        return true;
    }
    
}


