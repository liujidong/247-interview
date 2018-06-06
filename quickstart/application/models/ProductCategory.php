<?php

class ProductCategory extends BaseModel {
    
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
    
}


