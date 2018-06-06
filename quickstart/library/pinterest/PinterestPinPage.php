<?php
class PinterestPinPage {
    private $id;	


    public function __construct($id) {
        $this->id = $id;
    }

    public function getPinInfo() {
        return PinterestScraper::getPinInfo($this->id);
    }  
}