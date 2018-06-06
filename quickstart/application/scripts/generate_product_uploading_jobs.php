<?php
require_once('includes.php');

//input store_id store_status, status =0 means a store is unlaunch , status=2 means store is launch
//      product status 0 means onsell 127 means delete
//output job_type: PRODUCT_UPLOADER PRODUCT_DELETE_BOARD PRODUCT_DELETE_PIN

$store_infos=StoresMapper::getAllStoreIdsAndStatus($account_dbobj);
foreach ($store_infos as $store_info) {

    if($store_info['status']==='0'){
        //delete board
        $job10=new Job($job_dbobj);
        $job10->setType(PRODUCT_DELETE_BOARD);
        $job10->setData(array(
                'store_id'=>$store_info['id'],
                'product_id'=>0
        ));
        $job10->setHash1();
        $job10->save();
        Log::write(INFO, 'Created Product Image Uploading job DELETE BOARD store_id '.$store_info['id']);
    } else {
        $store=new Store($account_dbobj);
        $store->findOne('id='.$store_info['id']);
        $store_dbobj = DBObj::getStoreDBObj($store->getHost(), $store->getId());
        if($store_dbobj->is_db_existed()){
            $new_product_ids=  ProductsMapper::getDailyProductIds($store_dbobj);
            
            foreach ($new_product_ids as $new_product_id) {
                $type=PRODUCT_UPLOADER;
                //create a product_upload job
                if($new_product_id['status']==="127"){
                    $type=PRODUCT_DELETE_PIN;
                }
                $job=new Job($job_dbobj);
                $job->setType($type);
                $job->setData(array(
                        'store_id'=>$store_info['id'],
                        'product_id'=>$new_product_id['id']
                ));
                $job->setHash1();
                $job->save();
                if($type===PRODUCT_DELETE_PIN){
                    Log::write(INFO, 'Created Product Image Uploading job DELETE PIN store_id '.$store_info['id'].' product_id '.$new_product_id['id']);
                }else{
                    Log::write(INFO, 'Created Product Image Uploading job UPLOAD PIN store_id '.$store_info['id'].' product_id '.$new_product_id['id']);
                }
            }
        }
    }
}