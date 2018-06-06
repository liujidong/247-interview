<?php

class Customer extends BaseModel {

    public function setFirstName($first_name) {
        $this->_first_name = trim($first_name);
    }
    
    public function setLastName($last_name) {
        $this->_last_name = trim($last_name);
    }
}



