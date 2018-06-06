<?php 
// sudo chmod 755 /var/log/httpd
class ErrorlogController extends BaseController{
	
    public function indexAction() {
        
        if(!empty($_REQUEST['key']) && $_REQUEST['key'] === 'xxx') {
            $this->_helper->layout->disableLayout();
            
            $file = '/var/log/httpd/pincommerce/error';
            exec("tail -50 $file", $output);
            $this->view->count = sizeof($output);
            $this->view->logs = $output;
            
            
        } else {
            redirect('/');
        }
        
    }
    
    public function printAction() {
        $this->_helper->layout->disableLayout();
	$this->_helper->viewRenderer->setNoRender(TRUE);
        if(!empty($_REQUEST['key']) && $_REQUEST['key'] === 'xxx') {
            
            ddd('$_SESSION:');
            ddd($_SESSION);
            
        } else {
            redirect('/');
        }
    }
    
    
}