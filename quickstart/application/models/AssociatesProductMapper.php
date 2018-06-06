<?php

class AssociatesProductMapper {   
    
    public static function addAssociatesProduct($associate_id, $store_id, $product_id, $dbobj){
        $sql = "insert into associates_products (associate_id, store_id, product_id, clicks) 
        values ($associate_id, $store_id, $product_id, 1) on duplicate key update id= LAST_INSERT_ID(id), clicks = clicks+1"; 
        return $dbobj->query($sql);
    }    
    
}

