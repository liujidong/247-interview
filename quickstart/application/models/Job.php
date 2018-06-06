<?php

class Job extends BaseModel {

    // job type
    // 1 -- generate type 2 jobs for scraping board pages (input: board_id) -- daemon
    // 2 -- scrap a board based on a page and a subpage (input: board_id, page, subpage) -- daemon
    // 3 -- pinterest image uploader to S3 (input: dst, src, pinterest_account_id (optional), pinterest_board_id (optional))
    // 4 -- email processor (input: to, toname(optional), subject, text, from, fromname(optional))
    // 5 -- account scraper (pinterest_account_id)
    
    public function setData($data) {
        $this->_data = json_encode($data);
        return true;
    }
    
    public function getData() {
        return json_decode($this->_data, true);
    }
    
    // should be called after setType, setData, setStatus
    public function setHash1() {
        $this->_hash1 = md5($this->_type.$this->_data.$this->_status);
        return true;
    }
    
    public function save() {
        
        $this->setHash1();
        parent::save();
    }
    
    
}


