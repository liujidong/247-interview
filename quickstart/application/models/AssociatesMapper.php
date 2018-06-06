<?php

class AssociatesMapper {

    public static function getProfile($associate_id, $dbobj) {
        $sql = "select ass.aid as aid,
                if(pa.id is null, 0, pa.id) as pinterest_account_id,
                if(pa.username is null, '', pa.username) as pinterest_username,
                from
                associates as ass
                left join associates_pinterest_accounts as apa on (ass.id=apa.associate_id)
                left join pinterest_accounts as pa on (apa.pinterest_account_id=pa.id)
                where ass.id=$associate_id group by ass.id";

        $return = array();

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record;
            }
        }
        return $return;
    }

    public static function getPaypalAccount($associate_id, $dbobj) {
        $sql = "select pa.username, pa.id
                from
                associates a join associates_paypal_accounts apa on (a.id = apa.associate_id)
                join paypal_accounts pa on (apa.paypal_account_id = pa.id)
                where
                a.id=$associate_id";

        $return = array('username'=>'', 'id'=>0);

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return['username'] = $record['username'];
                $return['id'] = $record['id'];
            }
        }
        return $return;
    }

    public static function getFuturePayments($associate_id, $dbobj) {
        $sql = "select
            spa.sale_id, spa.id, spa.created,
            pi.status, pi.amt, pi.contract, pi.currency_code,
            group_concat(sp.product_name) as product_names
            from
            sale_payments spa join payment_items pi on (spa.payment_item_id=pi.id)
            join sales s on (s.id=spa.sale_id)
            join search_products sp on (sp.store_id=s.store_id and sp.product_id=s.product_id)
            where s.associate_id=$associate_id and pi.status!=".PROCESSED."
            group by s.id
            order by spa.created desc";

        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                if($record['status'] == CREATED) {
                    $record['status'] = 'PENDING';
                } else {
                    $record['status'] = 'PROCESSING';
                }
                $record['product_names'] = explode(',', $record['product_names']);
                $record['contract'] = json_decode($record['contract'], true);
                $record['currency_symbol'] = currency_symbol($record['currency_code']);
                $return[]=$record;
            }
        }
        return $return;
    }

    public static function getPayments($associate_id, $dbobj) {
        $sql = "select
            spa.sale_id,
            p.id, p.status, p.created, p.amt, p.contract, p.currency_code,
            group_concat(s.id) as ref_ids
            from
            payments p join payment_items pi on (p.id=pi.payment_id)
            join sale_payments spa on (spa.payment_item_id=pi.id)
            join sales s on (s.id=spa.sale_id)
            where s.associate_id=$associate_id and p.status=".PROCESSED."
            group by p.id
            order by p.created desc";
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

    public static function getPinterestAccountId($associate_id, $dbobj) {
        $sql = "select
            if(apa.pinterest_account_id is null, 0, apa.pinterest_account_id) as pinterest_account_id
            from associates_pinterest_accounts apa left join pinterest_accounts pa on (apa.pinterest_account_id = pa.id)
            where
            apa.associate_id = $associate_id";

        $return = 0;

        if ($res = $dbobj->query($sql, $dbobj)) {
            if ($record = $dbobj->fetch_assoc($res)) {
                $return = $record['pinterest_account_id'];
            }
        }
        return $return;
    }

    public static function is_active_associate($associate_id, $dbobj) {
        $sql = "select u.* from users u
            join associates a on (u.associate_id = a.id)
            right join users_payment_accounts upa on (u.id=upa.user_id)
            right join users_addresses ua on (u.id=ua.user_id)
            join addresses addr on (addr.id = ua.address_id)
            where u.associate_id=$associate_id
            and u.first_name!='' and u.last_name!=''
            and a.external_website_name !=''
            and a.external_website_content != ''
            and a.marketing_channel != ''
            and addr.addr1 != ''
            and addr.country != ''
            and addr.city != ''
            and addr.state != ''
            and addr.zip != ''";

        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return true;
            }
        }
        return false;
    }

}
