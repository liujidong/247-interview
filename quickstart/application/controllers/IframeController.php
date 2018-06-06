<?php

class IframeController extends BaseController
{

    public function init()
    {
        $this->_helper->layout->disableLayout();

    }

    public function indexAction() {

    }

    public function pinpickerAction() {
        global $redis;

        $this->view->pinterest_username = $this->user['pinterest_username'];
        if(!empty($this->view->pinterest_username)){
            $this->view->boards = get_pinterest_account_info($this->view->pinterest_username);
        }

        //$board = new PinterestBoardPage($this->view->board_id);
        //$this->view->pins = $board->getNext();
        //$pins = get_pinterest_board_info($this->view->board_id);
        //ddd($pins);
        //dddd(get_pinterest_board_info($this->view->board_id, $pins['next_page_url']));
    }

    public function createproductsAction() {

    }

    public function createresellproductsAction() {

    }

    public function etsyimportAction() {

    }

    public function csvimportAction() {

    }

}
