<?php

class CSVService extends BaseService {
    
    protected $required_fields = array ( 'TITLE',
                                'DESCRIPTION',
                                'PRICE',
                                'CURRENCY_CODE',
                                'QUANTITY',
                                'TAGS',
                                'MATERIALS',
                            	'IMAGE1',
                                'IMAGE2',
                                'IMAGE3',
                                'IMAGE4',
                                'IMAGE5');
    
    public $fields_mapper = array('TITLE'=>'product_name',
                                'DESCRIPTION'=>'product_description',
                                'PRICE'=>'product_price',
                                'QUANTITY'=>'product_quantity',
                                //'ext_ref_url'=>'ext_ref_url',
                                'IMAGE'=>'pic_url',
                                'TAGS' =>'product_category'
                                );
    
    public $misc_fields = array('CURRENCY_CODE',
                                'TAGS',
                                'MATERIALS');
    
    public $image_columns = 5; 
    
    public function __construct($file_path){
        $save_to = get_csv_from_remote($file_path);
        $this->params['file_path'] = $save_to;
        $this->params['delimiter'] = ",";
    }
    
    protected function initReturn(){
        parent::initReturn();
        $this->response['data'] = array();
        $this->response['header'] = array();
    }
    
    public function validates(){
        
        // file existance
        if (!file_exists($this->params['file_path'])) {
            $this->errnos[FILE_EXT_ERROR] = 1;
            return false;
        }
        return true;
    }
    
    public function header_validates($keys){
        $keys = array_map('strtoupper', $keys);
        foreach ($this->required_fields as $field ){
            
            if( in_array($field, $keys) == false){
                return false;
            }
        }
        return true;
    }
    
    public function parse(){
        if (!$this->validates()) {
            return false;
        }
        
        $res = fopen($this->params['file_path'], 'r');
        $line_num = 0;
        
        while ($keys = fgetcsv($res, 0, $this->params['delimiter'])) {
            if ($line_num == 0) {
                
                if($this->header_validates($keys))
                    $this->response['header'] = $keys;
                else{
                    $this->errnos[CSV_FILE_HEADER_ERROR] = 1;
                    return false;
                }
            } else {
                array_push($this->response['data'], $keys);
            }
            
            $line_num ++;
        }
        
        fclose($res);
        return true;
    }
    
    // input: file_path, fields_mapper, $delimiter
    public function transform() {
        $file_path = $this->params['file_path'];
        $fields_mapper = $this->params['fields_mapper'];
        $delimiter = empty($this->params['delimiter'])?"\t":$this->params['delimiter'];
      
        $results = parse_csv($file_path, $delimiter);
        foreach($results as $i=>$result) {
            foreach($result as $key=>$value) {
                if($fields_mapper[$key] === 'misc') {
                    $this->response[$i]['misc'] = empty($this->response[$i]['misc'])?array($key=>$value):array_merge($this->response[$i]['misc'], array($key=>$value));
                    
                } else {
                    $this->response[$i][$fields_mapper[$key]] = $value;
                }
                
            }
            if(!empty($this->response[$i]['misc'])) {
                $this->response[$i]['misc'] = json_encode($this->response[$i]['misc']);
            }
        }
        
        $this->status = 0;
    }
    
}


