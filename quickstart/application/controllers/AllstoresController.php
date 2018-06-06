<?php

class AllstoresController extends BaseController {

    public function init() {

    }

    public function indexAction() {

        $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'updated';
        if(!in_array($sort, array("updated", "name"))) $sort = 'updated';
        $dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : 'desc';
        if(!in_array($dir, array("asc", "desc"))) $dir = 'desc';
        $page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

        $query = isset($_REQUEST['store_query']) ? $_REQUEST['store_query'] : '';

        $this->view->extra_params = array(
            'sort' => $sort,
            'dir' => $dir,
        );

        if(empty($query)){
            $key = lck_stores(ACTIVATED, $sort);
            $key->$dir($sort);
            $opt = array();
            if($sort == 'name'){
                $opt['list'] = array('ignore_cache' => true);
            }
        }else{
            global $dbconfig;
            $key = CacheKey::q($dbconfig->account->name . '.stores?store_query=' . $query);
            $key->$dir($sort);
            $opt['list'] = array('ignore_cache' => true);
            $this->view->extra_params['store_query'] = $query;
        }

        $store_info = BaseMapper::getCachedObjects($key, $page, PRODUCT_NUM_PER_PAGE, $opt);
        $this->view->stores = $store_info['data'];

        // pagination
        $this->view->total_rows = $store_info['total_rows']; //$count
        $this->view->rows_per_page = PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page;
    }

}
