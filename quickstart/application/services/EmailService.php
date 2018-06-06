<?php

class EmailService extends BaseService {

    const SMTPSERVER = 'smtp.sendgrid.net';
    
    private $_username;
    private $_password;
    private $_endpoint;
    private $_smtpserver;
 
    public function __construct() {
        global $sendgridconfig;
        $this->_username = $sendgridconfig->api->username;
        $this->_password = $sendgridconfig->api->password;   
        $this->_endpoint = $sendgridconfig->api->endpoint;
        $this->_smtpserver = EmailService::SMTPSERVER;
        parent::__construct();        
    }
    
    public function send() {

        if(empty($this->params['delivery']) || $this->params['delivery'] === 'smtp') {
            $this->_send_by_smtp();
        } else if($this->params['delivery'] === 'web') {
            $this->_send_by_web();
        } else {
            $this->status = 1;
        }
        
    }
    
    // input: to, toname, from, fromname, replyto, subject, text, type, data, job_dbobj
    public function create_job() {
        global $dbconfig;

        $params = $this->params;
        $subject_text = array('subject'=>'', 'text'=>'');
        if(empty($params['subject']) && empty($params['text'])) {
            $data = $params['data'];
            $data['site_url'] = getSiteMerchantUrl();
            $data['site_logo'] = getSiteMerchantUrl(SHOPINTEREST_LOGO);
            $type = $params['type'];
            $template = BaseModel::findCachedOne($dbconfig->account->name . ".email_template?type=$type");
            if(empty($template) || $template['status'] == DELETED){
                $view = new Zend_View();
                $view->setScriptPath(APPLICATION_PATH.'/views/scripts/emails/');
                foreach($data as $key=>$value) {
                    $view->assign($key, $value);
                }
                $subject_text['subject'] = $view->render($type . "-subject.phtml");
                $subject_text['text']  = $view->render($type. ".phtml");
            } else if($template['status'] == ACTIVATED){
                $email_subject = $template['subject'];
                $email_content = $template['content'];
                if(isset($template['header']) && !empty($template['header'])){
                    $header_ck = CacheKey::q($GLOBALS['account_dbname'].'.email_template?type=' . $template['header']);
                    $header_tpl = BaseModel::findCachedOne($header_ck);
                    if(!empty($header_tpl)){
                        $email_content = $header_tpl['content'] . "\n" . $email_content;
                    }
                }
                if(isset($template['footer']) && !empty($template['footer'])){
                    $footer_ck = CacheKey::q($GLOBALS['account_dbname'].'.email_template?type=' . $template['footer']);
                    $footer_tpl = BaseModel::findCachedOne($footer_ck);
                    if(!empty($footer_tpl)){
                        $email_content = $email_content . "\n" . $footer_tpl['content'];
                    }
                }

                if($template['template_engine'] == 'php'){
                    $view = new Zend_View();
                    $view->setScriptPath(APPLICATION_PATH.'/views/scripts/emails/');
                    foreach($data as $key=>$value) {
                        $view->assign($key, $value);
                    }
                    $subject_file = "DBTPL-" . $type . "-subject.phtml";
                    file_put_contents(APPLICATION_PATH.'/views/scripts/emails/' . $subject_file, $email_subject);
                    $subject_text['subject'] = $view->render($subject_file);
                    $content_file = "DBTPL-" . $type . ".phtml";
                    file_put_contents(APPLICATION_PATH.'/views/scripts/emails/' . $content_file, $email_content);
                    $subject_text['text']  = $view->render($content_file);
                } else { // mustache
                    //$m = new Mustache_Engine;
                    //return $m->render($string, $data);
                    $subject_text['subject'] = substitute($email_subject, $data);
                    $subject_text['text']  = substitute($email_content, $data);
                }
            }
        } else {
            $subject_text['subject'] = $params['subject'];
            $subject_text['text'] = $params['text'];
        }
        
        $job = new Job($params['job_dbobj']);
        $job->setType(EMAIL_SENDER);
        $job->setData(array(
            'to' => $params['to'],
            'toname' => empty($params['toname'])?'':$params['toname'],
            'from' => $params['from'],
            'fromname' => empty($params['fromname'])?'':$params['fromname'],
            'replyto' => empty($params['replyto'])?'':$params['replyto'],
            'subject' => $subject_text['subject'],
            'text' => $subject_text['text'],
        ));
        $job->setHash1();
        $job->save();
        $this->status = 0;
        $this->response['job_id'] = $job->getId();
        
    }
    
    private function _send_by_smtp(){

        $params = $this->params;
        Log::write(INFO, "params data: ".json_encode($params));
        $config = array('ssl' => 'tls',
                'port' => '587',
                'auth' => 'login',
                'username' => $this->_username,
                'password' => $this->_password);
        
        $transport = new Zend_Mail_Transport_Smtp($this->_smtpserver, $config);
        
        $mail = new Zend_Mail();        
        $mail->setFrom($params['from'], isset($params['fromname'])?$params['fromname']:'');
        $mail->addTo($params['to'],isset($params['toname'])?$params['toname']:'');
        $mail->setSubject($params['subject']);
        $mail->setReplyTo(isset($params['replyto'])?$params['replyto']:'');
        $mail->setBodyHtml($params['text']);
        
        try {
            $mail->send($transport);
            $this->status = 0;
            Log::write(INFO, "Sengrid SMTP Send Succeeded");
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->status = 1;
            Log::write(ERROR, "Sengrid SMTP Send Failed");
        }
    }
    
    private function _send_by_web(){

        $postfields = array(
            'to' => $this->params['to'],
            'toname' => isset($this->params['toname'])?$this->params['toname']:'',
            'subject' => $this->params['subject'],
            'html' => $this->params['text'],
            'from' => $this->params['from'],
            'fromname' => isset($this->params['fromname'])?$this->params['fromname']:'',
            'replyto' => isset($this->params['replyto'])?$this->params['replyto']:'',
            'api_user' => $this->_username,
            'api_key' => $this->_password
        );

        Log::write(INFO, "POST data: ".json_encode($postfields));

        if($response = curl_post($this->_endpoint, $postfields)) {
            $response_array = json_decode($response, true);
            if($response_array['message'] === 'success') {
                $this->status = 0;
            } else if($response_array['message'] === 'error') {
                $this->status = 1;
                $this->errnos[SENDGRID_SEND_FAILED] = 1;
                $this->response['errors'] = $response_array['errors'];
            } else {
                $this->status = 1;
                $this->errnos[UNKNOWN_SENDGRID_STATUS] = 1;
            }

        } else {
            $this->status = 1;
            $this->errnos[CURL_FAILED] = 1;
        }
        Log::write(INFO, $response);   

    }
}


