<?php

class SearchProductConvertedPicturesMapper {
    
    public static function remove_useless_data ($dbobj, $search_product_id, $picture_id) {
        $sql1 = "delete from 
            search_product_converted_pictures 
            where search_product_id = $search_product_id and 
            picture_id = $picture_id";
        $dbobj->query($sql1);        
    }
}
