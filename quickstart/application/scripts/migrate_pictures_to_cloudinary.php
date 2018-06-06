<?php

if(isset($argv[1]) && $argv[1] === 'test') {
    $GLOBALS['test'] = 1;
}

require_once('includes.php');
$job_type = PICTURE_MIGRATION;
$max_run_times = 100;
$run_times = 0;

while($job_id = JobsMapper::getNextJobId($job_type, $job_dbobj)) {

    // get the config of the job
    $job_config = new JobConfig($job_dbobj);
    $job_config->findOne('type=18');
    $max_instances = $job_config->getMaxInstances();
    $instance_name = $_SERVER['SCRIPT_NAME'];
    $instances_count = get_instances_num($instance_name);
    Log::write(INFO, "Instance Count: $instances_count");
    if($instances_count > $max_instances) {
        Log::write(INFO, "ERROR: there are more than $max_instances instances of $instance_name");
        die("ERROR: there are more than $max_instances instances of $instance_name\n");
    }
    Log::write(INFO, "job id: $job_id");

    // get the data
    $job19 = new Job($job_dbobj);
    $job19->findOne('id='.$job_id);
    $data = $job19->getData();
    Log::write(INFO, "data: ".json_encode($data));

    $store_id = $data['store_id'];
    $store_dbname = getStoreDBName($store_id);

    //=================
    $store_ck = $dbconfig->account->name . ".store?id=" . $store_id;
    $store = BaseModel::findCachedOne($store_ck);
    echo "processing store ", $store['id'], ".   ";
    // 1. upload store logo
    echo "upload logo", ".   ";;
    $logo = $store['logo'];
    $converted_logo = $store['converted_logo'];
    if(!empty($logo)){
        if(empty($converted_logo) || startsWith($converted_logo, "http")){ // empty or a url
            $filename = uuid();
            $folder = cloudinary_store_misc_ns($store['id']);
            try{
                $r = \Cloudinary\Uploader::upload(
                    $logo,
                    array("public_id" => $folder . $filename, 'format' => 'jpg',)
                );
                $store_obj = new Store($account_dbobj, $store['id']);
                $store_obj->setConvertedLogo($filename);
                $store_obj->save();
            } catch(Exception $e){

            }
        }
    }
    // 2. upload product pictures
    echo "upload product pictures", ".   ";;
    $store_dbobj = DBObj::getStoreDBObjById($store['id']);
    $sql_pics = "select p.id, p.name, p.url, pp.product_id as pid
                         from pictures p
                         join products_pictures pp on (p.id = pp.picture_id)";
    if($res2 = $store_dbobj->query($sql_pics, $store_dbobj)) {
        while($pic = $store_dbobj->fetch_assoc($res2)) {
            if(!empty($pic['name'])) continue;
            $pck = CacheKey::q($dbconfig->store->name . "_" . $store['id'] . "product?id=" . $pic['pid']);
            $name = uuid();
            $folder = cloudinary_store_product_ns($store['id'], $pic['pid']);
            try{
                $r = \Cloudinary\Uploader::upload(
                    $pic['url'],
                    array("public_id" => $folder . $name, 'format' => 'jpg',)
                );
                $np = new Picture($store_dbobj, $pic['id']);
                $np->setName($name);
                $np->save();
                DAL::delete($pck);
            } catch(Exception $e) {
                $sql_cp = "select url from converted_pictures
                               where picture_id = " . $pic['id'] ."
                               order by CONVERT(type, UNSIGNED INTEGER) desc";
                if($res3 = $store_dbobj->query($sql_cp, $store_dbobj)) {
                    while($cpic = $store_dbobj->fetch_assoc($res3)) {
                        try{
                            $r = \Cloudinary\Uploader::upload(
                                $cpic['url'],
                                array("public_id" => $folder . $name, 'format' => 'jpg',)
                            );
                            $np = new Picture($store_dbobj, $pic['id']);
                            $np->setName($name);
                            $np->save();
                            DAL::delete($pck);
                            break;
                        } catch (Exception $e){
                            // ignore
                        }
                    }
                }
            }
        }
    }
    echo " ... done\n";
}
