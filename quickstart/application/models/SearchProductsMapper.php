

<?php

class SearchProductsMapper {
    
    
    public static function searchProducts($associate_id, $dbobj, $description = '', $commission_start = 0, $commission_end = 0, $price_start = 0,$price_end = 0, $page_num = 1){
        
        $search_desc = '';
        $commission_range = '';
        $price_range = '';
        
        if($page_num === 0) {
            $limit = '';
        } else {
            $start = SALESNETWORK_PRODUCT_NUM_PER_PAGE * ( $page_num - 1);
            $limit = 'limit '.$start.', '.SALESNETWORK_PRODUCT_NUM_PER_PAGE;
        }
        if(!empty($description)) {
            $search_desc = "and (sp.product_name like '%$description%' or sp.product_description like '%$description%')";
        }
        if(!empty($commission_end)) {
            $commission_range = "and (sp.product_commission >=$commission_start and sp.product_commission<= $commission_end)";
        }
        if(!empty($price_end)) {
            $price_range = " and (sp.product_price >=$price_start and sp.product_price<= $price_end)";
        }        
       
        $sql = "select * from
                (select sp.id, sp.status, sp.product_id, sp.product_status, sp.product_name,sp.product_description,
                 sp.product_size,sp.product_quantity,sp.product_price, sp.product_shipping,sp.product_start_date,
                 sp.product_end_date,sp.product_commission,sp.global_category_id, sp.category, sp.category_description,
                 sp.pic_ids, sp.pic_types, sp.pic_sources, sp.pic_urls, sp.store_id, ap.status as associates_product_status, s.currency, s.subdomain as store_subdomain
                 from search_products sp
                 left join associates_products ap on (sp.store_id=ap.store_id and sp.product_id=ap.product_id and ap.associate_id=$associate_id)
                 left join stores s on (s.id = sp.store_id)
                 where sp.status = 0 and sp.product_status !=".DELETED." and s.status=".ACTIVATED." and
                 sp.product_name!='' and sp.product_price!='' and
                 sp.product_quantity>0 and sp.excluded_in_search=0 and sp.product_commission!=0 and
                 s.optin_salesnetwork=".ACTIVATED." $search_desc $commission_range $price_range )
                mid_search_products
                where associates_product_status is null or associates_product_status=1 
                order by id desc $limit";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $urls = explode(',', $record['pic_urls']);
                
                foreach($urls as $u){
                    if(preg_match('/^.*(_t)\..*$/i', $u)){
                        $record['thumbnail_img'] = $u;
                    }
                    else if(preg_match('/^.*(_c)\..*$/i', $u)){
                        $record['closeup_img'] = $u;
                    }
                    else if(preg_match('/^.*(_b)\..*$/i', $u)){
                        $record['board_img'] = $u;
                    }
                    else if(preg_match('/^.*(_f)\..*$/i', $u)){
                        $record['mobile_img'] = $u;
                    }
                    else{
                        $record['board_img'] = $u;
                        $record['mobile_img'] = $u;
                        $record['closeup_img'] = $u;
                        $record['thumbnail_img'] = $u;
                        break;
                    }
                }
                //$record['board_img'] = get_valid_pinterest_image_url($record['board_img']);
                $record['product_url'] = get_product_item_url($record['store_subdomain'], $record['product_id']);
                $record['store_url'] = getStoreUrl($record['store_subdomain']);
                $record['product_description'] = shorten_description($record['product_description'], $record['product_url']);
                $record['product_name'] = shorten_link($record['product_name'], $record['product_url']);
                $record['currency_symbol'] = currency_symbol($record['currency']);
                $return[] = $record;
                
            }
        }
        return $return;
    }
    
    public static function searchProductCnt($associate_id, $dbobj, $description = '', $commission_start = 0, $commission_end = 0, $price_start = 0,$price_end = 0){
        
        $search_desc = '';
        $commission_range = '';
        $price_range = '';
        
        if(!empty($description)) {
            $search_desc = "and (sp.product_name like '%$description%' or sp.product_description like '%$description%')";
        }
        if(!empty($commission_end)) {
            $commission_range = "and (sp.product_commission >=$commission_start and sp.product_commission<= $commission_end)";
        }
        if(!empty($price_end)) {
            $price_range = " and (sp.product_price >=$price_start and sp.product_price<= $price_end)";
        }        
        
        $sql = "select count(*) as cnt from
                (select sp.*, ap.status as associates_product_status
                 from
                 search_products sp left join associates_products ap
                 on (sp.store_id=ap.store_id and sp.product_id=ap.product_id and ap.associate_id=$associate_id)
                 left join stores s
                 on (s.id = sp.store_id)
                 where sp.status = 0 and sp.product_status !=".DELETED." and s.status=".ACTIVATED." and
                 sp.product_name!='' and sp.product_price!='' and
                 sp.product_quantity>0 and sp.excluded_in_search=0 and sp.product_commission!=0 and
                 s.optin_salesnetwork=".ACTIVATED." $search_desc $commission_range $price_range ) mid_search_products
                where associates_product_status is null or associates_product_status=1 ";

        $cnt =0;
        
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }
        return $cnt;
    }    
    
    public static function getMigrateStoreProductsData($dbobj) {
        
        $return=array();
        
        $sql="select * from store_products";
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)){
                $return[]=$record;
            }
        }
        
        return $return;        
    }
    
    public static function getActiveProducts($dbobj, $page_num = 0, $categorized = 0) {
        
        $limit = ($page_num>=1) ? ' limit '.PRODUCT_NUM_PER_PAGE*($page_num-1) . ','.PRODUCT_NUM_PER_PAGE : '';
        $category_filter = empty($categorized) ? ' and sp.global_category_id=0' : ' and sp.global_category_id!=0';
        $category_order = empty($categorized) ? ' ' : ' ,sp.global_category_id';
        
        $sql = "select sp.id, sp.status, sp.product_id, sp.product_status, sp.product_name,sp.product_description,sp.product_size,sp.product_quantity,sp.product_price, sp.product_shipping,sp.product_start_date, sp.product_end_date,sp.product_commission,sp.global_category_id, sp.category, sp.category_description, sp.pic_ids, sp.pic_types, sp.pic_sources, sp.pic_urls, sp.store_id, s.subdomain as store_subdomain 
            from search_products sp
            join stores s on (s.id = sp.store_id)
            join search_product_converted_pictures spcp on (sp.id = spcp.search_product_id)
            where s.status = ".ACTIVATED." and sp.product_price!=0 and 
            sp.product_name != '' and sp.product_quantity != 0 and sp.product_status!=".DELETED." $category_filter
            group by sp.id
            order by store_id $category_order
            $limit";

        $return = array();
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $urls = explode(',', $record['pic_urls']);
                $record['picture_url'] = $urls[0];

                //$record['board_img'] = get_valid_pinterest_image_url($record['board_img']);
                $record['product_url'] = get_product_item_url($record['store_subdomain'], $record['product_id']);
                $record['store_url'] = getStoreUrl($record['store_subdomain']);
                $record['product_description'] = shorten_description($record['product_description'], $record['product_url']);
                $record['product_name'] = shorten_link($record['product_name'], $record['product_url']);   
                
                $return[] = $record;
             }
        }
        return $return;      
    } 
    
    public static function getActiveProductsCnt($dbobj, $categorized = 0) {
        
        $category_filter = empty($categorized) ? ' and sp.global_category_id=0' : ' and sp.global_category_id!=0';
        
        $sql = "select count(distinct sp.id) as cnt 
            from search_products sp
            join stores s on (s.id = sp.store_id)
            join search_product_converted_pictures spcp on (sp.id = spcp.search_product_id)
            where s.status = ".ACTIVATED." and sp.product_price!=0 and 
            sp.product_name != '' and sp.product_quantity != 0 and sp.product_status!=".DELETED." $category_filter
            ";

        $cnt = 0;

        if($res = $dbobj->query($sql, $dbobj)){
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }
        return $cnt;
    }
        
    public static function randomlyPickProducts($dbobj, $limit = 100) {
                
        $sql1 = "select max(id) as max_id from search_products";
        $max_id = 0;
        
        if($res = $dbobj->query($sql1, $dbobj)){
            if($record = $dbobj->fetch_assoc($res)) {
                $max_id = $record['max_id'];
            }
        }
        
        $query_cnt = 0;
        $return = array();
        $used_random_ids = array();
        
        do{
            $search_product_ids = array();
            $random_cnt = 0;
            
            while (count($search_product_ids) < $limit+50 && $random_cnt < $limit+100) {
                
                $random_id = rand(1, $max_id);

                if(!isset($used_random_ids[$random_id])) {
                    $used_random_ids[rand(1, $max_id)] = 1;
                    $search_product_ids[$random_id] = 1;
                }
                $random_cnt++;
            }

            $sql2 = "select sp.id as search_product_id, sp.status, sp.product_id, sp.product_status, sp.product_name,sp.product_description,sp.product_size,sp.product_quantity,sp.product_price, sp.product_shipping,sp.product_start_date, sp.product_end_date,sp.product_commission,sp.global_category_id, sp.category, sp.category_description, sp.pic_ids, sp.pic_types, sp.pic_sources, sp.pic_urls,s.id as store_id, s.*, s.subdomain as store_subdomain 
                from search_products sp
                join stores s on (sp.store_id = s.id)
                where sp.product_status=".CREATED." and 
                    s.status=".ACTIVATED." and 
                    sp.product_quantity>0 and sp.excluded_in_search=0 and
                    s.excluded_in_search=0 and 
                    sp.id in (".implode(',', array_keys($search_product_ids)).")"; 

            if($res = $dbobj->query($sql2)) {
                while($record = $dbobj->fetch_assoc($res)) {

                    $search_product_id = $record['search_product_id'];

                    $converted_pictures = SearchProductsMapper::getConvertedPictures($dbobj, $search_product_id);
                    $record = array_merge($record, $converted_pictures);

                    $record['product_url'] = get_product_item_url($record['store_subdomain'], $record['product_id']);
                    $record['store_url'] = getStoreUrl($record['store_subdomain']);
                    $record['store_logo'] = default_store_logo($record['converted_logo']); 
                    
                    $return[] = $record;
                }
            }
            
            $query_cnt++;
        } while (count($return) < $limit && $query_cnt <= 6);    
        
        return $return;        
    }       
    
    public static function getConvertedPictures($dbobj, $search_product_id) {
                
        $sql1 = "select  * 
                from 
                    search_product_converted_pictures
                where 
                    search_product_id = $search_product_id and converted_192 != ''
                order by 
                    picture_order, picture_id limit 1";
        
        $return = array();

        if($res1 = $dbobj->query($sql1)) {
            if($record1 = $dbobj->fetch_assoc($res1)) {
                $return = $record1;
            }                        
        } 
        
        return $return;
    }
    
    public static function getProducts($dbobj, $option = array()) {

        $exclude_in_search = isset($option['exclude_in_search']) ? $option['exclude_in_search'] : true;
        
        if(isset($option['page_num'])) {
            $limit = ' limit ';
            $page_num_intval = intval($option['page_num']);
            $page_num = $page_num_intval >=1 ? $page_num_intval : 1;
            $limit .= ( $page_num - 1 ) * PRODUCT_NUM_PER_PAGE. ", ".PRODUCT_NUM_PER_PAGE; 
        } else {
            $limit = ' limit 100'; 
        }

        $where = "where sp.product_status !=".DELETED." and s.status=".ACTIVATED." and
            sp.product_name!='' and sp.product_price!='' and
            sp.product_quantity > 0";

        if($exclude_in_search) {
            $where .= " and sp.excluded_in_search=0 and s.excluded_in_search=0";
        }
        
        if(isset($option['where'])) {
            $option_where = $option['where'];
                    
            if(is_string($option_where)) {
                $option_where = array($option_where);
            }

            $where .= " and sp. ".implode(' and ', $option_where);
        }
        
        $order_by = "";     
        // array('page_views' => 'desc', 'score' => 'desc)
        if(isset($option['orderby']) && is_array($option['orderby'])) {
            $option_orderby = $option['orderby'];
            
            $order_by = "order by";
            
            foreach ($option_orderby as $key => $value) {
                $order_by .= ' sp.'.$key. ' '.$value. ',';
                $order_by = trim($order_by, ',');
            }
        }
        
        $sql = "select s.*, sp.id as search_product_id,sp.*, s.name as store_name, s.subdomain as store_subdomain
            from search_products sp
            join stores s on (sp.store_id = s.id)
            $where    
            group by sp.id
            $order_by
            $limit";        

        $return = array();
        
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {

                $search_product_id = $record['search_product_id'];
                
                $converted_pictures = SearchProductsMapper::getConvertedPictures($dbobj, $search_product_id);
                $record = array_merge($record, $converted_pictures);
                $record['product_url'] = get_product_item_url($record['store_subdomain'], $record['product_id']);
                $record['store_url'] = getStoreUrl($record['store_subdomain']);
                $record['store_logo'] = default_store_logo($record['converted_logo']);
                $record['currency_symbol'] = currency_symbol($record['currency']);
                
                $return[] = $record;
            }
        }
        return $return;     
    } 
    
    public static function getProductsCnt($dbobj, $option = array()) {
        
        $where = "where sp.product_status !=".DELETED." and s.status=".ACTIVATED." and
            sp.product_name!='' and sp.product_price!='' and
            sp.product_quantity>0 and sp.excluded_in_search=0";
        
        if(isset($option['where'])) {
            $option_where = $option['where'];
                    
            if(is_string($option_where)) {
                $option_where = array($option_where);
            }

            $where .= " and sp. ".implode(' and ', $option_where);
        }
              
        $sql = "select count(distinct sp.id) as cnt
            from search_products sp
            join stores s on (sp.store_id = s.id)
            $where";        

        $cnt = array();
        
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }
        return $cnt;     
    }     
    
}
