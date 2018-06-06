<?php

class AssociateService extends BaseService {
    
    // input: code, account_dbobj
    public function verify() {
        $code = $this->params['code'];
        $account_dbobj = $this->params['account_dbobj'];
        
        $id_hash = explode('_', $code);
        if(sizeof($id_hash) !== 2) {
            $this->status = 1;
            $this->errnos['INVALID_VERIFICATION_CODE'] = 1;
            return;
        }
        $id = $id_hash[0];
        $hash = $id_hash[1];
        
        $associate = new Associate($account_dbobj);
        $associate->findOne('id='.$id);
        if($associate->getId() === 0) {
            $this->status = 1;
            $this->errnos['INVALID_VERIFICATION_CODE'] = 1;
            return;
        }
        $email = $associate->getUsername();
        if($hash !== md5($email)) {
            $this->status = 1;
            $this->errnos['INVALID_VERIFICATION_CODE'] = 1;
            return;
        }
        $this->status = 0;
        $associate->setStatus(VERIFIED);
        $associate->save();
    }
    
}


