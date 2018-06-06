<?php

class Picture extends BaseModel {

    
    public function _internal_save($old_data, $auto_sync_list) {
        $is_new= empty($this->_id);
        parent::_internal_save($old_data, $auto_sync_list);
        if($is_new) return;
        GlobalProductsService::sync($this->_dbobj->getStoreId(), PicturesMapper::getProductId($this->_dbobj, $this->_id));       
    }
    
}


