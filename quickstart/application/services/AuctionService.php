<?php

class AuctionService extends BaseService {

    public function __construct() {
        parent::__construct();
    }

    public function create_auction() {
        $params = $this->params;
        $account_dbobj = $params['account_dbobj'];
        $auction_id = default2Int($params['auction_id']);
        
        $store_id = $params['store_id'];
        $product_id = $params['product_id'];
        $initial_bid_price = $params['initial_bid_price'];
        $min_bid_increment = $params['min_bid_increment'];
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];

        if(empty($store_id)) {
            $this->errnos[INVALID_STORE_ID] = 1;
            $this->status = 1;
            return;
        }
        if(empty($product_id)) {
            $this->errnos[INVALID_PRODUCT_ID] = 1;
            $this->status = 1;
            return;
        }
        if(empty($initial_bid_price) || $initial_bid_price < 0) {
            $this->errnos[INVALID_PRODUCT_PRICE] = 1;
            $this->status = 1;
            return;
        }

        if(empty($min_bid_increment) || $min_bid_increment < 0) {
            $this->errnos[INVALID_PRODUCT_PRICE] = 1;
            $this->status = 1;
            return;
        }

        if(empty($start_time)) {
            $this->errnos[INVALID_DATETIME] = 1;
            $this->status = 1;
            return;
        }

        if(empty($end_time)) {
            $this->errnos[INVALID_DATETIME] = 1;
            $this->status = 1;
            return;
        }

        if($end_time <= $start_time) {
            $this->errnos[INVALID_DATE_RANGE] = 1;
            $this->status = 1;
            return;
        }

        $auction = new Auction($params['account_dbobj'], $auction_id);
        $auction->setStatus($params['status']);
        $auction->setStoreId($store_id);
        $auction->setProductId($product_id);
        $auction->setInitialBidPrice($initial_bid_price);
        $auction->setCurrentBidPrice($initial_bid_price);
        $auction->setMinBidIncrement($min_bid_increment);
        $auction->setStartTime($start_time);
        $auction->setEndTime($end_time);
        $auction->save();

        $this->response['auction_id'] = $auction->getId();
    }

    public function create_bid() {
        global $shopinterest_config;

        $params = $this->params;

        $user_id = $params['user_id'];
        $auction_id = $params['auction_id'];
        $bid_price = sprintf("%.2f", $params['bid_price']);

        $bid = new UserAuction($params['account_dbobj']);
        $bid->setUserId($user_id);
        $bid->setAuctionId($auction_id);
        $bid->setBidPrice($bid_price);
        $bid->save();

        $notify_info = AuctionsMapper::getNotificationInfoOnNewBid($params['account_dbobj'], $auction_id);
        $outbid_info = array();
        $recvbid_info = null;
        if(count($notify_info) > 0){
            $recvbid_info = $notify_info[0];
        }
        if (count($notify_info) > 1 ) {
            $outbid_info = array_slice($notify_info, 1);
        }
        $service = new EmailService();
        if(!empty($recvbid_info)) {
            $service->setMethod('create_job');
            //send to shopper
            $service->setParams(array(
                'to' => $recvbid_info['username'],
                'from' => $shopinterest_config->support->email,
                'type' => SHOPPER_AUCTION_BID_RECEIVED_NOTIFICATION,
                'data' => array(
                    'site_url' => getURL(),
                    'product_name' => $recvbid_info['product_name'],
                    'auction_url' => getURL("/auction/item?auction_id=" . $recvbid_info['auction_id']),
                    'first_name' => _u($recvbid_info['first_name']),
                    'bid_price' => $recvbid_info['bid_price'],
                    'end_time' => $recvbid_info['end_time'],
                    'currency_symbol' => currency_symbol($recvbid_info['currency']),
                ),
                'job_dbobj' => $this->params['job_dbobj']
            ));
            $service->call();
            // send to merchant
            $service->setParams(array(
                'to' => $recvbid_info['merchant_name'],
                'from' => $shopinterest_config->support->email,
                'type' => MERCHANT_AUCTION_BID_RECEIVED_NOTIFICATION,
                'data' => array(
                    'site_url' => getURL(),
                    'product_name' => $recvbid_info['product_name'],
                    'auction_url' => getURL("/auction/item?auction_id=" . $recvbid_info['auction_id']),
                    'first_name' => _u($recvbid_info['merchant_first_name']),
                    'bid_price' => $recvbid_info['bid_price'],
                    'shopper' => $recvbid_info['first_name'],
                    'end_time' => $recvbid_info['end_time'],
                    'currency_symbol' => currency_symbol($recvbid_info['currency']),
                ),
                'job_dbobj' => $this->params['job_dbobj']
            ));
            $service->call();
        }

        $mail_sent = array();
        foreach($outbid_info as $obi){
            if(empty($obi) || ($obi['username'] == $recvbid_info['username']) ||
            array_key_exists($obi['username'], $mail_sent)) {
                continue;
            }
            $mail_sent[$obi['username']]=1;
            $service->setMethod('create_job');
            $service->setParams(array(
                'to' => $obi['username'],
                'from' => $shopinterest_config->support->email,
                'type' => SHOPPER_AUCTION_OUTBID_NOTIFICATION,
                'data' => array(
                    'site_url' => getURL(),
                    'product_name' => $obi['product_name'],
                    'auction_url' => getURL("/auction/item?auction_id=" . $recvbid_info['auction_id']),
                    'first_name' => _u($obi['first_name']),
                    'bid_price' => $obi['bid_price'],
                    'new_bid_price' => $bid_price,
                    'end_time' => $obi['end_time'],
                    'currency_symbol' => currency_symbol($obi['currency']),
                ),
                'job_dbobj' => $this->params['job_dbobj']
            ));
            $service->call();
        }

        $this->status = 0;
        $this->response['user_auction_id'] = $bid->getId();
    }

}
