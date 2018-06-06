<?php
class TicketController extends BaseController{

    public function indexActionV1() {
        $data = $this->_request->getPost();
        if(!empty($data)){
            unset($data['submit']);
            $errors = array();
            if(empty($data['email'])){
                $errors[] = $GLOBALS['errors'][INVALID_EMAIL]['msg'];
            }
            else{
                $validator = new Zend_Validate_EmailAddress();
                if (!$validator->isValid($data['email'])) {
                    $errors[] = $GLOBALS['errors'][INVALID_EMAIL]['msg'];
                }
            }

            if(empty($data['subject'])){
                $errors[] = $GLOBALS['errors'][INVALID_TICKET_SUBJECT]['msg'];
            }

            if(empty($data['description'])){
                $errors[] = $GLOBALS['errors'][INVALID_TICKET_DESCRIPTION]['msg'];
            }

            if(empty($errors)){
                $this->view->submit = true;
                //send an email to our emails
                global $shopinterest_config;
                $to_emails = array('xxx@shopinterest.co');
                $service = EmailService::getInstance();

                $service->setMethod('create_job');
                foreach($to_emails as $to){
                    $service->setParams(array(
                            'to' => $to,
                            'from' => $shopinterest_config->support->email,
                            'type' => MERCHANT_TICKET,
                            'data' => $data,
                            'subject'=>$data['subject'],
                            'text' => $data['description'],
                            'replyto' => $data['email'],
                            'job_dbobj' => $this->job_dbobj
                    ));
                    $service->call();
                }

            }
            else
                $this->view->ticket_errors = $errors;

        }
    }

    public function indexAction() {}
}