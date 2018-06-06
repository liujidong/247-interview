<?php

class EmailTemplatesMapper {

    public static function getCachedObjectList($params, $dbobj) {
        $ck = $params['_cachekey'];
        $dbname = $ck->getDBName();
        $sort = $ck->getOrderInfo();
        $sort = $sort['orderby'];

        $sql = "select id, created, updated from email_templates where status !=" . DELETED;

        $tpls = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $tpls[] = $record;
            }
        }
        $keys = array();
        if(count($sort) != 1) {
            foreach($tpls as $tpl) {
                $key = $dbname.".email_template?id=".$tpl['id'];
                $keys[] = $key;
            }
        } else {
            $tpl_key = $sort[0];
            foreach($tpls as $tpl) {
                $score = 0;
                if($tpl_key == 'updated' || $tpl_key == 'created'){
                    $score = strtotime2($tpl[$tpl_key]);
                } else {
                    $score = isset($product[$tpl_key]) ? $product[$tpl_key] : 0;
                }
                $key = $dbname.".email_template?id=".$tpl['id'];
                $keys[$key] = $score;
            }
        }
        return $keys;
    }
}
