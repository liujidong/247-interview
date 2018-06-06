<?php

require_once('includes.php');

$store_ids = StoresMapper::getAllStoreIds($account_dbobj);

$sql = "select count(*) as cnt from pictures";

foreach ($store_ids as $store_id) {
    if(APPLICATION_ENV != 'production' && $store_id > 30){
        break;
    }

    $store = BaseModel::findCachedOne($dbconfig->account->name . ".store?id=" . $store_id);
    $user = BaseModel::findCachedOne($dbconfig->account->name . ".user?id=" . $store['user_id']);
    if($user['last_activity'] < getNdaysago(100)){
        $pic_num = 0;
        $store_dbobj=  DBObj::getStoreDBObjById($store_id);
        if($res = $store_dbobj->query($sql)) {
            if($record = $store_dbobj->fetch_assoc($res)) {
                $pic_num = $record['cnt'];
            }
        }
        if($pic_num > 0){
            echo "$pic_num\t$store_id\n";
        }
    }
}
