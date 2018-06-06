<?php

class RedisCache extends Redis {

    private $class = '';
    private $table = '';
    private $identifier_field = '';
    private $identifier_val = '';
    private $field = '';
    private $account_dbobj = null;
    
    public function __construct($account_dbobj) {
        $this->account_dbobj = $account_dbobj;
        parent::__construct();
    }
    
    public function get($key, $refresh=false) {
       
        if(($val = parent::get($key)) && !$refresh) {
            return $val;
        }       
        if(strpos($key, 'fb_user:')) {
            return false;
        }
        if(!$this->parseKey($key)) {
            return false;
        }
        $object = new $this->class($this->account_dbobj);
        $table_fields = array_keys($object->_toArray());
        $object->findOne("{$this->identifier_field}='".$this->account_dbobj->escape($this->identifier_val)."'");

        if($object->getId() === 0) {
            return false;
        }
        if(in_array($this->field, $table_fields)) {
            $method = 'get'.  ucfirst(to_camel_case($this->field));
            $val = $object->$method();
            if($this->identifier_field === 'id') {
                parent::set("{$this->table}:{$this->identifier_val}:{$this->field}", $val);
            } else {
                parent::set("{$this->table}:{$this->identifier_field}={$this->identifier_val}:{$this->field}", $val);
            }
            
            foreach($table_fields as $field) {
                if($field !== $this->field) {
                    $method2 = 'get'.  ucfirst(to_camel_case($field));
                    $val2 = $object->$method2();
                    parent::set("{$this->table}:{$object->getId()}:$field", $val2);
                }
            }
            return $val;
            
        } else {
            $mapper_class = ucfirst(to_plural($this->table)).'Mapper';
            $mapper_method = 'get'.  ucfirst(to_camel_case($this->field));
            if(!method_exists($mapper_class, $mapper_method)) {
                return false;
            }
            $val = $mapper_class::$mapper_method($this->identifier_val, $this->account_dbobj);
            if(empty($val)) {
                return false;
            }
            parent::set("{$this->table}:{$this->identifier_val}:{$this->field}", $val);
            return $val;
        }
    }
    
    public function getArray($key, $refresh=false) {
        if(!$value = json_decode($this->get($key, $refresh), true)) {
            return false;
        } else {
            return $value;
        }
    }
    
    // $value is an array
    public function setArray($key, $value, $expire) {
        if(is_array($value)) {
            if(isset($expire)) {
                $this->set($key, json_encode($value), $expire);
            } else {
                $this->set($key, json_encode($value));
            }
            
        } else {
            return false;
        }
        
    }
    
    public function set($key, $value, $expire=REDIS_KEY_TIME_OUT) {
        parent::set($key, $value, $expire);
    }
    
    private function parseKey($key) {
        $parts = explode(':', $key);
        if(sizeof($parts) !==  3) {
            return false;
        }
        $this->class = ucfirst(to_camel_case($parts[0]));
        $this->table = $parts[0];
        $this->field = $parts[2];
        $identifier = explode('=', $parts[1]);
        $size = sizeof($identifier);
        if($size === 1) {
            $this->identifier_field = 'id';
            $this->identifier_val = $identifier[0];
        } else if($size === 2) {
            $this->identifier_field = $identifier[0];
            $this->identifier_val = $identifier[1];
        } else {
            return false;
        }
        
        
        if(!class_exists($this->class)) {
            return false;
        }
        
        return true;
    }

    public function expire($keys, $timeout){
                
        if(is_array($keys)) {
            foreach ($keys as $key) {
                parent::expire($key, $timeout);
            }
            return;
        }
        parent::expire($keys, $timeout);
    }
    
    public function mget2($keys_array) {
        
        $response = array();
 
        foreach ($keys_array as $key) {
            $response[$key] = $this->get($key);
        }
        
        return $response;        
    }
    
}


