<?php

class SalesNetworkService extends BaseService {
    
    public function getSalesReport(){
        
        $associate_id = $this->params['associate_id'];
        $account_dbobj = $this->params['account_dbobj'];
        $page_num = isset($this->params['page_num']) ? (int)$this->params['page_num'] :0;
        
        $this->response = SalesMapper::getSalesReport($associate_id, $account_dbobj, $page_num);        
    }
    
    public function getProducts() {

        $associate_id = $this->params['associate_id'];
        $account_dbobj = $this->params['account_dbobj'];
        $page_num = isset($this->params['page_num']) ? (int)$this->params['page_num'] :0;
        
        $this->response = SalesMapper::getProducts($associate_id, $account_dbobj, $page_num);         
    }
		
}


