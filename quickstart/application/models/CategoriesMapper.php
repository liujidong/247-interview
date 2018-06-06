<?php

class CategoriesMapper {

    public static function deleteCategoryByProductId($dbobj, $product_id) {

        $dbname = $dbobj->getDBName();
        $product_ck = get_product_ck($dbname, $product_id);
        $old_data = DAL::get($product_ck);

        $sql = "delete from products_categories where product_id = $product_id";
        $dbobj->query($sql);

        DAL::s($product_ck, $old_data);
    }
    
    public static function getProdutId($dbobj, $category_id) {
        $sql = "select id from products_categories where category_id = $category_id";

        $product_id = 0;
        if($res = $dbobj->query($sql)) {
            
            if($record = $dbobj->fetch_assoc($res)) {
                $product_id = $record['id'];
            }
        }

        return $product_id;
    }
    
    public static function getProdutIds($dbobj, $category_id) {
        $sql = "select product_id from products_categories where category_id = $category_id";

        $product_ids = array();
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $product_ids[] = $record['product_id'];
            }
        }

        return $product_ids;
    } 

    public static function getCachedObject($params, $dbobj) {
        $cond = $params['_cachekey']->conditionSQL("c");
        $deleted = DELETED;
        $sql = "select c.id, c.category, if(c.description='', c.category, c.description) as description,
             count(p.id) as product_cnt
             from
             categories c left join products_categories pc on (c.id=pc.category_id)
             left join products p on
             ( p.id=pc.product_id and p.name!='' and p.price>0 and p.quantity>0 and
               p.status != $deleted and p.global_category_id !=0 )
             where $cond and c.status != $deleted
             group by c.id";

        $return = array();
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
        }
        return $return;
    }

    public static function getCachedObjectList($params, $dbobj) {
        $ck = $params['_cachekey'];
        $dbname = $ck->getDBName();
        $keys = array();

        $tags = StoresMapper::getNonEmptyCategories($dbobj);
        foreach($tags as $tag) {
            $key = $dbname.".category?id=".$tag['id'];
            $keys[$key] = $tag['product_cnt'];
        }
        return $keys;
    }

}
