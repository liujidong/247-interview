<?php

class DB {
    private static $db_instances = array();
    private static $conns = array();
    private static $db_types = array('account', 'store', 'job');
    private $conn = null;
    private $dbhost = '';
    private $dbname = '';
    private $dbuser = '';
    private $dbpass = '';
    private $insert_id = 0;
    private $affected_rows = 0;

    /*
     * 
     *   Static Methods section
     */
    
    public static function getInstance($dbhost, $dbname, $dbuser='root', $dbpass='') {
        $dbinstance_key = self::getDBInstanceKey($dbhost, $dbname);
        if (!isset(self::$db_instances[$dbinstance_key])) {
            self::$db_instances[$dbinstance_key] = new DB($dbhost, $dbname, $dbuser, $dbpass);
        }
        return self::$db_instances[$dbinstance_key];
    }
    
    private static function get_conn($dbhost, $dbuser, $dbpass) {
        $dbhost_key = $dbhost;
        if(!isset(self::$conns[$dbhost_key]) || !is_resource(self::$conns[$dbhost_key])) {
            if(!is_resource(self::$conns[$dbhost_key] = mysql_connect($dbhost, $dbuser, $dbpass))) {
                dddd('Connection Error: '.mysql_errno());
            }
        }
        return self::$conns[$dbhost_key];
    }

    private static function getDBInstanceKey($dbhost, $dbname) {
        return $dbhost.'-'.$dbname;
    }
    
    public static function show_create_table($db_type, $table_name) {
        if(!in_array($db_type, self::$db_types)) {
            return '';
        } 
        $sql_str = file_get_contents(__DIR__."/../configs/$db_type.sql");
        $sqls = explode(';', $sql_str);
        foreach($sqls as $sql) {
            if(strpos($sql, "`$table_name`")) {
                return $sql.';';
            }
        }
    }
        
    /*
     * Instance Methods section
     * 
     */
            
    private function  __construct($dbhost, $dbname, $dbuser, $dbpass) {
        $this->dbhost = $dbhost;
        $this->dbname = $dbname;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->conn = DB::get_conn($dbhost, $dbuser, $dbpass);
    }

    public function getDBName() {
        return $this->dbname;
    }
    
    public function query($sql) {
        if(!mysql_select_db($this->dbname, $this->conn)) {
            die("Select DB Error:".$this->dbname);
        }
        $res = mysql_query($sql, $this->conn);
        $this->insert_id = mysql_insert_id($this->conn);
        $this->affected_rows = mysql_affected_rows($this->conn);
        if(!$res) {
            Log::write(WARN, "sql query return false : ".$sql);
        }
        return $res;
    }

    public function is_db_existed(){
        return  mysql_select_db($this->dbname, $this->conn);
    }
    
    public function transact($sqls) {
        if(!mysql_select_db($this->dbname, $this->conn)) {
            die("Select DB Error:".$this->dbname);
        }
        if(is_array($sqls)) {
            $start_sql = "start transaction";
            if(mysql_query($start_sql, $this->conn)) {
                return false;
            }    
            foreach($sqls as $sql) {
                if(!mysql_query($sql, $this->conn)) {
                    return false;
                }
            }
            $commit_sql = "commit";
            if(mysql_query($commit_sql, $this->conn)) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function escape($str)
    {
        return mysql_real_escape_string($str, $this->conn);
    }

    public function get_insert_id()
    {
        return $this->insert_id;
    }

    public function get_affected_rows() {
        return $this->affected_rows;
    }
    
    public function fetch_array($res)
    {
        return mysql_fetch_array($res);
    }
    
    public function fetch_assoc($res) {
        return mysql_fetch_assoc($res);
    }
    
    public function backup() {
        
        Log::write(INFO, "Start backing up db {$this->dbname} at {$this->dbhost}", true);

        $date = get_date();
        $timestamp = get_timestamp();
        global $fileuploader_config, $amazonconfig;
        $s3_bucket = $fileuploader_config->store_bucket;
        $backup_folder = $amazonconfig->s3->databasebackup_folder;
        $dst_path = '/'.$s3_bucket.$backup_folder.'/'.$date.'/';
        $user_option = '-u'.$this->dbuser;
        if(!empty($this->dbpass)) {
            $pass_option = '-p'.$this->dbpass;
        } else {
            $pass_option = '';
        }
        $host_option = '-h'.$this->dbhost;
        
        $command = "mysqldump $user_option $pass_option $host_option {$this->dbname}|gzip |aws put $dst_path{$this->dbname}-{$this->dbhost}-$timestamp.gz";
        
        Log::write(INFO, "backup command: $command", true);

        exec($command, $output);
    
        Log::write(INFO, "response: ".json_encode($output), true);
    }
}
