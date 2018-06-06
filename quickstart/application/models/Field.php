<?php

class Field extends BaseModel {

    public function setQuantity($quantity) {
        $this->_quantity = intval2($quantity);
        if($this->_quantity < 0) $this->_quantity = 0;
        return true;
    }
    
}
