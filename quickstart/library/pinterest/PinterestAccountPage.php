<?php
class PinterestAccountPage {
    private $username;
    private $page_num = 1;
    private $next_page_url = '';	


    public function __construct($username) {
        $this->username = $username;
    }

    // returns the next page of boards
    public function getNext() {
        if(!empty($this->next_page_url) || $this->page_num === 1 ) {
            $response = PinterestScraper::getBoards($this->username, $this->next_page_url);
            $this->next_page_url = $response['next_page_url'];
            $this->page_num++;
            return $response['boards'];
        } else {
            return false;
        }
    }
    
    public function getAccountInfo() {
        return PinterestScraper::getAccountInfo($this->username);
    }
    
    
    
}