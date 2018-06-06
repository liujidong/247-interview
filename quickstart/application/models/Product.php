<?php

class Product extends BaseModel {

    public function setName($name) {
        if(empty($name)) {
            return false;
        }
        $this->_name = $name;
        return true;
    }

    public function setDescription($desc) {
        if(empty($desc)) {
            return false;
        }
        $this->_description = $desc;
        return true;
    }

    public function setQuantity($quantity) {
        $this->_quantity = intval2($quantity);
        if($this->_quantity < 0) $this->_quantity = 0;
        return true;
    }

    public function setPrice($price) {
        if(empty($price)) {
            return false;
        }
        $this->_price = round(floatval2($price), 2);
        return true;
    }

    public function setCommission($commission) {
        if(empty($commission) || $commission > 100 ||
        $commission < 0) {
            return false;
        }
        $commission = default2Int($commission);

        if($commission < 3) $commission = 3;

        $this->_commission = round(floatval2($commission), 2);
        return true;
    }

    public function setShipping($shipping) {
        $this->_shipping = round(floatval2($shipping), 2);
        return true;
    }

    public function setGlobalCategoryId($global_category_id) {
        if(empty($global_category_id)) {
            return false;
        }
        $this->_global_category_id = $global_category_id;
        return true;
    }
    
    public function setFeaturedScore($score) {
        $score = intval($score);
        $this->_featured_score = $score<0?0:$score;
    }
    
    public function _internal_save($old_data, $auto_sync_list) {
        parent::_internal_save($old_data, $auto_sync_list);
        // sync tags in a rough way
        DAL::delete(lck_store_tags($this->_dbobj->getDBname()));
        GlobalProductsService::sync($this->_dbobj->getStoreId(), $this->_id);
    } 
    
    
    public static function format($data, $ck){
        json2AssocArray($data['custom_fields']);
        json2AssocArray($data['shipping_options']);
        json2AssocArray($data['shipping_destinations']);
        $data['store_url'] = getStoreUrl($data['store_subdomain']);
        // pictures
        json2AssocArray($data['pictures']);
        foreach($data['pictures'] as $i => $p){
            if($i>40) continue;
            if(empty($p['name'])) continue;
            foreach(array(45, 70, 192, 236, 550, 736) as $w){
                $opt = array("width" => $w, "crop" => "fill");
                if($w == 45 || $w == 550){
                    $opt['height'] = $w;
                }
                $img_url = cloudinary_product_picture(
                    $data, $p['name'], $opt
                );
                if($w == 45){
                    $img_url = preg_replace("/h_45,w_45/","w_45,h_45", $img_url);
                }
                $data['pictures'][$w][$p['id']] = $img_url;
            }
        }
        $data['description'] = nl2br(strip_tags($data['description']));
        //ddd($data);
        return $data;
    }
}
