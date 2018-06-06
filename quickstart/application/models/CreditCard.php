<?php

class CreditCard extends BaseModel {

    public function setCardNumber($card_number) {
        $this->_card_number = substr($card_number, -4);
    }
    
    // validation ...
    
}
