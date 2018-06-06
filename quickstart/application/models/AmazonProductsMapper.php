<?php

class AmazonProductsMapper {

    public static function getCachedObject($params, $dbobj) {
        global $dbconfig;
        $account_dbname = $dbconfig->account->name;
        $product_id = default2Int($params['id']);
        $asin = default2String($params['asin']);
        $ck = $params['_cachekey'];

        $cond = "p.id = $product_id";
        if(!empty($asin)){
            $cond = "p.asin = '" . $asin . "'";
        }

        $sql = "select
                p.*
                from
                amazon_products p
                where
                $cond and p.status!=".DELETED;

        $return = array();

        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                if(empty($record['id'])) {
                    return $return;
                }
                $return = $record;

                $category_ck = CacheKey::q($account_dbname.'.global_category?id='.$record['global_category_id']);
                $category = BaseModel::findCachedOne($category_ck);
                $category_path = default2String($category['path']);

                // get root_category_id
                $return['root_category_id'] = !empty($category['parent_id']) ?
                    $category['parent_id'] : (isset($category['id']) ? $category['id'] : 0);
                $return['category_path'] = $category_path;
                $cat_levels = preg_split("/\s*>\s*/", $category_path);
                foreach($cat_levels as $i => $l) {
                    $return["category_l" . ($i+1)] = preg_replace("/&/", "__and__", trim($l));
                }
            }
        }
        return $return;
    }

    public static function getCachedObjectList($params, $dbobj) {
        global $dbconfig;

        $dbname = $dbconfig->account->name;
        $ck = $params['_cachekey'];
        $sort = $ck->getOrderInfo();
        $sort = $sort['orderby'];
        $label = $ck->getLabel();

        $keys = array();
        $products = array();

        $sql = "select id, updated from amazon_products where " . $ck->conditionSQL();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $products[] = $record;
            }
        }
        if(count($sort) != 1) { // no order, no score
            foreach($products as $p) {
                $key = $dbname.".amazon_product?id=".$p['id'];
                $keys[] = $key;
            }
        } else {
            $order_key = $sort[0];
            foreach($products as $p) {
                $score = 0;
                if($order_key == 'updated'){
                    $score = strtotime2($p['updated']);
                } else {
                    $score = isset($product[$order_key]) ? $product[$order_key] : 0;
                }
                $key = $dbname.".amazon_product?id=".$p['id'];
                $keys[$key] = $score;
            }
        }
        return $keys;
    }

}
