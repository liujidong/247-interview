<?php

class CustomersDatatable extends BaseDatatable{

    protected function _create(){
        if(checkIsSet($this->action_params, 'first_name', 'last_name', 'email')) {
            $store_dbobj = DBObj::getStoreDBObjByDBName($this->action_params['dbname']);
            $customer = array(
                'first_name' => $this->action_params['first_name'],
                'last_name' => $this->action_params['last_name'],
                'cust_emails' => array(
                    array(
                        'email' => $this->action_params['email']
                    )
                )
            );
            BaseModel::saveObjects($store_dbobj, $customer, 'customer');
        }
    }

    protected function _update(){
        if(checkIsSet($this->action_params, 'first_name', 'last_name', 'email', 'row_id')) {
            $store_dbobj = DBObj::getStoreDBObjByDBName($this->action_params['dbname']);
            $customer_model = BaseModel::findCachedOne(CacheKey::q($this->action_params['row_id']));
            $customer = array(
                'id' => $customer_model['customer_id'],
                'first_name' => $this->action_params['first_name'],
                'last_name' => $this->action_params['last_name'],
                'cust_emails' => array(
                    array(
                        'id' => $customer_model['email_id'],
                        'email' => $this->action_params['email']
                    )
                )
            );
            BaseModel::saveObjects($store_dbobj, $customer, 'customer');
        }
    }

    protected function _delete(){
        if(checkIsSet($this->action_params, 'row_id')) {
            $store_dbobj = DBObj::getStoreDBObjByDBName($this->action_params['dbname']);
            $customer_model = BaseModel::findCachedOne(CacheKey::q($this->action_params['row_id']));
            $customer = array(
                'id' => $customer_model['customer_id'],
                'status' => DELETED,
                'cust_emails' => array(
                    array(
                        'id' => $customer_model['email_id'],
                        'status' => DELETED
                    )
                )
            );
            BaseModel::saveObjects($store_dbobj, $customer, 'customer');
        }
    }

    public function send_email(){
        if(checkIsSet($this->action_params, 'row_ids', 'subject', 'content')){
            global $shopinterest_config, $dbconfig;
            $max_allowed_customer_emails = $shopinterest_config->store->max_allowed_customer_emails;
            $row_id_array = explode(',', $this->action_params['row_ids']);
            $num_emails = sizeof($row_id_array);
            $store_dbobj = DBObj::getStoreDBObjByDBName($this->action_params['dbname']);
            
            if(StoresMapper::getCurrentScheduledJobsCount($store_dbobj, EMAIL_SENDER) + $num_emails >= $max_allowed_customer_emails) {
                $this->errors[] = $GLOBALS['errors'][EXCEED_MAX_ALLOWED_CUSTOMER_EMAILS];                    
                return;
            }

            $store_id = parse_store_dbname($this->action_params['dbname']);
            $store = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name.'.store?id='.$store_id));
            $user = BaseModel::findCachedOne(CacheKey::q($dbconfig->account->name.'.user?id='.$store['uid']));
            $fullname = trim($user['first_name'].' '.$user['last_name']);            
            $fromname = empty($fullname)?$store['name']:$fullname;
            $replyto = $user['username'];
            $from = 'xxx@shopinterest.co';
            $job_dbobj = DBObj::getJobDBObj();
            
            foreach($row_id_array as $row_id) {

                $customer = BaseModel::findCachedOne(CacheKey::q($row_id));

                $toname = trim($customer['first_name'].' '.$customer['last_name']);
                $toemail = $customer['email'];

                // input: to, toname, from, fromname, replyto, subject, text, type, data, job_dbobj
                // create an email job
                $subject = $this->action_params['subject'];
                $text = nl2br($this->action_params['content']);

        
                $service = new EmailService();
                $service->setMethod('create_job');   
                $service->setParams(array(
                    'to' => $toemail,
                    'from' => $from,
                    'subject' => $subject,
                    'text' => $text,
                    'toname' => $toname,
                    'fromname' => $fromname,
                    'replyto' => $replyto,        
                    'job_dbobj' => $job_dbobj
                ));
                $service->call();
                $service_response = $service->getResponse();
                
                // create a record in scheduled_jobs
                $scheduled_job = new ScheduledJob($store_dbobj);
                $scheduled_job->setType(EMAIL_SENDER);
                $scheduled_job->setJobId($service_response['job_id']);
                $scheduled_job->save();            
            }        
        }
    }
}