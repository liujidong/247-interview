<?php

class AuctionsMapper {

    public static function getAuctions($dbobj, $status=array(CREATED, ACTIVATED, PENDING)) {
        $status = "(" . implode(' , ', $status) . ")";
        $sql = "select
                a.*,
                s.subdomain, s.host as store_host
                from auctions a
                join stores s on (a.store_id = s.id)
                where a.status in $status order by a.end_time, a.id";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $store_dbobj = DBObj::getStoreDBObj($record['store_host'], $record['store_id']);
                $sql = "select name from products where id = " . $record['product_id'];
                if($res2 = $store_dbobj->query($sql, $store_dbobj)) {
                    if($product_record = $store_dbobj->fetch_assoc($res2)) {
                        $record['product_name'] = $product_record['name'];
                    }
                }

                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getAuctionsForShopper($dbobj, $page=1, $num_per_page=10, $outdated=false) {

        $outdated_cond = $outdated ? "a.end_time <= now()" : "a.start_time <= now() and a.end_time > now()";
        $end_order = $outdated ? "desc" : "asc";
        $limit = "limit " . (($page-1)*$num_per_page) . "," . $num_per_page;
        $sql = "select
                      sp.product_name, sp.product_id, sp.product_price, sp.product_description,
                      s.subdomain as store_subdomain, s.name as store_name,
                      spcp.converted_192 as converted_pictures,
                      s.country, s.currency,
                      a.*
                from  auctions a
                join stores s on (s.id = a.store_id)
                join search_products sp on (a.store_id = sp.store_id and a.product_id = sp.product_id)
                join search_product_converted_pictures spcp on (spcp.search_product_id = sp.id)
                where (a.status = " . ACTIVATED . " or a.status = " . COMPLETED . ") and $outdated_cond
                group by a.id
                order by a.end_time $end_order, a.id
                $limit";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $store_id = $record['store_id'];
                $product_id = $record['product_id'];
                $subdomain = $record['store_subdomain'];
                $store_url = getStoreUrl($subdomain);
                $record['store_url'] = $store_url;
                $record['product_url'] = $store_url."/products/item?id=".$product_id;
                $record['current_bid_price'] = sprintf("%.2f", $record['current_bid_price']);
                $record['pictures'] = $record['converted_pictures'];
                $record['active'] = !$outdated;
                //$off = $record['product_price'] - $record['initial_bid_price'];
                $record['starts_at_percentage'] =  (int)($record['initial_bid_price'] * 100 / $record['product_price']);
                $record['currency_symbol'] = currency_symbol($record['currency']);
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getAuctionsCntForShopper($dbobj, $outdated=false) {

        $outdated_cond = $outdated ? "a.end_time <= now()" : "a.start_time <= now() and a.end_time > now()";

        $sql = "select count(1) as cnt
                from  auctions a
                where (a.status = " . ACTIVATED . " or a.status = " . COMPLETED . ") and $outdated_cond
               ";
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record['cnt'];
            }
        }
        return 0;
    }

