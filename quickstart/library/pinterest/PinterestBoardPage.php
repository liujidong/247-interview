<?php
class PinterestBoardPage {
    private $id;
    private $page_num = 1;
    private $next_page_url = '';	


    public function __construct($id) {
        $this->id = $id;
    }

    // returns the next page of pins
    public function getNext() {
        if(!empty($this->next_page_url) || $this->page_num === 1 ) {
            $response = PinterestScraper::getPins($this->id, $this->next_page_url);
            $this->next_page_url = $response['next_page_url'];
            $this->page_num++;
            return $response['pins'];
        } else {
            return false;
        }
    } 
    
    public function getNextPageUrl() {
        return $this->next_page_url;
    }
    
    public function setNextPageUrl($next_page_url) {
        $this->next_page_url = $next_page_url;
    }
    
    public function getBoardInfo() {
        return PinterestScraper::getBoardInfo($this->id);
    }
}