<?php

require 'includes.php';

$ended_auctions = AuctionsMapper::getNotificationInfoOnAuctionEnded($account_dbobj);
$service = new EmailService();

foreach ($ended_auctions as $auction) {
    $sent = array();
    // send to shopper
    $service->setMethod('create_job');
    $service->setParams(array(
        'to' => $auction['username'],
        'from' => 'xxx@shopinterest.co',
        'type' => SHOPPER_AUCTION_WINBID_NOTIFICATION,
        'data' => array(
            'site_url' => getURL(),
            'product_name' => $auction['product_name'],
            'auction_url' => getURL("/auction/item?auction_id=" . $auction['auction_id']),
            'first_name' =>_u( $auction['first_name']),
            'bid_price' => $auction['bid_price'],
            'currency_symbol' => currency_symbol($auction['currency']),
        ),
        'job_dbobj' => $job_dbobj
    ));
    $service->call();
    $sent[$auction['username']] = 1;

    $service->setMethod('create_job');
    $service->setParams(array(
        'to' => $auction['merchant_name'],
        'from' => 'xxx@shopinterest.co',
        'type' => MERCHANT_AUCTION_END_NOTIFICATION,
        'data' => array(
            'site_url' => getURL(),
            'product_name' => $auction['product_name'],
            'auction_url' => getURL("/auction/item?auction_id=" . $auction['auction_id']),
            'first_name' => _u($auction['merchant_first_name']),
            'bid_price' => $auction['bid_price'],
            'shopper' => _u($auction['first_name']),
            'currency_symbol' => currency_symbol($auction['currency']),
        ),
        'job_dbobj' => $job_dbobj
    ));
    $service->call();
    $sent[$auction['merchant_name']] = 1;

    // broadcast end info
    $winner_name = $auction['first_name'];
    if(empty($winner_name)){
        $info = explode("@", $auction['username']);
        $winner_name = $info[0] . "@...";
    }
    $biders = AuctionsMapper::getAllBidersforAuction($account_dbobj, $auctiion['auction_id']);
    foreach($biders as $bider){
        if(array_key_exists($bider['username'], $sent)) continue;
        $service->setMethod('create_job');
        $service->setParams(array(
            'to' => $bider['username'],
            'from' => 'xxx@shopinterest.co',
            'type' => BIDER_AUCTION_END_NOTIFICATION,
            'data' => array(
                'site_url' => getURL(),
                'product_name' => $auction['product_name'],
                'auction_url' => getURL("/auction/item?auction_id=" . $auction['auction_id']),
                'first_name' => _u($bider['first_name']),
                'bid_price' => $auction['bid_price'],
                'currency_symbol' => currency_symbol($auction['currency']),
                'winner_name' => $winner_name,
            ),
            'job_dbobj' => $job_dbobj
        ));
        $service->call();
        $sent[$bider['username']] = 1;
    }
    // mark auction as completed
    AuctionsMapper::setAuctionCompleted($account_dbobj, $auction['auction_id']);
}
