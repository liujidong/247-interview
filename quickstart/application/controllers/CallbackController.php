<?php

class CallbackController extends StaticController {

    public function init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }
    
    public function indexAction() {
        error_log('*****************************');
        error_log('hit the /callback');
        error_log(json_encode($_REQUEST));
        error_log('*****************************');
    }
    
    public function ipnAction() {
        
        paypal_logger('********************************');
        
        paypal_logger('/callback/ipn/ endpoint is hit');
        
        $account_dbobj = DBObj::getAccountDBObj();
        $job_dbobj = DBObj::getJobDBObj();
        
        
        $service = PaypalService::getInstance();
        $service->setMethod('process_ipn');
        $service->setParams(array(
            'payload' => $_REQUEST,
            'account_dbobj' => $account_dbobj,
            'job_dbobj' => $job_dbobj
        ));
        $service->call();
        
        paypal_logger('********************************');
    }
    
    
    
    
}
