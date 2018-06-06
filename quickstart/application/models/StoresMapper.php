<?php

class StoresMapper {
    //status!=DELETE
    public static function getProductIds($dbobj,$status=CREATED) {

        $filter='';
        if($status!==IGNORE_STATUS){
            $filter=' where status ='.$status;
        }
        $sql = 'select id from products'.$filter.' order by id desc';

        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record['id'];
            }
        }
        return $return;
    }

    public static function getProducts($store_id, $dbobj, $page_num = 1,  $category='',  $query='', $ignore_quantity=false) {

        if($page_num === 0) {
            $limit = '';
        } else {
            $start = PRODUCT_NUM_PER_PAGE * ( $page_num - 1);
            $start = $start>0? $start : 0;
            $limit = 'limit '.$start.', '.PRODUCT_NUM_PER_PAGE;
        }

        $filter_sql = '';
        $search_filter = '';
        $quantity_filter = '';
        $left = 'left';

        if(!empty($category)) {
            $filter_sql = "and category='$category'";
            $left = '';
        }

        if(!empty($query)) {
            $search_filter = " and (p.name like '%$query%' or p.description like '%$query%')";
        }

        if(!$ignore_quantity) {
            $quantity_filter = " and p.quantity>0";
        }

        $sql = "select $store_id as store_id, c.category, p.*, group_concat(distinct pc.url) as url from products p
                join products_pictures pp on p.id = pp.product_id
                join pictures pc on pc.id = pp.picture_id
                $left join products_categories pct on pct.product_id = p.id and pct.category_id != -1
                $left join categories c on pct.category_id = c.id $filter_sql
                where p.status = 0 and p.price !=0 $quantity_filter $search_filter
                group by p.id
                order by p.updated desc, p.id desc $limit";

        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $urls = explode(',', $record['url']);

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
                if(empty($record['board_img'])) {
                    $record['board_img'] = $urls[0];
                }
                //$record['board_img'] = get_valid_pinterest_image_url($record['board_img']);
                //$record['mobile_img'] = get_valid_pinterest_image_url($record['mobile_img']);
                $record['price'] = round($record['price'], 2);
                $record['shipping'] = round($record['shipping'], 2);
                $record['description']= strip_tags($record['description']);
                $return[] = $record;

            }
        }
        return $return;
    }

    public static function getAllProducts($store_id, $dbobj, $page_num = 1,  $category='' ) {

        if($page_num === 0) {
            $limit = '';
        } else {
            $start = PRODUCT_NUM_PER_PAGE * ( $page_num - 1);
            $limit = 'limit '.$start.', '.PRODUCT_NUM_PER_PAGE;
        }


        $filter_sql = '';
        $left = 'left';
        if(!empty($category)) {
            $filter_sql = "and category='$category'";
            $left = '';
        }

        $sql = "select $store_id as store_id, c.category, p.*, group_concat(pc.url) as url from products p
                join products_pictures pp on p.id = pp.product_id
                join pictures pc on pc.id = pp.picture_id
                $left join products_categories pct on pct.product_id = p.id
                $left join categories c on pct.category_id = c.id $filter_sql
                where p.name!='' and p.price!='' and p.quantity != ''
                group by p.id
                order by p.id desc $limit";

        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $urls = explode(',', $record['url']);

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
                $record['price'] = round($record['price'], 2);
                $record['shipping'] = round($record['shipping'], 2);

                $return[] = $record;

            }
        }
        return $return;
    }
    //status=0
    public static function getProduct($product_id, $dbobj ,$status=CREATED) {

        $filter='';
        if($status!==IGNORE_STATUS){
            $filter=' and p.status ='.$status;
        }
        $sql = "select
                if(c.category is null, '', c.category) as category,
                if(c.description is null, '', c.description) as category_description,
                p.*,
                group_concat(pc.url) as pic_urls,
                group_concat(distinct pc.id) as pic_ids,
                group_concat(pc.type) as pic_types,
                group_concat(distinct pc.source) as pic_sources
                from products p
                join products_pictures pp on p.id = pp.product_id
                join pictures pc on pc.id = pp.picture_id
                left join products_categories pct on pct.product_id=p.id
                left join categories c on pct.category_id = c.id
                where p.price !=0 and p.id=$product_id".$filter;

        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {

                $pic_urls = explode(',', $record['pic_urls']);

                foreach($pic_urls as $u){
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

                $record['price'] = round($record['price'], 2);
                $record['shipping'] = round($record['shipping'], 2);
                $return = $record;

            }
        }
        return $return;
    }

    public static function getSearchProduct($product_id, $dbobj ,$status=CREATED) {

        $filter='';
        if($status!==IGNORE_STATUS){
            $filter=' and p.status ='.$status;
        }
        $sql = "select
                if(c.category is null, '', c.category) as category,
                if(c.description is null, '', c.description) as category_description,
                p.*,
                group_concat(pc.url) as pic_urls,
                group_concat(distinct pc.id) as pic_ids,
                group_concat(pc.type) as pic_types,
                group_concat(distinct pc.source) as pic_sources
                from products p
                join products_pictures pp on p.id = pp.product_id
                join pictures pc on pc.id = pp.picture_id
                left join products_categories pct on pct.product_id=p.id
                left join categories c on pct.category_id = c.id
                where p.id=$product_id".$filter;

        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {

                $pic_urls = explode(',', $record['pic_urls']);

                foreach($pic_urls as $u){
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

                $record['price'] = round($record['price'], 2);
                $record['shipping'] = round($record['shipping'], 2);
                $return = $record;

            }
        }
        return $return;
    }

    public static function getProductsCount($dbobj) {
        $sql = "select count(id) as cnt from products";
        $return = 0;
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['cnt'];
            }
        }
        return $return;
    }

    public static function getSelectedBoardsIds($store_id, $dbobj) {
     	$sql = "select spb.pinterest_board_id as id from
     		stores s join stores_pinterest_boards spb on (s.id=spb.store_id)
     		where s.id=$store_id";
     	$return = array();

     	if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record['id'];
            }
     	}
     	return $return;
     }

     public static function getSelectedBoardsPinsIds($store_id, $dbobj, $page_num = 1 ) {

     	$start = ( $page_num - 1 ) * PIN_NUM_PER_PAGE;
     	$end = $start + PIN_NUM_PER_PAGE;
     	$sql = "select pb.id as board_id, pp.id as pin_id
     	from
        stores s join stores_pinterest_boards spb on (s.id=spb.store_id)
        join pinterest_boards pb on (spb.pinterest_board_id=pb.id)
        join pinterest_boards_pinterest_pins pbpp on (pb.id=pbpp.pinterest_board_id)
        join pinterest_pins pp on (pbpp.pinterest_pin_id=pp.id)
     	where s.id = $store_id group by pp.id order by pp.id asc limit $start,$end";

        $return = array();

     	if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
     	}
     	return $return;
     }

     public static function getValidBoards($store_id, $pinterest_account_id, $dbobj) {
        $max_pins = 3000;

        $sql = "select pb.*, if(spb.store_id is null, 0, spb.store_id) as store_id  from pinterest_accounts pa join pinterest_accounts_pinterest_boards papb
            on (pa.id=papb.pinterest_account_id) join pinterest_boards pb on (papb.pinterest_board_id=pb.id)
            left join stores_pinterest_boards spb on (spb.pinterest_board_id=pb.id and spb.store_id=$store_id)
            where pa.id=$pinterest_account_id and pb.status=0 group by pb.id order by pb.pins";
        $return = array();
        $pins_count = 0;
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $pins_count = $pins_count + $record['pins'];
                if($pins_count>$max_pins) {
                    break;
                } else if(empty($record['thumbnails'])) {
                    continue;
                } else {
                    $return[] = $record;
                }
            }
        }

        return $return;
     }

     public static function getSelectedBoardsPinsCount($store_id, $dbobj){
     	$sql = "select count(pp.id) as cnt
     	from
     	stores s join stores_pinterest_boards spb on (s.id=spb.store_id)
     	join pinterest_boards pb on (spb.pinterest_board_id=pb.id)
     	join pinterest_boards_pinterest_pins pbpp on (pb.id=pbpp.pinterest_board_id)
     	join pinterest_pins pp on (pbpp.pinterest_pin_id=pp.id)
     	where s.id = $store_id";

     	$cnt = 0;

     	if($res = $dbobj->query($sql)) {
     		$record = $dbobj->fetch_row($res);
     		$cnt = $record[0];
     	}
     	return $cnt;
     }

     public static function getSelectedBoardsNonSelectedPins($store_id, $dbobj, $page_num = 1 ) {

     	$start = ( $page_num - 1 ) * PIN_NUM_PER_PAGE;
     	$end = $start + PIN_NUM_PER_PAGE;
     	$sql = "select pb.id as board_id, pp.*
     	from
        stores s join stores_pinterest_boards spb on (s.id=spb.store_id)
        join pinterest_boards pb on (spb.pinterest_board_id=pb.id)
        join pinterest_boards_pinterest_pins pbpp on (pb.id=pbpp.pinterest_board_id)
        join pinterest_pins pp on (pbpp.pinterest_pin_id=pp.id)
        left join stores_pinterest_pins spp on (spp.pinterest_pin_id=pp.id and spp.store_id=$store_id)
     	where s.id = $store_id and spp.store_id is null group by pp.id order by pp.id asc limit $start,".PIN_NUM_PER_PAGE;

     	$return = array();

     	if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $record['description'] = strip_tags($record['description']);
                $record['price'] = round($record['price'], 2);
                $return[] = $record;
            }
     	}
     	return $return;
     }

     public static function getSelectedBoardsNonSelectedPinsCount($store_id, $dbobj, $page_num = 1 ) {

     	$start = ( $page_num - 1 ) * PIN_NUM_PER_PAGE;
     	$end = $start + PIN_NUM_PER_PAGE;
     	$sql = "select count(pp.id) as cnt
     	from
        stores s join stores_pinterest_boards spb on (s.id=spb.store_id)
        join pinterest_boards pb on (spb.pinterest_board_id=pb.id)
        join pinterest_boards_pinterest_pins pbpp on (pb.id=pbpp.pinterest_board_id)
        join pinterest_pins pp on (pbpp.pinterest_pin_id=pp.id)
        left join stores_pinterest_pins spp on (spp.pinterest_pin_id=pp.id and spp.store_id=$store_id)
     	where s.id = $store_id and spp.store_id is null";

        $return = 0;

     	if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['cnt'];
            }
     	}
     	return $return;
     }

     public static function getPaypalAccount($store_id, $dbobj) {

         $sql = "select pa.*
             from
             stores s join merchants_stores ms on (s.id=ms.store_id) join
             merchants_paypal_accounts mpa on (mpa.merchant_id=ms.merchant_id) join
             paypal_accounts pa on (pa.id=mpa.paypal_account_id)
             where s.id=$store_id group by pa.id";

        $return = array();

     	if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
     	}
     	return $return;

     }

     public static function getAllStoreIds($dbobj) {
        $sql = "select id from stores order by id";

        $return = array();

     	if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record['id'];
            }
     	}
     	return $return;
     }

     public static function getAllStores($dbobj, $page_num = 0, $filters = array()) {

         if($page_num === 0) {
             $limit = '';
         } else {
             $start = STORE_NUM_PER_PAGE * ( $page_num - 1);
             $limit = 'limit '.$start.', '.STORE_NUM_PER_PAGE;
         }

         $merchant_email_filter = empty($filters['merchant_email']) ? '' : " m.username like '%{$filters['merchant_email']}%' ";
         $store_subdomain_filter = empty($filters['store_subdomain']) ? '' : " s.subdomain like '%{$filters['store_subdomain']}%' ";
         $filter = '';

         if(!empty($merchant_email_filter) || !empty($store_subdomain_filter)) {

             $filter.=' where ';
             if(!empty($merchant_email_filter)) {
                 $filter.= $merchant_email_filter;
                 if(!empty($store_subdomain_filter)) {
                     $filter.= ' and '.$store_subdomain_filter;
                 }
             } else {
                 $filter.= $store_subdomain_filter;
             }
         }

         $sql = "select s.*, u.id as user_id, m.username, ms.merchant_id, pa.username as pinterest_username, DATE_FORMAT(s.created,'%Y-%m-%d') as created_time,
                    DATE_FORMAT(s.updated,'%Y-%m-%d') as store_updated_time,
                    DATE_FORMAT(m.created,'%Y-%m-%d') as merchant_created_time
                    from stores s
                    join merchants_stores ms on s.id = ms.store_id
                    join merchants m on ms.merchant_id = m.id
                    join users u on u.merchant_id=m.id
                    left join merchants_pinterest_accounts mpa on mpa.merchant_id = ms.merchant_id
                    left join pinterest_accounts pa on pa.id = mpa.pinterest_account_id
                    $filter
                    order by s.created desc $limit";
         $return = array();
         if($res = $dbobj->query($sql)) {
             while($record = $dbobj->fetch_assoc($res)) {
                 if(empty($record['currency'])){
                     $record['currency'] = 'USD';
                 }
                 $return[] = $record;
             }
         }
         return $return;
     }

     public static function getStoreSettings($store_id, $dbobj) {
         $sql = "select s.*,
             if(group_concat(t.id, '-', t.tag) is null, '', group_concat(t.id, '-', t.tag)) as ids_tags
             from
             stores s left join stores_tags st on (s.id=st.store_id) left join
             tags t on (st.tag_id=t.id)
             where
             s.id=$store_id";

         $return = array();

     	if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
     	}
     	return $return;
     }

     public static function getStoreTags($store_id, $dbobj) {
         $sql = "select group_concat(t.tag) as tags
             from stores s join stores_tags st on (s.id=st.store_id)
             join tags t on (t.id=st.tag_id)
             where s.id=".$store_id.' group by s.id';

         $return = '';
         if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['tags'];
            }
     	 }
     	 return $return;
     }

     public static function isSubdomainExist($store_id, $subdomain, $dbobj) {
         $sql = "select id from stores where subdomain='$subdomain' and id!=$store_id";
         $return = 0;
         if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['id'];
            }
     	}
     	if($return === 0) {
            return false;
        } else {
            return true;
        }
     }

     public static function getStoreByNameOrTag($dbobj, $content){

         $site_domain = getSiteDomain();
         $sql = "select s.name, CONCAT(s.subdomain,'.$site_domain') as url, t.tag from stores s
                    left join stores_tags st on st.store_id = s.id
                    left join tags t on st.tag_id = t.id
                    where s.status = 2 and ( s.name like '%".$content."%' or t.tag like '%".$content."%' ) order by s.id limit 8;
                         ";

         $return = array();

         if($res = $dbobj->query($sql)) {
             while($record = $dbobj->fetch_assoc($res)) {
                 $return[] = $record;
             }
         }
         return $return;
     }

     public static function deleteSelectedBoards($store_id, $dbobj) {
         $sql = "delete from stores_pinterest_boards where store_id=$store_id";
         $dbobj->query($sql);

     }

     public static function getStoreRating($dbobj){
         $sql = " select CEIL(sum(score)/count(id)) as rating from reviews ";
         $return = 0;
         if($res = $dbobj->query($sql)) {
             $record = $dbobj->fetch_assoc($res);
             $return = $record['rating'];
         }
         return $return;
     }

     public static function getSelectedBoardIds($store_id, $dbobj) {
         $sql = "select pinterest_board_id from stores_pinterest_boards where store_id=$store_id";
         $return = array();

     	 if($res = $dbobj->query($sql)) {
             while($record = $dbobj->fetch_assoc($res)) {
                 $return[] = $record['pinterest_board_id'];
             }
     	 }
     	 return $return;
     }

     public static function getOrdersCount($dbobj, $statuses = array('Pending', 'Completed')) {
        $status_filter = '';
        if (!empty($statuses)) {
            $status_filter = 'where';
            foreach ($statuses as $i => $status) {
                if ($i === 0) {
                    $status_filter = $status_filter . " payment_status='$status'";
                } else {
                    $status_filter = $status_filter . " or payment_status='$status'";
                }
            }
        }
        $sql = "select count(*) as cnt from orders $status_filter";
        $cnt = 0;

        if ($res = $dbobj->query($sql)) {
            $record = $dbobj->fetch_assoc($res);
            $cnt = $record['cnt'];
        }
        return $cnt;
    }

     public static function getCategories($dbobj) {
         $sql = "select id, category, if(description='', category, description) as description from categories";
         $return = array();

     	 if($res = $dbobj->query($sql)) {
             while($record = $dbobj->fetch_assoc($res)) {
                 $return[] = $record;
             }
     	 }
     	 return $return;
     }

     public static function getNonEmptyCategories($dbobj) {
         $deleted = DELETED;
         $sql = "select c.id, c.category, if(c.description='', c.category, c.description) as description,
             count(p.id) as product_cnt
             from
             categories c join products_categories pc on (c.id=pc.category_id)
             join products p on (p.id=pc.product_id)
             where
             p.name!='' and p.price>0 and p.quantity>0 and p.status != $deleted and p.global_category_id !=0
             group by c.id";
         $return = array();

     	 if($res = $dbobj->query($sql)) {
             while($record = $dbobj->fetch_assoc($res)) {
                 $return[] = $record;
             }
     	 }
     	 return $return;
     }

     public static function deleteCategory($category, $dbobj) {
         $sql = "delete c.*, pc.* from categories c left join products_categories pc on (c.id=pc.category_id)
             where c.category='$category'";
         $dbobj->query($sql);
     }

     public static function getMerchantInfo($store_id, $dbobj) {

         $sql = "select if(ppa.username is null, '', ppa.username) as merchant_paypal_username , u.merchant_id, m.username as merchant_username
            from stores s
            join merchants_stores ms on (s.id = ms.store_id)
            join merchants m on (ms.merchant_id = m.id)
            join users u on (ms.merchant_id = u.merchant_id)
            left join users_payment_accounts upa on (u.id = upa.user_id)
            left join payment_accounts pma on (upa.payment_account_id = pma.id)
            left join paypal_accounts ppa on (pma.paypal_account_id = ppa.id)
            where s.id = $store_id";

        $return = array();

     	if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
     	}
     	return $return;

     }

     public static function getDailyStoresCount($dbobj) {
         $sql = "select count(*) as cnt from stores where date(created)=date(now())";
         $return = 0;
         if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['cnt'];
            }
     	}
     	return $return;
     }


    // fields:
    // email
    // password
    // pinterest_username
    // addr1
    // addr2
    // city
    // state
    // country
    // zip
    // store_name
    // subdomain
    // tables:
    // merchants, stores, addresses, pinterest_accounts
    public static function getProfile($store_id, $dbobj) {
        $sql = "select m.username, m.id as merchant_id, m.password,
                if(pa.id is null, 0, pa.id) as pinterest_account_id,
                if(pa.username is null, '', pa.username) as pinterest_username,
                s.id as store_id, s.subdomain as store_subdomain, s.name as store_name,
                s.featured as store_featured, s.host as store_host,
                s.logo as store_logo, s.status as store_status,
                round(s.tax, 2) as store_tax, round(s.shipping, 2) as store_shipping,
                round(s.additional_shipping, 2) as store_additional_shipping,
                s.optin_salesnetwork as store_optin_salesnetwork,
                if(a.id is null, 0, a.id) as address_id,
                if(a.addr1 is null, '', a.addr1) as addr1,
                if(a.addr2 is null, '', a.addr2) as addr2,
                if(a.city is null, '', a.city) as city,
                if(a.state is null, '', a.state) as state,
                if(a.country is null, '', a.country) as country,
                if(a.zip is null, '', a.zip) as zip,
                if(group_concat(t.id, '-', t.tag) is null, '', group_concat(t.id, '-', t.tag)) as ids_tags
            from
            merchants m join merchants_stores ms on (m.id=ms.merchant_id)
            join stores s on (ms.store_id=s.id)
            left join merchants_pinterest_accounts mpa on (m.id=mpa.merchant_id)
            left join pinterest_accounts pa on (pa.id=mpa.pinterest_account_id)
            left join merchants_addresses ma on (ma.merchant_id=m.id)
            left join addresses a on (a.id=ma.address_id)
            left join stores_tags st on (s.id=st.store_id) left join
            tags t on (st.tag_id=t.id)
            where
            s.id=$store_id group by s.id";

        $return = array();

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
        }
        return $return;

    }

    public static function getAllStoreInfo($dbobj, $min_store_id=0, $max_store_id=PHP_INT_MAX, $status=IGNORE_STATUS){

        if($status === IGNORE_STATUS) {
            $status_filter = '';
        } else {
            $status_filter = " and status=$status";
        }

        $where = 'where id>='.$min_store_id.' and id<='.$max_store_id.$status_filter;
        $sql = "select * from stores $where order by id";

        $return = array();

     	if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                if(empty($record['currency'])){
                    $record['currency'] = 'USD';
                }
                $return[]=$record;
            }
     	}
     	return $return;
    }

    public static function getMissingStoreInfo($dbobj){
        $sql = "select distinct store_id from missing_converted_photos where status=6";

        $return = array();

     	if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[]=$record['store_id'];
            }
     	}
     	return $return;
    }

    public static function getAllStoreIdsAndStatus($dbobj) {
        $sql = "select id ,status from stores";

        $return = array();

     	if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[]=$record;
            }
     	}
     	return $return;
     }

    public static function getCurrentScheduledJobsCount($dbobj, $type) {
        $sql = "select count(*) as cnt from scheduled_jobs where type=$type and date(created)=date(now())";

        $cnt = 0;

        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }
        return $cnt;
    }

    public static function getContacts($dbobj){
        $sql = "select c.first_name, c.last_name, ce.email, c.id from customers_cust_emails cce
        join cust_emails ce on cce.cust_email_id = ce.id
        join customers c on c.id = cce.customer_id
        where c.status != ".DELETED."
        group by ce.id ";
        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[]=$record;
            }
        }
        return $return;

    }

    public static function searchProducts($dbobj, $query){
        $sql = "select c.category, p.*, group_concat(pc.url) as url from products p
                join products_pictures pp on p.id = pp.product_id
                join pictures pc on pc.id = pp.picture_id
                left join products_categories pct on pct.product_id = p.id and pct.category_id != -1
                left join categories c on pct.category_id = c.id
                where (p.name like '%$query%' or p.description like '%$query%') and p.status = 0 and p.price !=0
                group by p.id
                order by p.id desc";
        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {

                $pic_urls = explode(',', $record['url']);

                foreach($pic_urls as $u){
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

                $record['price'] = round($record['price'], 2);
                $record['shipping'] = round($record['shipping'], 2);
                $return[]=$record;
            }
        }
        return $return;
    }

    public static function closeStore($dbobj, $query){
        $sql="update merchants m join merchants_stores ms on (m.id=ms.merchant_id)
              join stores s on (ms.store_id=s.id)
              set m.status=".BLOCKED.",s.status=".PENDING."
              where s.subdomain='".$query."' or m.username='".$query."'";
        $dbobj->query($sql);
    }

    public static function getFuturePayments($store_id, $dbobj) {
        $sql = "select
            op.order_id, op.id, op.created,
            pi.status, pi.sender, pi.receiver, pi.amt, pi.contract, pi.currency_code
            from
            order_payments op join payment_items pi on (op.payment_item_id=pi.id)
            where op.store_id=$store_id and pi.status!=".PROCESSED.' order by op.created desc';
        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                if($record['status'] == CREATED) {
                    $record['status'] = 'PENDING';
                } else {
                    $record['status'] = 'PROCESSING';
                }
                $record['contract'] = json_decode($record['contract'], true);
                $record['currency_symbol'] = currency_symbol($record['currency_code']);
                $return[]=$record;
            }
        }
        return $return;
    }

    public static function getPayments($store_id, $dbobj) {
        $sql = "select
            op.order_id,
            p.id, p.created, p.status, p.sender, p.receiver, p.amt, p.contract, p.updated, p.currency_code,
            group_concat(op.order_id) as ref_ids
            from
            payments p join payment_items pi on (p.id=pi.payment_id)
            join order_payments op on (op.payment_item_id=pi.id)
            where op.store_id=$store_id and p.status=".PROCESSED.' group by p.id';
        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $record['ref_ids'] = explode(',', $record['ref_ids']);
                $record['status'] = 'PAID';
                $record['contract'] = json_decode($record['contract'], true);
                $record['currency_symbol'] = currency_symbol($record['currency_code']);
                $return[]=$record;
            }
        }
        return $return;
    }

     public static function getAllStoreCnt($dbobj, $merchant_email = '', $store_subdomain = '') {

         $merchant_email_filter = empty($merchant_email) ? '' : " m.username like '%$merchant_email%' ";
         $store_subdomain_filter = empty($store_subdomain) ? '' : " s.subdomain like '%$store_subdomain%' ";
         $filter = '';

         if(!empty($merchant_email_filter) || !empty($store_subdomain_filter)) {

             $filter.=' where ';
             if(!empty($merchant_email_filter)) {
                 $filter.= $merchant_email_filter;
                 if(!empty($store_subdomain_filter)) {
                     $filter.= ' and '.$store_subdomain_filter;
                 }
             } else {
                 $filter.= $store_subdomain_filter;
             }
         }

         $sql = "select count(1) as cnt
                    from stores s join merchants_stores ms on (s.id = ms.store_id)
                    join merchants m on (m.id = ms.merchant_id)
                    $filter";

         $cnt = 0;

         if($res = $dbobj->query($sql)) {
             if($record = $dbobj->fetch_assoc($res)) {
                 $cnt = $record['cnt'];
             }
         }

         return $cnt;
     }

    public static function getUserId($store_id, $dbobj) {
        $sql = "select u.id as user_id
            from merchants_stores ms
            join users u on (ms.merchant_id = u.merchant_id)
            where ms.store_id=$store_id";

        $return = 0;

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record['user_id'];
            }
        }
        return $return;
    }

    public static function getStoreHosts($dbobj) {
        $sql = "select distinct(host) as host from stores";
        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record['host'];
            }
        }

        return $return;
    }

    public static function getUnvalidProducts($dbobj, $page_num = 1) {

        if($page_num === 0) {
            $limit = '';
        } else {
            $start = PRODUCT_NUM_PER_PAGE * ( $page_num - 1) > 0 ? PRODUCT_NUM_PER_PAGE * ( $page_num - 1) : 0;
            $limit = 'limit '.$start.', '.PRODUCT_NUM_PER_PAGE;
        }

        $sql = "select c.category, p.*, group_concat(distinct pc.url) as product_image from products p
                join products_pictures pp on p.id = pp.product_id
                join pictures pc on pc.id = pp.picture_id
                left join products_categories pct on pct.product_id = p.id and pct.category_id != -1
                left join categories c on pct.category_id = c.id
                where p.status = ".PENDING."
                group by p.id
                order by p.updated desc, p.id desc $limit";

        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }

        return $return;
    }

    public static function getUnvalidProductsCnt($dbobj) {

        $sql = "select count(1) as cnt from products where status=".PENDING."";
        $cnt = 0;

        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }

        return $cnt;
    }

    public static function rebuilding_incorret_data($dbobj) {

        $sql = "update stores
            set optin_salesnetwork = ".CREATED."
            where status!=".ACTIVATED." and optin_salesnetwork=".ACTIVATED;

        $dbobj->query($sql);
    }

    public static function get_version($dbobj) {
        $sql = "select version from version";
        $version = 0;

        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $version = $record['version'];
            }
        }

        return $version;
    }

    public static function check_table_exist($dbobj, $table) {
        $sql = "show tables like '$table'";

        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return true;
            }
        }

        return false;
    }

    public static function isActiveStore($dbobj, $store_id) {
        $sql = "select * from stores where id = $store_id and status=".ACTIVATED;

        $is_acvive = false;

        if($res = $dbobj->query($sql)){
            if($record = $dbobj->fetch_assoc($res)) {
                $is_acvive = true;
            }
        }
        return $is_acvive;
    }

    public static function getFeaturedProducts($dbobj) {

        $sql = "select * from products where status != ".DELETED." and name !=''
                and price != 0 and quantity > 0 and featured != 0";
        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }

        return $return;
    }

    public static function getCachedObject($params, $dbobj){
        global $dbconfig;
        $store_dbname = $dbconfig->store->name;
        $account_dbname = $dbconfig->account->name;
        $ck = $params['_cachekey'];

        $sql = "select
                s.*, u.id as uid, pa.username as pinterest_username,
                u.merchant_id as merchant_id
                from stores s
                join merchants_stores ms on (s.id = ms.store_id)
                join users u on (ms.merchant_id = u.merchant_id)
                left join merchants_pinterest_accounts mpa on (mpa.merchant_id = u.merchant_id)
                left join pinterest_accounts pa on (pa.id = mpa.pinterest_account_id)
                where " . $ck->conditionSQL('s') . "
                and s.status != " . DELETED . " and
                u.status != " . DELETED;
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $store_id = $record['id'];
                $record['user_id'] = $record['uid'];
                $record['active_product_cnt'] = DAL::getListCount(lck_store_active_products($store_dbname.'_'.$store_id));
                $record['inactive_product_cnt'] = ProductsMapper::getAllProductsCnt($store_id) - $record['active_product_cnt'];
                $record['product_sales'] = MyorderItemsMapper::getSalesCnt($dbobj, $store_id);
                $record['total_sale_amount'] = MyordersMapper::getSaleAmountForStore(
                    $dbobj, $store_id, NULL, COMPLETED
                );
                $record['total_sale_transactions'] = MyordersMapper::getOrdersCountForStore(
                    $dbobj, $store_id, NULL, COMPLETED
                );
                return $record;
            }
        }
        return array();
    }

    public static function getCachedObjectList($params, $dbobj) {
        $cachekey = $params['_cachekey'];
        $dbname = $cachekey->getDBName();
        $cond = $cachekey->conditionSQL('', TRUE, FALSE);

        //$entity = 
        $sql = "select id, updated from stores where " . $cond;

        if(isset($params['store_query'])){
            $sql = "select id, updated from stores where name like '%" . $dbobj->escape($params['store_query'])
                . "%' and status = " . ACTIVATED . $cachekey->orderSQL() . $cachekey->limitSQL();
        }

        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $entity_key = $dbname.'.store?id='.$record['id'];
                $score = strtotime2($record['updated']);
                $return[$entity_key] = $score;
            }
        }
        return $return;
    }

    public static function getCachedObjectListCount($params, $dbobj) {
        $cachekey = $params['_cachekey'];
        $status = isset($params['status']) ? $params['status'] : ACTIVATED;

        $sql = "select count(*) as cnt from stores where status = " . $status;
        if(isset($params['store_query'])){
            $sql = "select count(1) as cnt from stores where name like '%" . $dbobj->escape($params['store_query'])
                . "%' and status = " . $status;
        }
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['cnt'];
            }
        }
        return 0;
    }

    public static function forceDeleteStore($dbobj, $store_id){
        global $dbconfig;

        $dbname = getStoreDBName($store_id);

        // clear database
        $sqls = array();
        $sqls[] = "drop database " . $dbname;
        $sqls[] = "delete from stores where id = " . $store_id;
        $sqls[] = "delete ci from cart_items ci join carts c on(ci.cart_id = c.id)
                   where ci.store_id = " . $store_id . " and
                   c.status = " . ACTIVATED;
        $sqls[] = "delete oi from myorder_items oi join myorders o on(oi.order_id = o.id)
                   where oi.store_id = " . $store_id . " and
                   o.status = " . ACTIVATED . " and o.payment_status = " . ORDER_UNPAID;

        $sqls[] = "delete from mycoupons where store_id = " . $store_id;
        $sqls[] = "delete from auctions where store_id = " . $store_id;
        $sqls[] = "update users u
                   join merchants_stores ms on ms.merchant_id = u.merchant_id
                   set u.merchant_id = 0
                   where ms.store_id = $store_id";
        $sqls[] = "delete from merchants_stores where store_id = " . $store_id;
        foreach($sqls as $sql){
            $dbobj->query($sql);
        }

        // clear cache
        $store = BaseModel::findCachedOne($dbconfig->account->name . ".store?id=" . $store_id);
        $user = new User($dbobj, $store['uid']);
        $ck = CacheKey::q($dbconfig->account->name . ".user?id=" . $user->getId());
        DAL::delete($ck);
        $ck = CacheKey::q($dbconfig->account->name . ".user?username=" . $user->getUsername());
        DAL::delete($ck);
        $store = new Store($dbobj, $store_id);
        $subdomain = $store->getSubdomain();
        $store->setStatus(DELETED);
        $store->save();
        DAL::delete($dbconfig->account->name . ".store?id=" . $store_id);
        DAL::delete($dbconfig->account->name . ".store?subdomain=" . $subdomain);

        // clear images
        $prefix = cloudinary_store_product_ns($store_id, 0); //"$env/s-s/s-$store_id/p/p-0/"
        $prefix = substr($prefix, 0, -6);
        try{
            $api = new \Cloudinary\Api();
            $api->delete_resources_by_prefix($prefix);
        }catch(Exception $e){
        }
    }
}
