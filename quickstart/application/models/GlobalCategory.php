<?php

class GlobalCategory extends BaseModel {

    public function setName($name) {
        
        if(strlen($name) > 255) {
            return false;
        }
        $this->_name = $name;
        return true;
    }
}


