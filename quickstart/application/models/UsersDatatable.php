<?php

class UsersDatatable extends BaseDatatable{

    protected function _update() {
        if(checkIsSet($this->action_params, 'row_id', 'store_transaction_fee_waived', 'username', 'user_blocked')){
            $row_id = $this->action_params['row_id'];
            $ck = CacheKey::q($row_id);
            
            $user_model = BaseModel::findCachedOne($ck);
            $account_dbobj = DBObj::getAccountDBObj();
            if(!empty($user_model['store_id'])){
                $store_id = $user_model['store_id'];
                $store_obj = new Store($account_dbobj, $store_id);
                $store_obj->setTransactionFeeWaived($this->action_params['store_transaction_fee_waived']);
                $store_obj->save();
            }

            $user_id = $user_model['id'];
            $user = new User($account_dbobj, $user_id);
            $user->setUsername($this->action_params['username']);
            $user->save();

            $status = empty($this->action_params['user_blocked']) ? ACTIVATED : BLOCKED;
            $service = new UserService();
            $service->setMethod('update_account');
            $service->setParams(array(
                'user_id' => $user_id,
                'status' => $status,
                'account_dbobj' => $account_dbobj
            ));
            $service->call();

            DAL::delete($ck);
        }
    }

    protected function _search() {

        if(checkIsSet($this->action_params, 'q')) {
            $q = trim($this->action_params['q']);
            if(strstr($q, '@') !== false) {
                $this->action_params['condition_string'] = 'username='.$q;
            } else {
                $this->action_params['condition_string'] = 'subdomain='.$q;
            }
        }
    }
}
