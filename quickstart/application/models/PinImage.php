<?php

class PinImage extends BaseModel {

    // overwiter setImage45 setImage70... here 
    public function __call($name, $arguments) {
        //parent::__call($name, $arguments);
        if(preg_match('/^setImage\d{2,3}$/', $name)) {
            if(isset($arguments[0]) && checkRemoteFile($arguments[0])) {
                $attribute_name = format_get_image($name);
                $this->$attribute_name = $arguments[0];
                return true;
            }
            return false;
        }
        return parent::__call($name, $arguments);
    }
}
