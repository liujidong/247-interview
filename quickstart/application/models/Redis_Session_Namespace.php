<?php

class Redis_Session_Namespace {
    private $_session_array = array();
    private $_namespace = 'default';
    
    public function __construct($namespace = 'default') {

        $this->_namespace = $namespace;
        
        $this->_session_array = &Redis_Session::get_session_array();
        
        if(!in_array($namespace, array_keys($this->_session_array))) {
            $this->_session_array[$namespace] = array();
            Redis_Session::save_session_array();
        }
    }
    
    public function __set($name, $value) {
        if(!empty($name)) {
            $this->_session_array[$this->_namespace][$name] = $value;
            Redis_Session::save_session_array();
        }
    }
    
    public function __get($name) {
        return empty($this->_session_array[$this->_namespace][$name]) ? '' : $this->_session_array[$this->_namespace][$name];
    }

    public function __isset($name) {
        return isset($this->_session_array[$this->_namespace][$name]);
    }
}
