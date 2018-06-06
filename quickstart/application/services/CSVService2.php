<?php

class CSVService2 extends BaseService {

    private $_file_path;
    private $_delimiter = ",";
    
    private function _set_file_path($file_path) {
        $this->_file_path = $file_path;        
    }
    
    public function parse() {
        
        $file_path = $this->params['file_path'];
        $csv_array = array();
        $keys = array();  
        
        $this->_set_file_path($file_path);        

        if (($handle = fopen($this->_file_path, 'r')) !== false) {
            $line_num = 0;
            
            while (($date = fgetcsv($handle, 0, $this->_delimiter)) !== false) {
                
                if ($line_num === 0 ) {
                    $keys = $date;
                }
                $cnt = count($date);
            
                for ($i=0; $i<$cnt; $i++) {
                    $csv_array[$line_num][$keys[$i]] = $date[$i];
                }
                $line_num++;
            }
            fclose($handle);    
            array_shift($csv_array);
        }

        $this->response = $csv_array;
    }

}


