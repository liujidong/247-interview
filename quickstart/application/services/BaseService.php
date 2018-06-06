<?php

class BaseService {

    protected static $service = array();
    protected $method = '';
    protected $params = array();
    protected $status = 0;
    protected $errnos = array();
    protected $errors = array();
    // "redirect_url" can be put into the "$response" array
    protected $response = array();

    public function __construct() {

    }

    public static function getInstance() {
        $class = get_called_class();
        if(!isset(self::$service[$class])) {
            self::$service[$class] = new $class();
        }
        return self::$service[$class];
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function setParams($params) {
        $this->params = $params;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getParams() {
        return $this->params;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getErrnos() {
        return $this->errnos;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getErrorMessages(){
        global $errors;
        $error_msg = array();
        foreach ($this->getErrnos() as $key => $value) {
            $error_msg[] = $errors[$key]['msg'];
        }
        return $error_msg;
    }

    public function getResponse() {
        return $this->response;
    }

    protected function initReturn() {
        $this->status = 0;
        $this->errnos = array();
        $this->response = array();
    }

    public function call() {
        // initialize the responses
        $this->initReturn();
        $method = $this->method;
        $this->$method();
    }

    public function destroy(){
        self::$service = null ;
    }

}
