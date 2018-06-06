<?php

class SalesMapper {
    
    public static function getSalesReport($associate_id, $dbobj, $page_num = 1){

        if($page_num === 0) {
            $limit = '';
        } else {
            $start = SALESNETWORK_PRODUCT_NUM_PER_PAGE * ( $page_num - 1);
            $limit = 'limit '.$start.', '.SALESNETWORK_PRODUCT_NUM_PER_PAGE;
        }
        
        $sql = "select a.aid,sum(s.product_quantity) as total_sales, if(sum(s.commission_amt) is null, 0, round(sum(s.commission_amt),2)) as commission_amt, sp.store_id as store_id, sp.product_id as product_id, sp.product_price as product_price, sp.product_commission as product_commission,
                ap.clicks as clicks,
                sp.pic_urls as pic_urls,st.subdomain as store_subdomain,sp.product_name as product_name,
                st.currency
                from sales s
                join sale_payments sps on (s.id = sps.sale_id)
                join payment_items pi on (sps.payment_item_id = pi.id) 
                right join associates_products ap on (s.associate_id=ap.associate_id and s.store_id=ap.store_id and s.product_id=ap.product_id and s.status=".PROCESSED.")
                join search_products sp on (ap.store_id=sp.store_id and ap.product_id=sp.product_id)
                join associates a on (a.id=ap.associate_id)
                join stores st on (st.id = sp.store_id)
                where ap.associate_id=$associate_id and (ap.clicks!=0 or s.id is not null)
                group by ap.associate_id,ap.store_id,ap.product_id
                order by s.id desc $limit";

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
                $store_url = getStoreUrl($record['store_subdomain']);
                $product_name = $record['product_name'];
                $record['store_url'] = $store_url;
                $record['product_url'] = $store_url."/products/item?id=".$record['product_id'];
                $record['product_aff_url'] = $store_url."/products/item?id=".$record['product_id'].'&aid='.$record['aid'];
                $record['product_share_url'] = $store_url."/products/item?id=".$record['aid'].'_'.$record['product_id'];
                $record['product_name_shorten'] = shorten_link($product_name, $record['product_url']);
                $record['product_aff_name'] = shorten_link($product_name, $record['product_aff_url'], 100);
                $record['currency_symbol'] = currency_symbol($record['currency']);
                $return[] = $record;
            }
        }
        return $return;
    }
    
    public static function getSalesCnt($associate_id, $dbobj) {
        
        $sql = "select count(*) as cnt from associates_products where associate_id=$associate_id and clicks!=0 ";
        $cnt = 0;

        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }
        return $cnt;
    }
       
    public static function getProducts($associate_id,  $dbobj, $description = '', $commission_start = 0, $commission_end = 0, $price_start = 0,$price_end = 0, $page_num = 1){
        
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
        
        $sql = "select ap.* ,sp.*,a.aid, st.currency, st.subdomain as store_subdomain
                from associates_products ap
                join search_products sp on (ap.store_id=sp.store_id and ap.product_id=sp.product_id)
                join associates a on (a.id=ap.associate_id)
                join stores st on (st.id = ap.store_id)
                where ap.associate_id=$associate_id and sp.status=0 and sp.product_commission!=0 and st.optin_salesnetwork=".ACTIVATED.". and ap.status=0 and sp.product_quantity>0 $search_desc $commission_range $price_range
                group by ap.id 
                order by ap.id desc $limit";

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
                $store_url = getStoreUrl($record['store_subdomain']);
                $product_name = $record['product_name'];
                $record['store_url'] = $store_url;
                $record['product_url'] = $store_url."/products/item?id=".$record['product_id'];
                $record['product_aff_url'] = $store_url."/products/item?id=".$record['product_id'].'&aid='.$record['aid'];
                $record['product_share_url'] = $store_url."/products/item?id=".$record['aid'].'_'.$record['product_id'];
                $record['product_name_shorten'] = shorten_link($product_name, $record['product_url']);
                $record['product_aff_name'] = shorten_link($product_name, $record['product_aff_url'], 1000);
                $record['currency_symbol'] = currency_symbol($record['currency']);
                $return[] = $record;  
                
            }
        }
        return $return;
    }

    public static function getProductCnt($associate_id,  $dbobj, $description = '', $commission_start = 0, $commission_end = 0, $price_start = 0,$price_end = 0){
        
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

        $cnt = 0;
        
        $sql = "select count(*) as cnt from associates_products ap 
                join search_products sp on (ap.store_id=sp.store_id and ap.product_id=sp.product_id)
                join stores st on (st.id = sp.store_id)
                join associates a on (a.id=ap.associate_id)
                where ap.associate_id=$associate_id and sp.status=0 and sp.product_commission!=0 and st.optin_salesnetwork=".ACTIVATED." and ap.status=0 and sp.product_quantity>0 $search_desc $commission_range $price_range";

        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }
        return $cnt;
    }    
    
}
?>