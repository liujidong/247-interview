<?php

class Category extends BaseModel {

    public function setCategory($category) {
        $category = sanitize_string($category);
        $this->_category = $category;
        if(!empty($category)) {
            return true;
        }
        return false;
    }
    
    public function setDescription($description) {
        $description = trim(sanitize_words($description));
        if(empty($description)) {
            return false;
        }
        $this->_description = $description;
    }
    
    public function _internal_save($old_data, $auto_sync_list) {
        $is_new= empty($this->_id);
        parent::_internal_save($old_data, $auto_sync_list);
        if($is_new) return;
        $product_ids = CategoriesMapper::getProdutIds($this->_dbobj, $this->_id);
        foreach($product_ids as $product_id) {
            GlobalProductsService::sync($this->_dbobj->getStoreId(), $product_id);  
        }
        
             
    }
    
    
}


