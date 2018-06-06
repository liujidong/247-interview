<?php

class CategoryController extends BaseController {
    public function init() {
        
    }

    public function indexAction() {
        $page_num = default2Int($_REQUEST['page'], 1);
        $page_parts = get_path_parts($this->page_path);
        $category = $page_parts[1];
        $category_string = sanitize_words($category, ' ');
        $results = GlobalProductsMapper::search($this->account_dbobj, $category, $page_num);
        $this->view->products = $results['products'];
        // pagination 
        $this->view->total_rows = $results['total']; //$count
        $this->view->rows_per_page = PRODUCT_NUM_PER_PAGE;
        $this->view->page_num = $page_num;
        $service = AmazonSearchService::getInstance();
        $service->setMethod('search');
        $service->setParams(array(
            'keywords' => $category_string
        ));
        $service->call();
        $response = $service->getResponse();

        $this->view->amazon_products = $response['view_objects'];
        
        
    }
    
}