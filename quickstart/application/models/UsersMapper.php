<?php

class UsersMapper {

    public static function getMerchants($dbobj) {

        $sql = "select * from merchants";

        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
    
    
    public static function getStoreId($merchant_id, $dbobj) {
        $sql = "select ms.store_id from merchants m join merchants_stores ms on (m.id=ms.merchant_id)
		where m.id=$merchant_id";
        $return = 0;
        if ($res = $dbobj->query($sql)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record['store_id'];
            }
        }
        return $return;
    }

    public static function getPinterestAccount($merchant_id, $dbobj) {
        $sql = "select pa.* from merchants m join merchants_pinterest_accounts mpa on (m.id=mpa.merchant_id)
                join pinterest_accounts pa on (mpa.pinterest_account_id=pa.id)
                where m.id=$merchant_id";
        $return = array();
        if ($res = $dbobj->query($sql)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            } else {
                $return = array();
            }
        }
        return $return;
    }

    public static function getSelectedBoards($merchant_id, $dbobj) {

        $sql = "select pb.* from merchants m join merchants_stores ms on (m.id=ms.merchant_id)
     		join stores s on (ms.store_id=s.id)
     		join stores_pinterest_boards spb on (spb.store_id = s.id)
     		join pinterest_boards pb on (pb.id = spb.pinterest_board_id)
     		where m.id=$merchant_id";

        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getUnSelectedBoards($merchant_id, $dbobj) {
        $sql = "select pb.* from merchants m join merchants_pinterest_accounts mpa on (m.id=mpa.merchant_id)
             join pinterest_accounts pa on (mpa.pinterest_account_id=pa.id)
             join pinterest_accounts_pinterest_boards papb on (papb.pinterest_account_id=pa.id)
             join pinterest_boards pb on (papb.pinterest_board_id=pb.id)
             where m.id=$merchant_id and pb.id not in
             (select pb.id from merchants m join merchants_stores ms on (m.id=ms.merchant_id)
             join stores s on (ms.store_id=s.id)
     		 join stores_pinterest_boards spb on (spb.store_id = s.id)
     		 join pinterest_boards pb on (pb.id = spb.pinterest_board_id)
     		 where m.id=$merchant_id)";

        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getSelectedBoardsPinsInfo($merchant_id, $dbobj) {
        $sql = "select pb.id as board_id, pp.id as pin_id, pp.external_id as external_pin_id,
     	pp.domain as pin_domin, pp.description as pin_description, pp.images_mobile as pin_images_mobile,
     	pp.images_closeup as pin_images_closeup,pp.images_thumbnail as pin_images_thumbnail,
     	pp.images_board as pin_images_board, pp.created_at as pin_created_at, pp.is_repin as pin_is_repin,
     	pp.is_video as pin_is_video, pp.source as pin_source, pp.created as pin_created,
     	pp.comments as pin_comments, pp.likes as pin_like, pp.repins as pin_repins, pp.updated as pin_updated,
     	p.id as product_id, p.name as product_name, p.size as product_size, p.quantity as product_quantity,
     	p.created as product_created, p.updated as product_updated,pp.price as pin_price
     	from merchants m join merchants_stores ms on (m.id=ms.merchant_id)
		join stores s on (ms.store_id=s.id) join stores_pinterest_accounts spa on (s.id=spa.store_id) 
		join pinterest_accounts pa on (spa.pinterest_account_id=pa.id) 
		join pinterest_accounts_pinterest_boards papb on papb.pinterest_account_id = pa.id 
		join pinterest_boards pb join pinterest_boards_pinterest_pins pbpp
     	on (pb.id=pbpp.pinterest_board_id) join pinterest_pins pp
     	on (pbpp.pinterest_pin_id=pp.id)
     	left join pinterest_pins_pictures ppp on (pp.id=ppp.pinterest_pin_id)
     	left join products_pictures ppi on ppi.picture_id = ppp.picture_id
     	left join products p on (ppi.product_id=p.id)
     	where pb.selected=1 and m.id = $merchant_id group by pp.id ";
        $return = array();

        if ($res = $db->query($sql, $dbobj)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getPaymentAccountIds($merchant_id, $dbobj) {
        $sql = "select m.id as merchant_id, if(mpa.paypal_account_id is null, 0, mpa.paypal_account_id) as paypal_account_id
             from 
             merchants m left join merchants_paypal_accounts mpa on (m.id=mpa.merchant_id)
             where m.id=$merchant_id";
        $return = array();

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return['merchant_id'] = $record['merchant_id'];
                $return['paypal_account_id'] = $record['paypal_account_id'];
            }
        }
        return $return;
    }
    
    public static function getPaymentAccount($user_id, $dbobj) {
        $sql = "select 
            pa.id, pay.username as paypal_account_username, 
            pay.status as paypal_account_status, pay.created as paypal_account_created,
            pay.updated as paypal_account_updated
            from 
            users_payment_accounts upa join payment_accounts pa on (upa.payment_account_id=pa.id)
            left join paypal_accounts pay on (pay.id=pa.paypal_account_id)
            where
            upa.user_id=$user_id";
        
        $return = array('paypal_username'=>'', 'paypal_account_id'=>0);

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
        }
        return $return;
        
    }
    
    public static function getPaymentAccountId($user_id, $dbobj) {
        $sql = "select 
            if(upa.payment_account_id is null, 0, upa.payment_account_id) as payment_account_id
            from 
            users_payment_accounts upa left join payment_accounts pa on (upa.payment_account_id=pa.id)
            where
            upa.user_id=$user_id";
        
        $return = 0;

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record['payment_account_id'];
            }
        }
        return $return;
        
    }
    

    public static function getStoreInfo($user_id, $dbobj) {
        $sql = "select 
                if(pa.id is null, 0, pa.id) as pinterest_account_id,
                if(pa.username is null, '', pa.username) as pinterest_username, 
                s.id as store_id, s.subdomain as store_subdomain, s.name as store_name, 
                s.featured as store_featured, s.host as store_host,
                s.logo as store_logo, s.status as store_status,
                round(s.tax, 2) as store_tax, round(s.shipping, 2) as store_shipping,
                round(s.additional_shipping, 2) as store_additional_shipping,
                s.store_external_website, s.store_description, s.store_return_policy, s.store_optin_salesnetwork, 
                s.created as store_created, s.updated as store_updated
                if(group_concat(t.id, '-', t.tag) is null, '', group_concat(t.id, '-', t.tag)) as ids_tags
            from
            users u join merchants m on (u.merchant_id=m.id)
            join merchants_stores ms on (m.id=ms.merchant_id)
            join stores s on (ms.store_id=s.id) 
            left join merchants_pinterest_accounts mpa on (m.id=mpa.merchant_id)
            left join pinterest_accounts pa on (pa.id=mpa.pinterest_account_id)
            left join stores_tags st on (s.id=st.store_id) 
            left join tags t on (st.tag_id=t.id)
            where 
            u.id=$user_id group by u.id";
        
        $return = array();

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
        }
        return $return;
        
    }
    
    public static function getAddress($user_id,$dbobj){
        $sql = "select a.addr1, a.addr2, a.city, a.state, a.zip, a.country from users u 
                left join users_addresses ua on u.id = ua.user_id 
                left join addresses a on a.id = ua.address_id
                where u.id = $user_id ";
        
        $return = array();
        
        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
        }
        return $return;
    }
    
    public static function getAddressId($user_id,$dbobj){
        $sql = "select if(ua.address_id is null, 0, ua.address_id) as address_id
                from users u 
                left join users_addresses ua on u.id = ua.user_id 
                where u.id = $user_id ";
        
        $return = 0;
        
        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record['address_id'];
            }
        }
        return $return;
    }
    
    public static function getUserIdByAssociateId($associate_id, $dbobj){
        $sql = "select id from users where associate_id = $associate_id";
        
        $user_id = 0;
        
        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $user_id = $record['id'];
            }
        }
        return $user_id;
    }    
    
    public static function getAllUsers($dbobj, $page_num=0, $search='') {
        
        $limit = ($page_num>=1) ? ' limit '.ACCOUNT_NUM_PER_PAGE*($page_num-1) . ','.ACCOUNT_NUM_PER_PAGE : '';
        $filter = empty($search) ? '' : " where username like '%$search%' ";
        
        $sql = "select * from users $filter order by id $limit";
        
        $return = array();
        
        if ($res = $dbobj->query($sql, $dbobj)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;        
    }
    
    public static function getUsersCount($dbobj, $search='') {
        
        $filter = empty($search) ? '' : " where username like '%$search%' ";
        $sql = "select count(id) as cnt from users $filter";
        
        $cnt = 0;
        
        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }
        return $cnt;        
    }    
    
    public static function isAccountActive($dbobj, $filters = null) {
        
        $filter_str = '';
        $filter_arr = array();    
                
        if(isset($filters['user_id'])) {
            $filter_arr[] = " id = {$filters['user_id']}";
        }  
        
        if(isset($filters['email'])) {
           $filter_arr[] = " username = '".$filters['email']."'";
        }             
        
        if(isset($filters['merchant_id'])) {
            $filter_arr[] = " merchant_id = {$filters['merchant_id']}";
        } 
        
        if(isset($filters['associate_id'])) {
            $filter_arr[] = " associate_id = {$filters['associate_id']}";
        }   
        
        $filter_arr[] = " status != ".BLOCKED;        

        $filter_str = " where ".implode(' AND ', $filter_arr);
        
        $sql = "select * 
            from users 
            $filter_str
            limit 0,1";
        $return = false;
        
        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = true;
            }
        }

        return $return;          
    }
    
    
    public static function updateEmail($dbobj, $current_email, $new_email) {
        $sql = "update 
                users u
                join merchants m
                on (u.merchant_id = m.id)
                set u.username = '$new_email', m.username = '$new_email'
                where u.username= '$current_email'";
        $dbobj->query($sql);        
    }
    
    public static function getCachedObject($params, $dbobj) {
        global $dbconfig;
        $store_dbname = $dbconfig->store->name;
        
        $user_id = isset($params['id'])?$params['id']:0;
        $username = isset($params['username'])?$params['username']:'';
        
        if(!empty($user_id)) {
            $where = "u.id=$user_id";
        } else if(!empty($username)) {
            $where = "u.username='".$dbobj->escape($username)."'";
        } else {
            return array();
        }
        
        $sql = "select u.*, ms.store_id, group_concat(cc.id) as credit_card_ids, pa.username as pinterest_username
            from users u
            left join merchants_stores ms on (u.merchant_id=ms.merchant_id)
            left join credit_cards cc on (u.id=cc.user_id)
            left join merchants_pinterest_accounts mpa on (mpa.merchant_id = u.merchant_id)
            left join pinterest_accounts pa on (pa.id = mpa.pinterest_account_id)
            where $where
            group by u.id order by cc.updated desc";

        $return = array();

        if ($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                if(empty($record['store_id'])){
                    $record['pinterest_username'] = '';
                }
                $return = $record;
            }
        }
        return $return;
        
    }
    
    public static function getCachedObjectList($params, $dbobj) {
        $ck = $params['_cachekey'];
        $cond_sql = $ck->conditionSQL();
        $sql = "select id, updated from users where " . $cond_sql;
        
        if(isset($params['subdomain'])) {
            $sql = "select u.id, u.updated
                    from users u join merchants_stores ms on (ms.merchant_id = u.merchant_id) join stores s on (ms.store_id = s.id)
                    where u.status!=". DELETED . " and s.subdomain='".$params['subdomain']."'
                    group by u.id";
        }

        $dbname = $ck->getDBName();
        $sort = $ck->getOrderInfo();
        $sort = $sort['orderby'];
        $label = $ck->getLabel();

        $keys = array();
        $users = array();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $users[] = $record;
            }
        }

        if(count($sort) != 1) { // no user, no score
            foreach($users as $user) {
                $key = $dbname.".user?id=".$user['id'];
                $keys[] = $key;
            }
        } else {
            $user_key = $sort[0];
            foreach($users as $user) {
                $score = 0;
                if($user_key == 'updated'){
                    $score = strtotime2($user['updated']);
                } else {
                    $score = isset($product[$user_key]) ? $product[$user_key] : 0;
                }
                $key = $dbname.".user?id=".$user['id'];
                $keys[$key] = $score;
            }
        }
        return $keys;
    }

    public static function getCachedObjectListCount($params, $dbobj) {

        $ck = $params['_cachekey'];
        $cnt = 0;
        $cond_sql = $ck->conditionSQL('', FALSE, FALSE);
        $sql = "select count(id) as cnt from users where $cond_sql";
        
        if(isset($params['username'])) {
            $sql = "select count(id) as cnt from users where status!=" .DELETED . " and username='".$params['username']."'";
        }
        if(isset($params['subdomain'])) {
            $sql = "select count(u.id) as cnt
                    from users u join merchants_stores ms on (ms.merchant_id = u.merchant_id) join stores s on (ms.store_id = s.id)
                    where u.status!=". DELETED . " and s.subdomain='".$params['subdomain']."'
                    group by u.id";
        }

        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }

        return $cnt;
    }
}

