<?php

class AuctionController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $num_per_page = 50;
        $account_dbobj = $this->account_dbobj;
        $page_num = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
        $outdated = isset($_REQUEST['outdated']) && !empty($_REQUEST['outdated']);
        $auctions = AuctionsMapper::getAuctionsForShopper($account_dbobj, $page_num, $num_per_page,  $outdated);

        $this->view->outdated = $outdated;
        $this->view->auctions = $auctions;

        // pagenation
        $this->view->total_rows = AuctionsMapper::getAuctionsCntForShopper($account_dbobj, $outdated);
        $this->view->rows_per_page = $num_per_page;
        $this->view->page_num = $page_num;

        $this->view->extra_params = $outdated ? array('outdated' => 1) : array();

        $this->view->now = get_current_datetime();
    }

    public function itemAction()
    {
        $account_dbobj = $this->account_dbobj;
        if((!isset($_REQUEST['auction_id'])) || empty($_REQUEST['auction_id'])) {
            redirect(getURL());
        }
        $auction_id = (int)$_REQUEST['auction_id'];
        $auctions = AuctionsMapper::getAuctionDetail($account_dbobj, $auction_id);
        if(count($auctions)!=1){
            redirect(getURL());
        }
        $auction = $auctions[0];
        $auction['active'] = $auction['in_bid'] and $auction['status'] == ACTIVATED;
        $this->view->auction = $auction;

        $this->view->now = get_current_datetime();
    }

}