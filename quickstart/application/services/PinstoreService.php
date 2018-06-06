<?php

class PinstoreService extends BaseService {
    
    public function __construct() {
        parent::__construct();
    }
    
    // input: pinterest_email, pinterest_password, account_dbobj, job_dbobj
    // output:
    // - false on auth failure
    // - array(
    //  'account' => array('id' => $pinterest_account_id, ...),
    //  'boards' => array(...)
    // )
    // steps:
    // - PinterestBrowser::login, get the pinterest username
    // - Create a pinterest account if it doesnt exist
    // - PinterestService::import_account_boards
    // - get account info
    // - get boards info
    // - pack the results and send back 
    public function login() {
        
        $pinterest_email = $this->params['pinterest_email'];
        $pinterest_password = $this->params['pinterest_password'];
        $account_dbobj = $this->params['account_dbobj'];
        $job_dbobj = $this->params['job_dbobj'];

        $browser = PinterestBrowser::getInstance($pinterest_email, $pinterest_password);
        $pinterest_username = $browser->login();
        Log::write(INFO, 'pinterest_username: '.$pinterest_username);
        
        if(empty($pinterest_username)) {
            Log::write(WARN, 'pinterest login failed');
            $this->status = 1;
            return;
        }
        Log::write(INFO, 'pinterest login successfully');
        // create a pinterest account
        $pinterest_account = new PinterestAccount($account_dbobj);
        $pinterest_account->findOne("username='".$account_dbobj->escape($pinterest_username)."'");
        $pinterest_account_id = $pinterest_account->getId();
        if(empty($pinterest_account_id)) {
            if(!$pinterest_account->setUsername($pinterest_username)) {
                Log::write(WARN, 'failed on creating a new pinterest account');
                $this->status = 1;
                return;
            } else {
                $pinterest_account->setExternalId();
                $pinterest_account->save();
                $pinterest_account_id = $pinterest_account->getId();
            }
            Log::write(INFO, 'succeeded on creating a new pinterest account: '.$pinterest_account_id.' '.$pinterest_username);
        }
        $this->response = array('account' => array(
            'id' => $pinterest_account_id,
            'username' => $pinterest_username
        ), 'boards' => array());
        
        Log::write(INFO, 'start to import boards info for the pinterest account: '.$pinterest_account_id.' '.$pinterest_username);
        // import account and boards info
        $service = new PinterestService();
        $service->setMethod('import_account_boards');
        $service->setParams(array(
            'pinterest_account_id' => $pinterest_account_id,
            'account_dbobj' => $account_dbobj,
            'job_dbobj' => $job_dbobj,
            'force' => true
        ));
        $service->call();
        $account_info = $service->getResponse();
        // get boards info

        $this->response['boards'] = $account_info['boards']; 
        $this->status = 0;
    }
    
    // input: pinterest_email, pinterest_password, pinterest_account_id, pinterest_boardname or pinterest_board_id
    // store_id, store_dbobj, account_dbobj, job_dbobj 
    // output: 0/1
    // steps:
    // - if pinterest_boardname exists, call PinterestBrowser::create_board, 
    // create a pinerest board in pinterest_boards, get the pinterest_board_id
    // - create a type 7 job with the data: 
    // pinterest_email, pinterest_password, pinterest_account_id, pinterest_board_id, store_id
    
    public function upload_pins() {
        $pinterest_email = $this->params['pinterest_email'];
        $pinterest_password = $this->params['pinterest_password'];
        $pinterest_account_id = $this->params['pinterest_account_id'];
        $pinterest_boardname = $this->params['pinterest_boardname'];
        $pinterest_board_id = $this->params['pinterest_board_id'];
        $store_id = $this->params['store_id'];
        $store_dbobj = $this->params['store_dbobj'];
        $account_dbobj = $this->params['account_dbobj'];
        $job_dbobj = $this->params['job_dbobj'];
        
        Log::write(INFO, "pinterest_email: $pinterest_email pinterest_account_id: $pinterest_account_id 
            pinterest_boardname: $pinterest_boardname pinterest_board_id: $pinterest_board_id store_id: $store_id");
        
        if(!empty($pinterest_boardname)) {
            Log::write(INFO, 'create a new board');
            // create a new board
            $browser = new PinterestBrowser($pinterest_email, $pinterest_password);
            $return = $browser->create_board(array(
                'name' => $pinterest_boardname
            ));
            if(!$return || $return['status'] !== 'success') {
                Log::write(WARN, 'failed on creating a new board');
                $this->status = 1;
                return;
            } else {
                Log::write(INFO, 'succeeded on creating a new board');
                $external_pinterest_board_id = $return['id'];
                $boardname = $return['name'];
                $board_url = $return['url'];
                
                $board = new PinterestBoard($account_dbobj);
                $board->findOne("external_id='".$account_dbobj->escape($external_pinterest_board_id)."'");
                if($board->getId() === 0) {
                    $board->setExternalId($external_pinterest_board_id);
                    $board->setName($boardname);
                    $board->setUrl($board_url);
                    $board->save();
                }
                $pinterest_board_id = $board->getExternalId();
                Log::write(INFO, 'create a new board in pinterest_boards table '.$pinterest_board_id);
                $pinterest_account = new PinterestAccount($account_dbobj);
                $pinterest_account->setId($pinterest_account_id);
                
                BaseMapper::saveAssociation($pinterest_account, $board, $account_dbobj);
            }
            
        }

        $scheduled_job = new ScheduledJob($store_dbobj);
        $scheduled_job->save();
        
        // create a type 7 job
        $job_type = PIN_STORE_PRODUCTS;
        $data = array(
            'pinterest_email'=>$pinterest_email, 
            'pinterest_password'=>encrypt($pinterest_password), 
            'pinterest_account_id' => $pinterest_account_id,
            'pinterest_board_id' => $pinterest_board_id,
            'store_id' => $store_id,
            'scheduled_job_id' => $scheduled_job->getId()
        );
        
        $data_encoded = json_encode($data);
        $hash1 = md5($job_type.$data_encoded.CREATED);
        $job7 = new Job($job_dbobj);
        $job7->findOne("hash1='$hash1'");
        $job7_id = $job7->getId();

        if(empty($job7_id)) {
            $job7->setType($job_type);
            $job7->setData($data);
            $job7->save();
            Log::write(INFO, 'created a type 7 job '.$data_encoded);
            // create a record in scheduled_jobs
            $scheduled_job->setType($job_type);
            $scheduled_job->setJobId($job7->getId());
        } else {
            $scheduled_job->setStatus(DELETED);
        }
        $scheduled_job->save();
        Log::write(INFO, 'created a scheduled job '.$job7->getId());

        $this->status=0;
    }
    
}


