<?php

class EmailsMapper {

    public static function update_email($account_dbobj, $data){
        $m = new Email($account_dbobj);
        $m->find('email = ' . $account_dbobj->escape($data['email']));
        $fields = array("email", 'source', 'first_name', 'last_name');
        foreach($fields as $f){
            if(!isset($data[$f])) continue;
            $m->set($f, $data[$f]);
        }
        if(isset($data['tags'])){
            $tags = empty2($m->getTags()) ? array() : explode(",", $m->getTags());
            $tags = array_unique(array_merge($tags, $data['tags']));
            $m->setTags(implode(',', $tags));
        }
        if(isset($data['unsubscribe'])){
            $bits = $m->getUnsubscribe();
            $bits = $bits | $data['unsubscribe'];
            $m->setUnsubscribe($bits);
        }
        $m->save();
    }
}