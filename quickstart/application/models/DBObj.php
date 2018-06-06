<?php

class DBObj {
    
    private $db;
    
    public function __construct($host, $name, $user='root', $password='') {
        
        $this->db = DB::getInstance($host, $name, $user, $password);
    }

    public function getDBName() {
        return $this->db->getDBName();
    }
    
    public function getStoreId() {
        $match=array();
        if(!preg_match("/.*_(\d+)/", $this->getDBName(), $match)) {
            return 0;
        }
        return $match[1];
    }
    
    public function query($sql) {
        return $this->db->query($sql);
    }
    
    public function escape($str) {
        return $this->db->escape($str);
    }
    
    public function get_insert_id()
    {
        return $this->db->get_insert_id();
    }

    public function fetch_array($res)
    {
        return $this->db->fetch_array();
    }
    
    public function fetch_assoc($res) {
    	return $this->db->fetch_assoc($res);
    }
    
    public function fetch_row($res) {
        return $this->db->fetch_row($res);
    }
    
    //used after delete or update db
    public function get_affected_rows(){
        return $this->db->get_affected_rows();
    }
    
    public function is_db_existed(){
        return  $this->db->is_db_existed();
    }
    
    public static function getAccountDBObj() {
        global $dbconfig;
        
        $DB_IP = $dbconfig->account->host;
        $DB_USER = $dbconfig->account->user;
        $DB_PASS = $dbconfig->account->password;
        $DB_NAME = $dbconfig->account->name;
        
        return new DBObj($DB_IP, $DB_NAME, $DB_USER, $DB_PASS);
    }
    
    public static function getStoreDBObj($host, $store_id) {
    	global $dbconfig;
        return new DBObj($host, getStoreDBName($store_id), $dbconfig->store->user, $dbconfig->store->password);
    }
    
    public static function getStoreDBObjById($store_id) {
        global $dbconfig;
        $account_dbobj = self::getAccountDBObj();
        $store = new Store($account_dbobj, $store_id);
        $host = $store->getHost();
        return new DBObj($host, getStoreDBName($store_id), $dbconfig->store->user, $dbconfig->store->password);
    }

    public static function getStoreDBObjByDBName($dbname) {
        $match=array();
        if(!preg_match("/.*_(\d+)/", $dbname, $match)) {
            return null;
        }
        $store_id = $match[1];
        return self::getStoreDBObjById($store_id);
    }
    
    public static function getJobDBObj() {
        global $dbconfig;
        $DB_IP = $dbconfig->job->host;
        $DB_USER = $dbconfig->job->user;
        $DB_PASS = $dbconfig->job->password;
        $DB_NAME = $dbconfig->job->name;

        return new DBObj($DB_IP, $DB_NAME, $DB_USER, $DB_PASS);
    }
    

}



