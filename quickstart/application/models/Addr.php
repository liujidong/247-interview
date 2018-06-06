<?php

class Addr extends BaseModel {
    
    // addr1, addr2, city, state, zip
    function standardize_mailing_address() {
        $addr1 = strtoupper(trim($addr1));
        $addr2 = strtoupper(trim($addr2));
        $city = strtoupper(trim($city));
        $state = strtoupper(trim($state));

        if(empty($addr1) || empty($city) || empty($state)) {

        }

    }
}


