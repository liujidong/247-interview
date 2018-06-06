<?php

class Log extends BaseModel {
        
    public static function write($type, $msg, $echo=false, $var1='', $var2='', $var3='') {
        
        static $log;        
        if(null === $log) {
            $job_dbobj = DBObj::getJobDBObj();
            $log = new Log($job_dbobj);            
        } 

        if(!is_string($msg)) {
            $msg = json_encode($msg);
        }
        
        $log->setType($type);
        $log->setMsg($msg);
        $log->setVar1($var1);
        $log->setVar2($var2);
        $log->setVar3($var3);

        $response = Log::get_info_from_backtrace();
        $log->setClass($response['class']);
        $log->setMethod($response['method']);            
        $log->setFile($response['file']);
        $log->setProcessId(getmypid()); 
        $log->setHostname(getServerAddress());
        $log->save();
        
        if($echo || isset($GLOBALS['test'])) {
            if($type === INFO) {
                $type_str = 'INFO';
            } else if($type === WARN) {
                $type_str = 'WARN';
            } else if($type === ERROR) {
                $type_str = 'ERROR';
            }
            ddd("$type_str: ".json_encode(array(
                'msg' => $msg,
                'var1' => $var1,
                'var2' => $var2,
                'var3' => $var3
            )));
        }
        
    }

    // return file,class,method,
    private static function get_info_from_backtrace() {
        $response = array(
            'file' => '',
            'class' => '',
            'method' => ''
        );
        @list(, $runner, $caller) = debug_backtrace(false);
        $response['file'] = $runner['file'];
        if(!empty($caller)) {
            $response['class'] = isset($caller['class']) ? $caller['class'] : '';
            $response['method'] = isset($caller['function']) ? $caller['function'] : '';
        }
        return $response;
    }
}