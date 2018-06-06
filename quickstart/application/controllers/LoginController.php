<?php
class LoginController extends BaseController{

    public function indexAction() {
        if(isset($_REQUEST['submit'])) {

            if(empty($_REQUEST['username'])) {
                $this->view->errnos[INVALID_MERCHANT_LOGIN] = 1;
                return;
            }
            if(empty($_REQUEST['password'])) {
                $this->view->errnos[INVALID_MERCHANT_LOGIN] = 1;
                return;
            }
            $username = trim($_REQUEST['username']);
            $password = trim($_REQUEST['password']);
            if(empty($username)) {
                $this->view->errnos[INVALID_MERCHANT_LOGIN] = 1;
                return;
            }
            if(empty($password)) {
                $this->view->errnos[INVALID_MERCHANT_LOGIN] = 1;
                return;
            }

            $service = AccountsService::getInstance();
            $service->setMethod('login');
            $service->setParams(array('username'=>$username, 'password'=>$password,
            'account_dbobj'=>$this->account_dbobj));
            $service->call();
            $status = $service->getStatus();

            if($status === 0) {
                $response = $service->getResponse();

                if($response['logged_in'] === 1) {
                    $user = $response['user'];
                    $this->user_session->user_id = $user->getId();
                    $this->user_session->merchant_id = $user->getMerchantId();

                    if(!empty($_GET['next'])) {
                        redirect($_GET['next']);
                    }

                    if($this->user_session->merchant_id !== 0) {
                        redirect('/selling/settings');
                    } else if($this->user_session->user_id !== 0) {
                        redirect('/me/settings');
                    }
                } else {
                    $this->view->errnos[INVALID_MERCHANT_LOGIN] = 1;
                    return;
                }
            }
        }
    }
}
?>