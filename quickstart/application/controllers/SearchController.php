<?php

class SearchController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $q = empty($_REQUEST['q'])?'everything else':$_REQUEST['q'];
        $this->view->keywords = $q;
        
        $page_num = default2Int($_REQUEST['page'], 1);
        
        $results = GlobalProductsMapper::search($this->account_dbobj, $q, $page_num);
        $this->view->products = $results['products'];
        // pagination 
        $this->view->total_rows = $results['total']; //$count
        $this->view->rows_per_page = PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;
        $this->view->extra_params = array('q' => $q);

        $service = AmazonSearchService::getInstance();
        $service->setMethod('search');
        $service->setParams(array(
            'keywords' => $q
        ));
        $service->call();
        $response = $service->getResponse();

        $this->view->amazon_products = $response['view_objects'];
        $this->view->all_total = $results['total'] + $response['total_rows'];
    }

}
