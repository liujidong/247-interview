<?php


class Redis_Session {
    
    private static $_options = array('expire' => REMEMBERME_SESSION_TIME_OUT);
    private static $_session_key = '';
    private static $_session_id = '';
    private static $_session_string = '';  
    private static $_session_array = array();  
    
    // options:
    // expire (in seconds)
    public static function setOptions($options) {
        self::$_options = array_merge(self::$_options, $options);
    }
    
    public static function start() {
        global $shopinterest_config;
        $_COOKIE = array_merge($_COOKIE, $_REQUEST);
        self::$_session_key = $shopinterest_config->session_key;
        self::_init_session();
    }
    
    private static function _init_session() {
        
        if(isset($_COOKIE[self::$_session_key])) {
            global $redis;
            self::$_session_id = $_COOKIE[self::$_session_key];
            if(!self::$_session_array = $redis->getArray(self::$_session_id)) {
                self::$_session_array = array();
            }
        } else {
            self::$_session_id = md5(microtime().rand(1,9999999999999999999999999)); // GENERATE A RANDOM ID
            setcookie(self::$_session_key, self::$_session_id, time() + self::$_options['expire'], '/', getCookieDomain());
        }
    }
    
    public static function destroy() {
        
        if(empty(self::$_session_key)) {
            self::start();
        }
        
        global $redis;
        if(!empty(self::$_session_id)) {
            $redis->del(self::$_session_id);
            setcookie(self::$_session_key, "", time() -3600, '/', getCookieDomain());
        }
    }
    
    public static function save_session_array() {
        if(empty(self::$_session_key)) {
            self::start();
        }
        
        global $redis;
        $redis->setArray(self::$_session_id, self::$_session_array, self::$_options['expire']);
    }

    public static function &get_session_array() {
        if(empty(self::$_session_key)) {
            self::start();
        }
        
        return self::$_session_array;
    }
}
