<?php

class MyorderGroup extends BaseModel {

    public function getOrderNum(){
        return "#0623-NC-GRP" . sprintf("%06d", $this->getId());
    }

}
