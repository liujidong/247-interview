<?php

class GlobalProductsMapper {

    public static function search($dbobj, $query,
            $page=1,
            $search_fields=array('product_name', 'product_description', 'product_global_category_path', 'product_tags')) {
        
        $query = query_words($query);
        
        $search_fields_count = count($search_fields);
        $where = '';
        $limit = ($page-1)*PRODUCT_NUM_PER_PAGE.', '.PRODUCT_NUM_PER_PAGE;
        foreach($search_fields as $i => $field) {
            $where .= " $field like '$query'";
            if($i !== $search_fields_count-1) {
                $where .= ' or ';
            }
        }
        $sql = 'select gp.*, s.subdomain as store_subdomain, s.currency as store_currency, s.name as store_name
            from global_products gp join stores s on (gp.store_id=s.id) where gp.product_status!='.DELETED.' and ('.$where.') order by gp.id limit '.$limit;
        $return = array('products' => array(), 'total' => 0);

        if ($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $record['product_pictures'] = json_decode($record['product_pictures'], true);
                $record['product_url'] = get_product_item_url($record['store_subdomain'], $record['product_id']);
                $record['name'] = $record['product_name'];
                $record['store_url'] = getStoreUrl($record['store_subdomain']);
                $return['products'][] = $record;
            }
        }
        
        $sql = 'select count(gp.id) as cnt
            from global_products gp join stores s on (gp.store_id=s.id) where '.$where;

        if ($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return['total'] = $record['cnt'];
            }
        }
        return $return;
    }

    public static function deleteProductsInStore($dbobj, $store_id){
        $sql = "delete from global_products where store_id = $store_id";
        $dbobj->query($sql);
    }
}
