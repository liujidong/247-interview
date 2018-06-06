<?php

class MerchantsMapper {

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

        if ($res = $dbobj->query($sql, $dbobj)) {
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
    
    public static function getPaypalAccount($merchant_id, $dbobj) {
        $sql = "select p.id as paypal_account_id, p.username as paypal_username
            from 
            merchants_paypal_accounts mpa join paypal_accounts p on (mpa.paypal_account_id=p.id)
            where
            mpa.merchant_id=$merchant_id";
        
        $return = array('paypal_username'=>'', 'paypal_account_id'=>0);

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return['paypal_username'] = $record['paypal_username'];
                $return['paypal_account_id'] = $record['paypal_account_id'];
            }
        }
        return $return;
        
    }

    public static function getStoreInfo($merchant_id, $dbobj) {
        $sql = "select 
                if(pa.id is null, 0, pa.id) as pinterest_account_id,
                if(pa.username is null, '', pa.username) as pinterest_username, 
                s.id as store_id, s.subdomain as store_subdomain, s.name as store_name, 
                s.featured as store_featured, s.host as store_host,
                s.logo as store_logo, s.status as store_status,
                round(s.tax, 2) as store_tax, round(s.shipping, 2) as store_shipping,
                round(s.additional_shipping, 2) as store_additional_shipping,
                s.external_website as store_external_website, s.description as store_description, 
                s.return_policy as store_return_policy, s.optin_salesnetwork as store_optin_salesnetwork,
                s.payment_solution as store_payment_solution, s.transaction_fee_waived as store_transaction_fee_waived,
                if(group_concat(t.id, '-', t.tag) is null, '', group_concat(t.id, '-', t.tag)) as ids_tags,
                s.created as store_created, s.updated as store_updated
            from 
            merchants m join merchants_stores ms on (m.id=ms.merchant_id)
            join stores s on (ms.store_id=s.id) 
            left join merchants_pinterest_accounts mpa on (m.id=mpa.merchant_id)
            left join pinterest_accounts pa on (pa.id=mpa.pinterest_account_id)
            left join stores_tags st on (s.id=st.store_id) 
            left join tags t on (st.tag_id=t.id)
            where 
            m.id=$merchant_id group by m.id";
        
        $return = array();

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
        }
        return $return;
        
    }
    
    public static function getAddress($merchant_id,$dbobj){
        $sql = "select m.id, a.addr1, a.addr2, a.city, a.state, a.zip, a.country from merchants m 
                left join merchants_addresses ma on m.id = ma.merchant_id 
                left join addresses a on a.id = ma.address_id
                where m.id = $merchant_id ";
        
        $return = array();
        
        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
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
    public static function getProfile($merchant_id, $dbobj) {
        $sql = "select m.username,m.first_name, m.last_name, m.phone_number, m.id as merchant_id, m.password,
                m.status as merchant_status,
                if(pa.id is null, 0, pa.id) as pinterest_account_id,
                if(pa.username is null, '', pa.username) as pinterest_username, 
                s.id as store_id, s.subdomain as store_subdomain, s.name as store_name, 
                s.featured as store_featured, s.host as store_host,
                s.logo as store_logo, s.status as store_status,
                round(s.tax, 2) as store_tax, round(s.shipping, 2) as store_shipping,
                round(s.additional_shipping, 2) as store_additional_shipping,
                s.external_website, s.description, s.return_policy, s.optin_salesnetwork, 
                if(a.id is null, 0, a.id) as address_id,
                if(a.addr1 is null, '', a.addr1) as addr1, 
                if(a.addr2 is null, '', a.addr2) as addr2, 
                if(a.city is null, '', a.city) as city, 
                if(a.state is null, '', a.state) as state, 
                if(a.country is null, '', upper(a.country)) as country, 
                if(a.zip is null, '', a.zip) as zip,
                if(group_concat(t.id, '-', t.tag) is null, '', group_concat(t.id, '-', t.tag)) as ids_tags,
                if(p.id is null, 0, p.id) as paypal_account_id
            from 
            merchants m join merchants_stores ms on (m.id=ms.merchant_id)
            join stores s on (ms.store_id=s.id) 
            left join merchants_pinterest_accounts mpa on (m.id=mpa.merchant_id)
            left join pinterest_accounts pa on (pa.id=mpa.pinterest_account_id)
            left join merchants_addresses ma on (ma.merchant_id=m.id)
            left join addresses a on (a.id=ma.address_id)
            left join stores_tags st on (s.id=st.store_id) left join
            tags t on (st.tag_id=t.id)
            left join merchants_paypal_accounts mpay on (mpay.merchant_id=m.id)
            left join paypal_accounts p on (mpay.paypal_account_id=p.id)
            where 
            m.id=$merchant_id group by m.id";
        
        $return = array();

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
        }
        return $return;
        
    }
    

    public static function getPinterestAccountId($merchant_id, $dbobj) {
        $sql = "select 
            if(mpa.pinterest_account_id is null, 0, mpa.pinterest_account_id) as pinterest_account_id
            from merchants_pinterest_accounts mpa left join pinterest_accounts pa on (mpa.pinterest_account_id = pa.id)
            where 
            mpa.merchant_id = $merchant_id";
        
        $return = 0;

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record['pinterest_account_id'];
            }
        }
        return $return;               
    }   
    
    public static function getMerchantStoreInfo($dbobj) {
        $sql = "select m.*, s.* from merchants m 
            join merchants_stores ms on (m.id = ms.merchant_id) 
            join stores s on (s.id = ms.store_id)
            where s.status=".ACTIVATED;
        
        $return = array();
        
        if ($res = $dbobj->query($sql, $dbobj)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;            
    }

}

