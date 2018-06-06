<?php

class ProductCategoryMapper {
    public static function getCategories($dbobj,$status=CREATED){
        $sql = "select pc.id, pc.category, pc.description from product_categories pc where status=$status order by pc.category asc";
        
        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
    
    public static function deleteCategory($category, $dbobj) {
        $sql = "delete from product_categories where category='$category'";
        return $dbobj->query($sql);
    }
}
?>