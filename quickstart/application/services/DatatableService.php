<?php

class DatatableService extends BaseService {

    private static $tableConfigs = array();
    
    public static function getTableConfigs() {
        if(!self::$tableConfigs) {
            self::$tableConfigs = array(
                'slider_featured_products' => array(
                    'base_list_key' => lck_featured_products(),
                    'table_header_row' => array('Action', 'Product URL', 'Score'),
                ),
                'ad_featured_products' => array(
                    'base_list_key' => lck_featured_products(AD_FEATURED),
                    'table_header_row' => array('Action', 'Product URL', 'Score'),
                ),
                'myorders' => array(
                    'base_list_key' => lck_myorders_base(),
                    'table_header_row' => array('Status', 'Order Number', 'Customer', 'Time', 'Price'),
                    'cache_opt' => array('ignore_cache' => true),
                ),
                'myorders_for_shopper' => array(
                    'base_list_key' => lck_myorders_base(),
                    'table_header_row' => array('Status', 'Order Number', 'Time', 'Price'),
                    'cache_opt' => array('ignore_cache' => true),
                ),
                'myorders_for_admin' => array(
                    'base_list_key' => lck_myorders_base(),
                    'table_header_row' => array('Status', 'Order Number', 'Store ID', 'Customer', 'Time', 'Price', 'Action'),
                    'cache_opt' => array('ignore_cache' => true),
                ),
                'analytics' => array(
                    'base_list_key' => CacheKey::q('analytics'),
                    'table_header_row' => array('Page', 'Visitors', 'PageViews', 'AvgTimeOnPage(seconds)', 'VisitBounceRate(%)'),
                ),
                'store_customers' => array(
                    'base_list_key' => lck_store_customers(),
                ),
                'wallet_payments' => array(
                    'base_list_key' => CacheKey::q($GLOBALS['account_dbname'].'.wallet_activities')->desc('updated'),
                    'table_header_row' => array('ID', 'Status', 'Type', 'Ref-ID', 'Amount', 'Balance', 'Available Balance'),
                    'cache_opt' => array('ignore_cache' => true),
                ),
                'resell_order_items' => array(
                    'base_list_key' => CacheKey::q($GLOBALS['account_dbname'].'.resell_order_items')->desc('updated'),
                    'table_header_row' => array('Product', 'Status', 'Quantity', 'Price', 'Commission', 'Balance'),
                    'cache_opt' => array('ignore_cache' => true),
                ),
                'store_coupon' => array(
                    'base_list_key' => lck_store_coupon(),
                    'table_header_row' => array('Action', 'Code', 'Scope', 'Start Time',
                    'End Time', 'Price Offer Type', 'Price Off', 'Offer Name',
                    'Offer Description', 'Usage Limited', 'Usage Restriction', 'Free Shipping', 'Pub on deals page')
                ),
                'admin_coupon' => array(
                    'base_list_key' => lck_store_coupon(),
                    'table_header_row' => array('Action', 'Code', 'Scope', 'Start Time',
                    'End Time', 'Price Offer Type', 'Price Off', 'Offer Name',
                    'Offer Description', 'Usage Limited', 'Usage Restriction', 'Free Shipping', 'Pub on deals page')
                ),
                'all_users' => array(
                    'base_list_key' => CacheKey::q($GLOBALS['account_dbname'].'.users?status!=127')
                    ->desc('created')->label('admin:all_users'),
                    'table_header_row' => array(
                        'Email', 'User Status', 'First Name', 'Last Name', 'Phone', 'Aid', 'Last Login', 'Last Activity',
                        'Credit card on File', 'Pinterest URL',
                        'Store', 'Store Status', 'Store Created Time', 'Country', 'Currency', 'Tax', 'Paypal Fee Waived',
                        'Active Products CNT', 'Inactive Products CNT', 'Total Sale Transactions ', 'Total Sale Amount', 'Sales', 'Action'),
                    'cache_opt' => array('list' => array('ignore_cache' => true)),
                    'page_size' => 60,
                ),
                'email_templates' => array(
                    'base_list_key' => CacheKey::q($GLOBALS['account_dbname'].'.email_templates?status!=127')->desc('created'),
                    'table_header_row' => array('ID', 'Status', 'Type', 'Subject', 'Created', 'Updated', 'Action'),
                    'cache_opt' => array('list' => array('ignore_cache' => true)),
                    'page_size' => 60,
                ),
                'amazon_products' => array(
                    'base_list_key' => lck_amazon_products_base(),
                    'table_header_row' => array('ASIN', 'Name', 'Price', 'Category', 'Featured', 'F-Score', 'Updated', 'Action'),
                    'cache_opt' => array('list' => array('ignore_cache' => true)),
                    'page_size' => 60,
                ),
            );
            self::$tableConfigs = array_merge(self::$tableConfigs, self::toBeMergedTableConfigs());
        }

        return self::$tableConfigs;
    }
    
    private static function toBeMergedTableConfigs() {
        $l1_category_keys = DAL::get(lck_categories(true));
        $configs = array();
        foreach($l1_category_keys as $category_key) {
            $category = DAL::get(CacheKey::q($category_key));
            $category_name = sanitize_string($category['name']);
            $configs[$category_name.'_featured_products'] = array(
                'base_list_key' => lck_featured_products(CATEGORY_FEATURED, $category['id']),
                'table_header_row' => array('Action', 'Product URL', 'Score'),
                'extra_params' => array(
                    'category' => $category,
                    'category_name' => $category_name
                )
            );
        }
        return $configs;
    }
    
    // input: table_object, action, action_params, account_dbobj, render, page
    public function process() {
        $table_object = $this->params['table_object'];
        $action = $this->params['action'];
        $action_params = $this->params['action_params'];
        $render = $this->params['render'];
        $page = $this->params['page'];
        $view_data = $this->params['view_data'];

        if(!$table_object_configs = self::getTableObjectConfigs($table_object)) {
            $this->status = 1;
            $this->errors[] = $GLOBALS['errors'][NO_TABLEOBJECT_DEFINED];
            return;
        }
        $table_object_configs['table_object'] = $table_object;
        $table_object_class = self::getTableObjectDatatableClass($table_object);
        $tableObject = $table_object_class::getInstance($table_object, $action_params, $table_object_configs, $render, $page);
        $tableObject->setViewData($view_data);
        $tableObject->$action();

        $this->response = $tableObject->getData();
        $this->errors = $tableObject->getErrors();
        
        if(empty($this->errors)) {
            $this->status = 0;
        } else {
            $this->status = 1;
        }
    }
    
    public static function getTableObjectConfigs($table_object) {
        $table_configs = self::getTableConfigs();
        return empty($table_configs[$table_object])?false:$table_configs[$table_object];
    }
    
    public static function getTableObjectDatatableClass($table_object) {
        $table_object_configs = self::getTableObjectConfigs($table_object);
        $base_list_key = $table_object_configs['base_list_key'];
        return ucfirst(to_camel_case(to_plural($base_list_key->getEntity()))).'Datatable';
    }
    
    
    
}
