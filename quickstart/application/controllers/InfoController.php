<?php

class InfoController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
    }
    
    function indexAction() {
        //dddd(1);//
        global $dbconfig;
        $store_id = 1;
        $ck = CacheKey::q($dbconfig->account->name . ".store?id=$store_id");
        $store = DAL::get($ck);
        //dddd($store);
        if(empty2($store['converted_logo'])) $store['converted_logo'] = "/img/merchant_placeholder.jpg";
        if(empty2($store['external_website'])) $store['external_website'] = "/";
        $this->view->store = $store;
    }

}