    public static function getAuctionDetail($dbobj, $auction_id) {
        $sql = "select
                      sp.product_name, sp.product_id, sp.product_price, sp.product_description,
                      s.subdomain as store_subdomain, s.name as store_name,
                      group_concat(spcp.converted_550) as converted_pictures,
                      group_concat(spcp.converted_45) as thumb_pictures,
                      (a.start_time <= now() and a.end_time > now()) as in_bid,
                      s.currency,
                      a.*
                from  auctions a
                join stores s on (s.id = a.store_id)
                join search_products sp on (a.store_id = sp.store_id and a.product_id = sp.product_id)
                join search_product_converted_pictures spcp on (spcp.search_product_id = sp.id)
                where a.id = $auction_id
                group by a.id";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $store_id = $record['store_id'];
                $product_id = $record['product_id'];
                $subdomain = $record['store_subdomain'];
                $store_url = getStoreUrl($subdomain);
                $record['store_url'] = $store_url;
                $record['product_url'] = $store_url."/products/item?id=".$product_id;
                $record['pictures'] = explode(',', $record['converted_pictures']);
                $record['thumb_pictures'] = explode(',', $record['thumb_pictures']);
                $record['current_bid_price'] = sprintf("%.2f", $record['current_bid_price']);
                $record['currency_symbol'] = currency_symbol($record['currency']);
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getCurrentBidPrices($dbobj, $ids=null) {
        $ids_cond = '';
        if(!empty($ids)) {
            $ids_cond = "and a.id in ( $ids )";
        }
        $sql = "select a.id, a.current_bid_price, a.min_bid_increment, a.bid_times,
                s.currency
                from auctions a join
                stores s on (s.id = a.store_id)
                where a.status = " . ACTIVATED . " and a.start_time <= now() and a.end_time > now() $ids_cond";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $record['currency_symbol'] = currency_symbol($record['currency']);
                $record['current_bid_price'] = sprintf("%.2f", $record['current_bid_price']);
                $return[] = $record;
            }
        }
        return $return;
    }


    public static function getBids($dbobj, $auction_id) {
        $sql = "select
                      u.username,
                      sp.product_name, sp.product_id, sp.product_price, s.subdomain as store_subdomain,
                      a.initial_bid_price,
                      bid.*
                from user_auctions bid
                join auctions a on (bid.auction_id = a.id)
                join search_products sp on (a.store_id = sp.store_id and a.product_id = sp.product_id)
                join stores s on (sp.store_id = s.id)
                join users u on (u.id = bid.user_id)
                where bid.auction_id = $auction_id
                group by bid.id
                order by bid.id desc";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getNotificationInfoOnNewBid($dbobj, $auction_id) {
        $sql = "select
                      u.username, u.first_name,
                      m.username as merchant_name, m.first_name as merchant_first_name,
                      sp.product_name, sp.product_id, sp.product_price, s.subdomain as store_subdomain,
                      a.initial_bid_price, a.end_time,
                      s.currency,
                      bid.*
                from user_auctions bid
                join auctions a on (bid.auction_id = a.id)
                join stores s on (s.id = a.store_id)
                join search_products sp on (a.store_id = sp.store_id and a.product_id = sp.product_id)
                join users u on (u.id = bid.user_id)
                join merchants_stores ms on (ms.store_id = a.store_id)
                join users m on (m.merchant_id = ms.merchant_id)
                where bid.auction_id = $auction_id
                group by bid.id
                order by bid.id desc
                limit 2";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getNotificationInfoOnAuctionEnded($dbobj) {
        $sql = "select
                      u.username, u.first_name,
                      m.username as merchant_name, m.first_name as merchant_first_name,
                      sp.product_name, sp.product_id, sp.product_price, s.subdomain as store_subdomain,
                      a.initial_bid_price,
                      s.currency,
                      bid.*
                from user_auctions bid
                join auctions a on (bid.auction_id = a.id)
                join stores s on (s.id = a.store_id)
                join search_products sp on (a.store_id = sp.store_id and a.product_id = sp.product_id)
                join users u on (u.id = bid.user_id)
                join merchants_stores ms on (ms.store_id = a.store_id)
                join users m on (m.merchant_id = ms.merchant_id)
                where a.bid_times > 0 and a.end_time < now() and a.status = " . ACTIVATED . "
                and bid.bid_price = a.current_bid_price";
        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getAllBidersforAuction($dbobj, $auction_id) {
        $sql = "select
                      u.username, u.first_name,
                      bid.*
                from user_auctions bid
                left join users u on (u.id = bid.user_id)
                where bid.auction_id = $auction_id";
        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function setAuctionCompleted($dbobj, $auction_id) {
        $auction = new Auction($dbobj);
        $auction->findOne("id=$auction_id");
        if($auction->getId() !== 0) {
            $auction->setStatus(COMPLETED);
            $auction->save();
        }
    }
}
