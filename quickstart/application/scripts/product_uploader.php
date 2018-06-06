<?php
// job type: 9
// input：store_id product_id 
// output PROCESSED

require_once('includes.php');
global $pinterest_config;
$job_type=PRODUCT_UPLOADER;
$pinterest_account=$pinterest_config->email;

if(!login()){
    die("Auto-login false\n");
}
$csrfmiddlewaretoken=get_csrftocken_from_cookie($pinterest_account); 

while($job_id=  JobsMapper::getNextJobId($job_type, $job_dbobj)){
    $job_config = new JobConfig($job_dbobj);
    $job_config->findOne('type='.$job_type);
    $max_instances = $job_config->getMaxInstances();
    $instance_name = $_SERVER['SCRIPT_NAME'];
    $instances_count = get_instances_num($instance_name);
    echo "Instance Count: $instances_count\n";
    if($instances_count > $max_instances) {
        die("ERROR: there are more than $max_instances instances of $instance_name\n");
    }
    
    echo "job id: $job_id\n";
    $status = PROCESSED;
    $job9 = new Job($job_dbobj);
    $job9->findOne('id='.$job_id);
    $data = $job9->getData();
    echo "data: ".json_encode($data)."\n";
    $store_id = $data['store_id'];
    $product_id=$data['product_id'];
    
    $store_obj = new Store($account_dbobj);
    $store_obj->findOne("id=$store_id");
    $subdomain=$store_obj->getSubdomain();
    $domain=$subdomain.'.'.getSiteDomain();
    $account_obj=new PinterestAccount($account_dbobj);
    $account_obj->findOne("username="."'".$pinterest_config->username."'");
    
    $boardslists=  PinterestAccountsMapper::getBoards($account_obj->getId(), $account_dbobj, "name="."'".$subdomain."'");
    $board_obj=new PinterestBoard($account_dbobj);
    foreach ($boardslists as $boardslist) {
        $board_obj->findOne("id=${boardslist['id']}");
    }
    $board_externalid=$board_obj->getExternalId();

    if(empty($board_externalid)){

        $postitem= array( 'name'=>$subdomain,'category'=>'architecture');
        $result=boardcreator($postitem,$csrfmiddlewaretoken);
        if($result['http_code']===403){
            login();
            $csrfmiddlewaretoken=get_csrftocken_from_cookie($pinterest_account); 
            $status=CREATED;
            $job9->setStatus($status);
            $job9->save();
            continue;
        }elseif ($result['http_code']===200 && $result['html']['status']==="success") {
            $board_obj->setName($subdomain);
            $board_obj->setExternalId($result['html']['id']);
            $board_obj->save();
            BaseMapper::saveAssociation($account_obj, $board_obj, $account_dbobj);
            echo 'Save association between account_id: '.$account_obj->getId().' and board_id: '.$board_obj->getId().'\n'; 
            echo "Success on creating $subdomain storeId : $store_id\n";
        }else{
            //debug
            $status=FAILED;
            ddd($result);
        }
    }

    //upload pins to this boards
    $store_dbobj=  DBObj::getStoreDBObj($store_obj->getHost(), $store_obj->getId());
    $pictures=ProductsMapper::getPictureByProductId($product_id, $store_dbobj);

    $product_obj=new Product($store_dbobj);
    $product_obj->findOne('id='.$product_id);
    $productitem=array(
        'board'=>$board_obj->getExternalId(),
        'details'=>  get_product_description($product_obj->getDescription()),
        'link'=>$domain ."/products/item?id=".$product_obj->getId(),
        'img_url'=>$pictures['mobile_img'],
        'tags'=>'',
        'buyable'=>'$'.$product_obj->getPrice(),
        'csrfmiddlewaretoken'=>$csrfmiddlewaretoken
    );
    $res=pinuploader($productitem);
    if($res['http_code']===403){
        login();
        $csrfmiddlewaretoken=get_csrftocken_from_cookie($pinterest_account); 
        $status=CREATED;
        $job9->setStatus($status);
        $job9->save();
        continue;
    }elseif ($res['http_code']===200&&$res['html']['status']==="success") {
        $pin_obj=new PinterestPin($account_dbobj);
        $pin_id=  getPinIdFromUrl($res['html']['url']);
        if(!empty($pin_id)){
            $pin_obj->setExternalId($pin_id);
            $pin_obj->setDescription($product_obj->getDescription());
            $pin_obj->setImagesBoard($pictures['mobile_img']);
            $pin_obj->save();
            BaseMapper::saveAssociation($board_obj, $pin_obj, $account_dbobj);
            echo 'Save association between board_id: '.$board_obj->getId().' and pin_id: '.$pin_obj->getId()."\n"; 
            echo "Success on pins pin_external_id : $pin_id\n";
        }
    }else{
        $status=FAILED;
        ddd($res);
    }
    
    
    //save status
    $job9->setStatus($status);
    $job9->save();
    //after upload a store's product to pinterest , need to waiting here
    $sleep=$job_config->getSleep();
    sleep($sleep); 
}

