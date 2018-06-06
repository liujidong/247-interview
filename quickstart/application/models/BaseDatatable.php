<?php

class BaseDatatable {
    private static $_instance = null;
    private static $template_prefix = null;
    protected $mu_engine = null;
    protected $table_object = '';
    protected $action_params = array();
    protected $table_object_configs = array();
    protected $render;
    protected $page = 1;
    protected $data = array('rows' => array(), 'total_rows' => 0, 'current_page' => 0, 'views' => array());
    protected $view_data = array();
    protected $errors = array();

    private function __construct($table_object, $action_params, $table_object_configs, $render, $page) {
        $this->mu_engine = $mu_engine = new Mustache_Engine(array(
            'cache' => '/tmp/cache/mustache',
            'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../views/mustache/datatable')
        ));
        $this->table_object = $table_object;
        $this->action_params = $action_params;
        $this->table_object_configs = $table_object_configs;
        $this->render = $render;
        $this->page = $page;
        $this->table_object_name = getUnderscoreName($this->table_object);
    }
    
    public static function getInstance($table_object, $action_params, $table_object_configs, $render, $page = 1) {
        if(empty(self::$template_prefix)){
            self::$template_prefix = array(
                'header' => 'hd',
                'header_create' => 'hc',
                'header_search' => 'hs',                
                'table_header' => 'th',
                'table_header_row' => 'thr',
                'table_body' => 'tb',
                'table_body_row' => 'tbr',
                'create_form' => 'cf',
                'update_form' => 'uf',
                'search_form' => 'sf'
            );
        }
        $class = get_called_class();
        if(!isset(self::$_instance)) {
            self::$_instance = new $class($table_object, $action_params, $table_object_configs, $render, $page);
        }
        return self::$_instance;
    }

    public function setViewData($data){
        $this->view_data = $data;
    }

    public function view_data(){
        return $this->view_data;
    }

    public function get_template($type){
        $name = $this->table_object_configs['table_object'];
        $prefix = isset(self::$template_prefix[$type]) ? self::$template_prefix[$type] : $type;
        $tpl_name = $prefix."_".$name;
        if(file_exists(dirname(__FILE__)."/../views/mustache/datatable/$tpl_name.mustache")){
            return $tpl_name;
        }
        return $type;
    }

    protected function getDataRows() {
        $base_list_key = $this->table_object_configs['base_list_key'];
        if(!empty2($condition = $this->action_params['condition_string'])) {
            $base_list_key->_and(CacheKey::c($condition));
        }

        // store db
        $dbname = $base_list_key->getDBName();
        if(is_store_dbname($base_list_key->getDBName()) && !empty($this->action_params['dbname'])) {
            $base_list_key->setDBName($this->action_params['dbname']);
        }
        $cache_opt = isset($this->table_object_configs['cache_opt']) ? $this->table_object_configs['cache_opt'] : array();
        $page_size = isset($this->table_object_configs['page_size']) ? $this->table_object_configs['page_size'] : DATATABLE_ITEMS_PER_PAGE;
        return BaseMapper::getCachedObjects($base_list_key, $this->page, $page_size, $cache_opt);
    }
    
    // get data rows based on the condition
    public function read() {
        $this->data = $this->getDataRows();
        $this->data['rows'] = $this->formatRows($this->data['data']);

        unset($this->data['data']);
        $this->data['views'] = array();
        
        foreach($this->render as $tpl) {
            $this->data['views'][$tpl] = $this->$tpl();
        }
        
    } 
    
    protected function formatRows($data) {
        $rows = array();
        foreach($data as $item) {
            if(empty($item['row_id'])) {
                continue;
            }
            $rows[$item['row_id']] = $item;
        }
        return $rows;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function container() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('container'));
        return html_entity_decode($tpl->render($this));
    }
    
    public function header() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('header'));
        return html_entity_decode($tpl->render($this));
    }
    
    public function header_create() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('header_create'));
        return html_entity_decode($tpl->render($this->view_data));
    }
    
    public function header_search() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('header_search'));
        return html_entity_decode($tpl->render($this->action_params));
    }

    public function table() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('table'));
        return html_entity_decode($tpl->render($this));
    }
    
    public function table_header() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('table_header'));
        return html_entity_decode($tpl->render($this));
    }
    
    public function table_header_row() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('table_header_row'));
        return html_entity_decode($tpl->render($this->table_object_configs));
    }
    
    public function table_body() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('table_body'));
        return html_entity_decode($tpl->render($this));
    }
    
    public function table_body_rows() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('table_body_row'));
        $html = '';
        foreach($this->data['rows'] as $row) {
            $html .= html_entity_decode($tpl->render($row));
        }
        return $html;
    }
    
    public function footer() {
        $pagination = new Pagination2;
        $page_size = isset($this->table_object_configs['page_size']) ? $this->table_object_configs['page_size'] : DATATABLE_ITEMS_PER_PAGE;
        return $pagination->pagination($this->data['total_rows'], $page_size,
                $this->data['current_page'], array('table_object' => $this->table_object));
    }
    
    public function create_form() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('create_form'));
        return html_entity_decode($tpl->render($this));
    }
    
    public function update_form() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('update_form'));
        return html_entity_decode($tpl->render($this));
    }
    
    public function delete_form() {
        $tpl = $this->mu_engine->loadTemplate($this->get_template('delete_form'));
        return html_entity_decode($tpl->render($this));
    }

    protected function _create() {}

    public function create(){
        $this->_create();
        $this->read();
    }

    protected function _update() {}

    public function update(){
        $this->_update();
        $this->read();
    }

    protected function _delete() {}

    public function delete(){
        $this->_delete();
        $this->read();
    }

    protected function _search() {}

    public function search(){
        $this->_search();
        $this->read();
    }
    
}
