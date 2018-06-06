<?php

class ProductsMapper {

    public static function getAllProductsCnt($store_id){
        $dbobj = DBObj::getStoreDBObjById($store_id);
        $sql = "select count(1) as cnt from products where status != " . DELETED;
        $return = 0;
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['cnt'];
            }
        }
        return $return;
    }

    public static function getProductById($prod_id, $dbobj){

        $sql = "select p.*, group_concat(pc.url) as url from products p
        join products_pictures pp on p.id = pp.product_id
        join pictures pc on pc.id = pp.picture_id where p.id = $prod_id and p.status = 0 group by p.id";
        $return = array();

        if($res = $dbobj->query($sql, $dbobj)) {

            if($record = $dbobj->fetch_assoc($res)) {
                $urls = explode(',', $record['url']);
                foreach($urls as $u){
                    if(preg_match('/^.*(_t)\..*$/i', $u)){
                        $record['thumbnail_img'] = $u;
                    }
                    else if(preg_match('/^.*(_c)\..*$/i', $u)){
                        $record['closeup_img'] = $u;
                    }
                    else if(preg_match('/^.*(_b)\..*$/i', $u)){
                        $record['board_img'] = $u;
                    }
                    else if(preg_match('/^.*(_f)\..*$/i', $u)){
                        $record['mobile_img'] = $u;
                    } else{
                        $record['board_img'] = $u;
                        $record['mobile_img'] = $u;
                        $record['closeup_img'] = $u;
                        $record['thumbnail_img'] = $u;
                        break;
                    }
                }

	        //$record['mobile_img'] = get_valid_pinterest_image_url($record['mobile_img']);
                $return[] = $record;
	        }
	    }
	    return $return;
	}

	/**
	 * Get the count of products which match statuses.
	 *
	 * @param $dbobj
	 * @param $filters
	 * @return int product cnt of the store
	 */

    public static function getProductCnt($dbobj, $filters = null, $ignore_quantity=false){
        $filter_str = '';
        $filter_arr = array();

        if(isset($filters['status'])){
            $filter_arr[] = " p.status in (".implode(",",$filters['status']) .")";
        }

        if(!empty($filters['query'])){
            $filter_arr[] = "(p.name like '%".$filters['query']."%' or p.description like '%".$filters['query']."%')";
        }

        if(isset($filters['category'])){
            $filter_arr[] = " c.category = '".$filters['category']."'";
        }

        if(!empty($filter_arr)){
            $filter_str = " where ".implode(' AND ', $filter_arr);
        }

        if(!$ignore_quantity) {
            $filter_str = empty($filter_str) ? ' where p.quantity>0' : $filter_str.' and p.quantity>0';
        }

        $sql = "select count(distinct p.id) as cnt from products p
                left join products_categories pc on p.id = pc.product_id
                left join categories c on c.id = pc.category_id";
        $filter_str;

        $cnt = 0;

        if($res = $dbobj->query($sql, $dbobj)){
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }
        return $cnt;
    }

    public static function getPictureIds($product_id, $dbobj) {
         $sql = "select p.id
            from products_pictures pp
            join pictures p on (pp.picture_id = p.id)
            where pp.product_id=$product_id
            and (p.type = 'board' or p.type = '')";
        $picture_ids = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $picture_ids[] = $record['id'];
            }
        }
        return $picture_ids;
    }

    public static function getDailyProductIds($dbobj){

        $return=array();
        $sql="select id,status from products where date(updated)=date(now())-1";
        if($res=$dbobj->query($sql)){
            while($record=$dbobj->fetch_assoc($res)){
                $return[]=$record;
            }
        }
        return $return;
    }
    public static function getPictureByProductId($product_id, $dbobj){
        $sql="select group_concat(pc.url) as url from products p
                join products_pictures pp on p.id=pp.product_id
                join pictures pc on pc.id=pp.picture_id
                where p.id=$product_id";
        $return = array();
        if($res = $dbobj->query($sql,$dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $urls = explode(',', $record['url']);
                foreach($urls as $u){
                    if(preg_match('/^.*(_t)\..*$/i', $u)){
                        $record['thumbnail_img'] = $u;
                    }
                    else if(preg_match('/^.*(_c)\..*$/i', $u)){
                        $record['closeup_img'] = $u;
                    }
                    else if(preg_match('/^.*(_b)\..*$/i', $u)){
                        $record['board_img'] = $u;
                    }
                    else if(preg_match('/^.*(_f)\..*$/i', $u)){
                        $record['mobile_img'] = $u;
                    }
                    else{
                        $record['board_img'] = $u;
                        $record['mobile_img'] = $u;
                        $record['closeup_img'] = $u;
                        $record['thumbnail_img'] = $u;
                        break;
                    }
                }
                $return = $record;
            }
        }
        return $return;
    }

    public static function deletePictures($product_id, $dbobj) {
        $sql = 'delete from products_pictures where product_id='.$product_id;
        $dbobj->query($sql);
    }

    public static function deleteCategory($product_id, $dbobj) {
        $sql = "delete from products_categories where product_id=$product_id";
        $dbobj->query($sql);
    }

    public static function getUnsyncProduct($dbobj, $limit=100) {

        $default_product_image = getSiteMerchantUrl(DEFAULT_PRODUCT_IMAGE);
        $sql = "select pp.product_id,
            group_concat(distinct concat_ws('~', p.pinterest_pin_id, pp.picture_id, p.url, p.source)) as pictures
            from products_pictures pp
            left join converted_pictures cp
            on (pp.picture_id = cp.picture_id)
            join pictures p
            on (pp.picture_id = p.id)
            join products pd
            on (pp.product_id = pd.id)
            where cp.picture_id is NULL and (p.type = 'board' or p.type = '') and pd.status != ".DELETED."
            and p.url!='".$default_product_image."'
            group by pp.product_id limit 0,$limit";

        $return = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)){

                $origin_picture_array = explode3(',', $record['pictures']);
                $origin_pictures = array();
                //p.pinterest_pin_id, pp.picture_id, p.url, , p.source
                foreach ($origin_picture_array as $pic) {
                    list($pinterest_pin_id, $picture_id, $picture_url, $picture_source) = explode3('~', $pic);
                    array_push($origin_pictures, array(
                        'pinterest_pin_id' => $pinterest_pin_id,
                        'picture_id' => $picture_id,
                        'picture_url' => $picture_url,
                        'picture_source' => $picture_source
                    ));
                }
                $record['pictures'] = $origin_pictures;
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function decreaseCount($store_id, $product_id, $cf, $qty) {
        global $dbconfig;
        $ck = CacheKey::q($dbconfig->store->name . "_" . $store_id . ".product?id=$product_id");
        $old_data = DAL::get($ck);
        $store_dbobj = DBObj::getStoreDBObjById($store_id);
        if(empty($cf)){
            $sql = "update products set quantity = (quantity - $qty) where id = $product_id";
            $store_dbobj->query($sql);
        } else {
            $sql = "update fields set quantity = (quantity - $qty)
                    where product_id = $product_id and status != 127 and name = '" . $store_dbobj->escape($cf) . "'";
            $store_dbobj->query($sql);

            $sql = "update products set quantity = (
                       select sum(quantity) from fields where product_id = $product_id and status != 127
                    ) where id = $product_id";
            $store_dbobj->query($sql);
        }
        DAL::s($ck, $old_data);
        DAL::delete(lck_store_tags($dbconfig->store->name . "_" . $store_id));
        DAL::delete(lck_store_categories($dbconfig->store->name . "_" . $store_id));
        $store_ck = CacheKey::q($dbconfig->account->name.'.store?id='.$store_id);
        $store_model = DAL::get($store_ck);
        $user_ck = CacheKey::q($dbconfig->account->name.'.user?id='.$store_model['user_id']);
        $user_model = DAL::get($user_ck);
        $merchant_email = $user_model['username'];

        $product_model = BaseModel::findCachedOne($ck);
        if($product_model['quantity'] == 0) {
            // send email
            global $shopinterest_config;
            $service = new EmailService();
            $service->setMethod('create_job');
            $service->setParams(array(
                'to' => $merchant_email,
                'from' => $shopinterest_config->support->email,
                'type' => MERCHANT_PRODUCT_SOLDOUT_NOTIFICATION,
                'data' => array(
                    'site_url' => getURL(),
                    'products' => array($product_model)
                ),
                'job_dbobj' => DBObj::getJobDBObj()
            ));
            $service->call();
        } else if(!empty($cf)) {
            $fields = $product_model['custom_fields'];
            foreach($fields as $field) {
                if($field['name'] === $cf && $field['quantity'] == 0) {
                    // send email
                    global $shopinterest_config;
                    $service = new EmailService();
                    $service->setMethod('create_job');
                    $service->setParams(array(
                        'to' => $merchant_email,
                        'from' => $shopinterest_config->support->email,
                        'type' => MERCHANT_PRODUCT_SOLDOUT_NOTIFICATION,
                        'data' => array(
                            'site_url' => getURL(),
                            'cf' => $cf,
                            'products' => array($product_model)
                        ),
                        'job_dbobj' => DBObj::getJobDBObj()
                    ));
                    $service->call();
                    break;
                }
            }
        }
    }

    public static function getProductQuantity($store_id, $product_id, $cf) {
        global $dbconfig;
        $store_dbobj = DBObj::getStoreDBObjById($store_id);
        if(!empty($cf)){
            $sql = "select quantity from fields
                    where product_id = $product_id
                    and name='" . $store_dbobj->escape($cf) . "'
                    and status != " . DELETED;
        } else {
            $sql = "select quantity from products
                    where id = $product_id and status != " . DELETED;
        }
        if($res = $store_dbobj->query($sql)){
            if($record = $store_dbobj->fetch_assoc($res)){
                return $record['quantity'];
            }
        }
        return 0;
    }

    public static function getProducts($dbobj, $page_num = 1, $search = '', $globle_category_id = 0, $status = 'active',
            $converted_type = CONVERTED192, $tag = '', $product_id = 0, $show = 'private') {

        if($show === 'private') {
            $num_show = CREATE_PRODUCT_NUM_PER_PAGE;
        } else {
            $num_show = STORE_NUM_PER_PAGE;
        }

        $limit = ($page_num>=1) ? ' limit '.$num_show*($page_num-1) . ','.$num_show : '';
        $globle_category_filter = empty($globle_category_id) ? '' : " and p.global_category_id = $globle_category_id";
        $search_filter = empty($search) ? '' : " and (p.name like '%$search%' or p.description like '%$search%')";
        $status_filter =
                $status === 'active' ?
                " (p.name!='' and p.price>0 and p.quantity>0 and  p.global_category_id>0)"
                :
                " (p.name='' or p.price='' or p.quantity = '' or p.global_category_id = 0)";

        $product_filter = empty($product_id) ? "" : " and p.id = $product_id";

        $left = 'left';
        $tag_filter = '';
        if(!empty($tag)) {
            $left = '';
            $tag_filter  = " and c.category = '$tag'";
        }

        $sql = "select p.id, p.id as product_id, p.status as product_status ,p.name as product_name, p.description as product_description, p.size as product_size,
                p.quantity as product_quantity, p.price as product_price, p.shipping as product_shipping, p.commission as product_commission,
                p.shipping as product_shipping, p.global_category_id as product_global_category_id, p.resell as product_resell, p.purchase_url as product_purchase_url,
                count(distinct pc.id) as origin_picture_cnt, group_concat(distinct concat_ws('~' ,pc.id, pc.url) order by pc.orderby, pc.id) as origin_pictures,
                group_concat(distinct concat_ws('~', cp.id, pc.id, cp.type, cp.url) order by pc.orderby, pc.id) as converted_pictures,
                group_concat(distinct concat_ws('~' ,c.category, c.description)) as categories,
                group_concat(distinct concat_ws('~' ,so.id, so.name)) as shipping_options,
                group_concat(distinct concat_ws('~' ,f.id, f.name, f.quantity)) as customer_fields
                from products p
                join products_pictures pp on (p.id = pp.product_id)
                join pictures pc on (pc.id = pp.picture_id)
                join converted_pictures cp on (pc.id = cp.picture_id)
                $left join products_categories pcs on (p.id = pcs.product_id)
                $left join categories c on (pcs.category_id = c.id)
                left join products_shipping_options pso on (p.id = pso.product_id)
                left join shipping_options so on (pso.shipping_option_id = so.id and so.status != ".DELETED.")
                left join products_fields pf on (p.id = pf.product_id)
                left join fields f on (pf.field_id = f.id and f.status != ".DELETED.")
                where $status_filter and p.status != ".DELETED."
                $product_filter
                and cp.type = $converted_type
                $tag_filter
                $globle_category_filter
                $search_filter
                group by p.id
                order by p.updated desc
                $limit";
        $return = array();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)){

                $record['product_price'] = round($record['product_price'], 2);
                $record['product_shipping'] = round($record['product_shipping'], 2);
                $record['product_description'] = strip_tags(html_entity_decode($record['product_description']));

                $origin_picture_array = explode3(',', $record['origin_pictures']);
                $origin_pictures = array();
                foreach ($origin_picture_array as $pic) {
                    list($id, $url) = explode3('~', $pic);
                    array_push($origin_pictures, array(
                        'id' => $id,
                        'url' => $url
                    ));
                }
                $record['origin_pictures'] = $origin_pictures;

                $convert_picture_array = explode3(',', $record['converted_pictures']);
                $convert_pictures = array();
                foreach ($convert_picture_array as $pic) {
                    if(empty($pic)) {
                        continue;
                    }

                    list($id, $picture_id, $type ,$url) = explode3('~', $pic);
                    array_push($convert_pictures, array(
                        'id' => $id,
                        'picture_id' => $picture_id,
                        'type' => $type,
                        'url' => $url
                    ));
                }
                $record['converted_pictures'] = $convert_pictures;

                $category_array = explode3(',', $record['categories']);
                $categories = array();
                foreach ($category_array as $category) {
                    list($cat, $desc) = explode3('~', $category);
                    if(empty($desc)) {
                        $desc = $cat;
                    }
                    array_push($categories, array(
                        'category' => $cat,
                        'description' => $desc
                    ));
                }
                $record['categories'] = $categories;

                $shipping_array = explode3(',', $record['shipping_options']);
                $shipping_options = array();
                foreach ($shipping_array as $shipping) {
                    if(empty($shipping)) {
                        continue;
                    }
                    list($id, $name) = explode3('~', $shipping);

                    array_push($shipping_options, array(
                        'id' => $id,
                        'name' => $name
                    ));
                }
                $record['shipping_options'] = $shipping_options;

                $field_array = explode3(',', $record['customer_fields']);
                $custom_fields = array();
                foreach ($field_array as $f) {
                    if(empty($f)) {
                        continue;
                    }
                    list($id, $name, $q) = explode3('~', $f);
                    array_push($custom_fields, array(
                        'id' => $id,
                        'name' => $name,
                        'quantity' => $q,
                    ));
                }
                $record['custom_fields'] = $custom_fields;

                $return[] = $record;
            }
        }

        return $return;
    }

    public static function getProductsCnt(
        $dbobj, $search = '', $globle_category_id = 0, $status = 'active', $converted_type = 192, $tag = ''
    ) {

        $globle_category_filter = empty($globle_category_id) ? '' : " and p.global_category_id = $globle_category_id";
        $search_filter = empty($search) ? '' : " and (p.name like '%$search%' or p.description like '%$search%')";
        $status_filter =
                $status === 'active' ?
                " (p.name!='' and p.price!='' and p.quantity != '' and  p.global_category_id !=0)"
                :
                " (p.name='' or p.price='' or p.quantity = '' or p.global_category_id =0)";

        $left = 'left';
        $tag_filter = '';
        if(!empty($tag)) {
            $left = '';
            $tag_filter  = " and c.category = '$tag'";
        }

        $sql = "select count(distinct p.id) as cnt
                from products p
                join products_pictures pp on (p.id = pp.product_id)
                join pictures pc on (pc.id = pp.picture_id)
                join converted_pictures cp on (pc.id = cp.picture_id)
                $left join products_categories pcs on (p.id = pcs.product_id)
                $left join categories c on (pcs.category_id = c.id)
                where $status_filter and p.status != 127
                and cp.type= $converted_type
                $tag_filter
                $globle_category_filter
                $search_filter";
        $cnt = 0;

        if($res = $dbobj->query($sql, $dbobj)){
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }
        return $cnt;
    }

    public static function isConverted($picture_id, $dbobj) {
        $sql = 'select id from converted_pictures where picture_id ='.$picture_id;
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return true;
            }
        }
        return false;
    }

    public static function deleteProductCategory($product_id, $category, $dbobj) {

        $dbname = $dbobj->getDBName();
        $product_ck = get_product_ck($dbname, $product_id);
        $old_data = DAL::get($product_ck);

        $sql = "delete pc.* from products_categories pc
            right join products p on (pc.product_id = p.id)
            right join categories c on (pc.category_id = c.id)
            where c.category = '$category' and p.id = $product_id";
        $dbobj->query($sql, $dbobj);

        DAL::s($product_ck, $old_data);
    }

    public static function deleteProductPicture($product_id, $picture_id, $dbobj) {

        $dbname = $dbobj->getDBName();
        $product_ck = get_product_ck($dbname, $product_id);
        $old_data = DAL::get($product_ck);

        $sql = "select count(p.id) as cnt
            from products_pictures pp join pictures p on (pp.picture_id = p.id)
            where pp.product_id = $product_id
            and p.status!=".DELETED."";
        $cnt = 0;

        if($res = $dbobj->query($sql, $dbobj)){
            if($record = $dbobj->fetch_assoc($res)) {
                $cnt = $record['cnt'];
            }
        }

        if($cnt > 1) {
            $sql1 = "update pictures set status = ".DELETED." where id = $picture_id";
            $dbobj->query($sql1, $dbobj);
            $sql2 = "delete from products_pictures where product_id = $product_id and picture_id = $picture_id";
            $dbobj->query($sql2, $dbobj);
        }

        DAL::s($product_ck, $old_data);
    }

    public static function getPictures($product_id, $dbobj) {
        $sql = "select p.id as picture_id, p.type as picture_type, p.url as picture_url,
            group_concat(distinct concat_ws('~', c.id, c.type, c.url)) as converted_pictures
            from products_pictures pp
            join pictures p on (p.id = pp.picture_id)
            join converted_pictures c on (c.picture_id = p.id)
            where pp.product_id = $product_id
            group by p.id";

        $return = array();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {

                $convert_picture_array = explode3(',', $record['converted_pictures']);
                $convert_pictures = array();
                foreach ($convert_picture_array as $pic) {
                    if(empty($pic)) {
                        continue;
                    }
                    list($id, $type ,$url) = explode3('~', $pic);
                    array_push($convert_pictures, array(
                        'id' => $id,
                        'type' => $type,
                        'url' => $url
                    ));
                }

                array_push($convert_pictures, array(
                    'id' => $record['picture_id'],
                    'type' => 'original',
                    'url' => $record['picture_url']
                ));
                $record['pictures'] = $convert_pictures;

                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getProductPictures($product_id, $dbobj) {
        $sql = "select p.pinterest_pin_id as pinterest_pin_id,
            p.id as picture_id, p.url as picture_url, p.source as picture_source, p.original_url
            from products_pictures pp
            join pictures p on (p.id = pp.picture_id)
            where pp.product_id = $product_id and (p.type = 'board' or p.type = '')";

        $return = array();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getAllProductIds($dbobj) {
        $sql = "select id from products where status!=".DELETED;

        $return = array();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record['id'];
            }
        }
        return $return;
    }

    public static function getProductConvertedPictures($dbobj,$product_id, $converted_type = array(CONVERTED192, CONVERTED550)) {

        $sql = "select p.*,
                group_concat(distinct concat_ws('~', pc.id, cp.type, cp.url) order by pc.orderby, pc.id) as pictures
                from products p
                join products_pictures pp on (p.id = pp.product_id)
                join pictures pc on (pc.id = pp.picture_id)
                join converted_pictures cp on (pc.id = cp.picture_id)
                where cp.type in (".implode(",",$converted_type) .") and
                p.id = $product_id and p.name!='' and p.price!='' and p.quantity != '' and p.status != ".DELETED;

        $return = array();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {

                $record['price'] = round($record['price'], 2);
                $record['shipping'] = round($record['shipping'], 2);

                $convert_picture_array = explode3(',', $record['pictures']);
                $pictures = array();
                foreach ($convert_picture_array as $pic) {
                    if(empty($pic)) {
                        continue;
                    }
                    list($picture_id, $type ,$url) = explode3('~', $pic);

                    if(!isset($pictures[$picture_id])) {
                        $pictures[$picture_id] = array();
                    }

                    $pictures[$picture_id][$type] = $url;
                }

                $record['pictures'] = $pictures;

                $return = $record;
            }
        }

        return $return;
    }

    public static function isActiveProduct($dbobj, $product_id) {

        $filter = " and global_category_id !=0";

        $sql = "select * from products where id = $product_id and status != ".DELETED." and name!='' and price>0 and quantity != 0 $filter";

        $is_active = false;

        if($res = $dbobj->query($sql)){
            if($record = $dbobj->fetch_assoc($res)) {
                $is_active = true;
            }
        }
        return $is_active;
    }

    public static function getConvertedPicturesByPictureIds($picture_ids, $dbobj) {
        if(empty($picture_ids)) return array();
        $sql = "select * from converted_pictures where picture_id in (".$picture_ids.") order by FIELD (picture_id, ".$picture_ids.")";

        $return = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $type = $record['type'];
                $url = $record['url'];
                $return[$type][$record['picture_id']] = $url;
            }
        }

        return $return;
    }

    public static function getPicturesByProductId($pid, $dbobj) {
        $sql = "select pic.id, name, url from pictures pic
                join products_pictures pp on( pp.picture_id = pic.id)
                where pp.product_id = " . $pid . " order by pic.orderby";

        $return = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getCachedObject($params, $dbobj) {
        global $dbconfig;
        $store_dbname = $dbconfig->store->name;
        $account_dbname = $dbconfig->account->name;
        $product_id = $params['id'];
        $ck = $params['_cachekey'];

        $sql = "select
                p.*,
                count(distinct pc.id) as picture_count,
                group_concat(distinct c.description) as tags
                from
                products p
                join products_pictures pp on (p.id = pp.product_id)
                join pictures pc on (pc.id = pp.picture_id)
                left join products_categories pcs on (p.id = pcs.product_id)
                left join categories c on (pcs.category_id = c.id  and c.status!=".DELETED.")
                where
                p.id = $product_id and pc.status!=".DELETED;

        $return = array();

        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {

                if(empty($record['id'])) {
                    return $return;
                }
                $store_key_reg = "#^".$store_dbname."_(\d+)(.*)$#";
                $match = array();
                preg_match($store_key_reg, $ck->cacheKey(), $match);
                $store_id = $match[1];

                $store_ck = CacheKey::q($account_dbname.'.store?id='.$store_id);
                $store = BaseModel::findCachedOne($store_ck);

                $store_status = $store['status'];
                $store_subdomain = $store['subdomain'];
                $store_name = $store['name'];
                $store_description = $store['description'];
                $store_logo = $store['converted_logo'];
                $store_country = $store['country'];
                $store_currency = $store['currency'];
                $store_url = getStoreUrl($store_subdomain);

                $category_ck = CacheKey::q($account_dbname.'.global_category?id='.$record['global_category_id']);
                $category = BaseModel::findCachedOne($category_ck);
                $category_path = default2String($category['path']);
                $return = $record;

                // for datatable: do this in BaseModel::findCachedOne now
                // $return['row_id'] = $ck->cacheKey();

                // get root_category_id
                $return['root_category_id'] = !empty($category['parent_id']) ?
                    $category['parent_id'] : (isset($category['id']) ? $category['id'] : 0);

                $pictures = self::getPicturesByProductId($return['id'], $dbobj);
                $return['pictures'] = empty($pictures) ? '' : json_encode($pictures);
                $return['product_url'] = get_product_item_url($store_subdomain, $record['id']);

                $return['store_id'] = $store_id;
                $return['store_status'] = $store_status;
                $return['store_subdomain'] = $store_subdomain;
                $return['store_name'] = $store_name;
                $return['store_description'] = $store_description;
                $return['store_logo'] = $store_logo;
                $return['store_currency'] = $store_currency;
                $return['store_url'] = $store_url;

                $return['category_path'] = $category_path;
                $cat_levels = preg_split("/\s*>\s*/", $category_path);
                foreach($cat_levels as $i => $l) {
                    $return["category_l" . ($i+1)] = preg_replace("/&/", "__and__", trim($l));
                }

                $cfs = ProductsMapper::getCustomFields($dbobj, $return['id']);
                if(!empty($cfs)){
                    $return['custom_fields'] = json_encode($cfs);
                }

                $sops = ProductsMapper::getShippingOptions($dbobj, $return['id']);
                $return['shipping_options'] = json_encode($sops);

                $sdests = ProductsMapper::getShippingDestinations($dbobj, $return['id']);
                $return['shipping_destinations'] = json_encode($sdests);
            }
        }
        return $return;
    }

    public static function getCachedObjectList($params, $dbobj) {
        $ck = $params['_cachekey'];

        $dbname = $ck->getDBName();
        $order = $ck->getOrderInfo();
        $order = $order['orderby'];
        $label = $ck->getLabel();

        $keys = array();
        //public static function getProducts($dbobj, $page_num = 1, $search = '', $globle_category_id = 0, $status = 'active',
        //    $converted_type = CONVERTED192, $tag = '', $product_id = 0, $show = 'private') {}
        if(isset($params['tag'])){
            $products = self::getActiveProductsByTag($dbobj, $params['tag']);
        } else if(isset($params['category_l1'])){
            $cat_l1 = $params['category_l1'];
            $cat_l2 = isset($params['category_l2'])? $params['category_l2'] : NULL;
            $products = self::getActiveProductsByCategoty($dbobj, $cat_l1, $cat_l2);
        } else if ($label === 'INACTIVE') {
            $products = self::getInActiveProductIdAndScore($dbobj);
        } else if($label === 'RESELL') {
            $products = self::getResellProductIdAndScore($dbobj);
        } else { // get active products
            $products = self::getActiveProductIdAndScore($dbobj);
        }

        if(count($order) != 1) { // no order, no score
            foreach($products as $product) {
                $key = $dbname.".product?id=".$product['id'];
                $keys[] = $key;
            }
        } else {
            $order_key = $order[0];
            foreach($products as $product) {
                $score = 0;
                if($order_key == 'updated'){
                    $score = strtotime2($product['updated']);
                } else if($order_key == 'price'){
                    $score = $product['price'];
                }
                $key = $dbname.".product?id=".$product['id'];
                $keys[$key] = $score;
            }
        }
        return $keys;
    }

    public static function getResellProductIdAndScore($dbobj){
        $filter = " and resell!=0";

        $sql = "select
                p.id, p.updated, p.price
                from
                products p
                join products_pictures pp on (p.id = pp.product_id)
                where
                p.status!=".DELETED.$filter." group by p.id order by p.updated desc";

        $return = array();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }

        return $return;
    }

    public static function getActiveProductsByTag($dbobj, $tag) {
        $deleted = DELETED;
        $sql = "select p.id, p.price, p.updated
                from
                products p
                join products_categories pc on (p.id=pc.product_id)
                join categories c on (c.id=pc.category_id)
                where
                p.name!='' and p.price>0 and p.quantity>0 and p.status != $deleted and
                p.global_category_id !=0
                and c.category = '" . $dbobj->escape($tag) . "'";
        $return = array();

        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getActiveProductsByCategoty($dbobj, $cat_l1, $cat_l2) {
        $cat_path = preg_replace("/__and__/", "&", $cat_l1);
        if(!empty($cat_l2)){
            $cat_path = $cat_path . " > " . preg_replace("/__and__/", "&", $cat_l2);
        }
        $account_dbobj = DBObj::getAccountDBObj();
        $sql = "select id from global_categories where path = '" . $account_dbobj->escape($cat_path) . "' limit 1";
        $cat_ids = array();
        if($res = $account_dbobj->query($sql)){
            if($record = $account_dbobj->fetch_assoc($res)) {
                $cat_ids[] = $record['id'];
                if(empty($cat_l2)){ // get children cats
                    $sql = "select id from global_categories where parent_id = ". $record['id'];
                    if($res = $account_dbobj->query($sql)){
                        while($record = $account_dbobj->fetch_assoc($res)) {
                            $cat_ids[] = $record['id'];
                        }
                    }
                }
            }
        }

        $filter = " and p.name!='' and p.price>0 and p.quantity >0";
        $filter .= " and global_category_id in (" . implode(", ", $cat_ids) . ")";
        $sql = "select
                p.id, p.updated, p.price
                from
                products p
                join products_pictures pp on (p.id = pp.product_id)
                where
                p.status!=".DELETED.$filter." group by p.id order by p.updated desc";

        $return = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getActiveProductIdAndScore($dbobj) {
        $filter = " and p.name!='' and p.price>0 and p.quantity>0 and p.global_category_id !=0";

        $sql = "select
                p.id, p.updated, p.price
                from
                products p
                join products_pictures pp on (p.id = pp.product_id)
                where
                p.status!=".DELETED.$filter." group by p.id order by p.updated desc";


        $return = array();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }

        return $return;
    }

    public static function getActiveFeaturedProductsCacheKeys($featured, $global_category_id, $dbobj) {
        if($featured == SLIDER_FEATURED || $featured == AD_FEATURED) {
            $filter = " and p.name!='' and p.price>0 and p.quantity>0 and p.global_category_id !=0 and featured=$featured";
        } else {
            $child_category_ids = GlobalCategoriesMapper::getChildCategoryIds(DBObj::getAccountDBObj(), $global_category_id);
            $child_category_ids[] = $global_category_id;
            $child_category_ids_string = join(',', $child_category_ids);
            $filter = " and p.name!='' and p.price!='' and p.quantity != '' and p.global_category_id in ($child_category_ids_string) and featured=$featured";
        }


        $sql = "select
                p.id, p.featured_score
                from
                products p
                join products_pictures pp on (p.id = pp.product_id)
                where
                p.status!=".DELETED.$filter." group by p.id order by p.updated desc";

        $return = array();
        $dbname =$dbobj->getDBName();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[$dbname.'.product?id='.$record['id']] = $record['featured_score'];
            }
        }

        return $return;
    }

    public static function getInActiveProductIdAndScore($dbobj) {
        $filter = " and (p.name='' or p.price=0 or p.quantity=0 or p.global_category_id=0)";

        $sql = "select
                p.id, p.updated
                from
                products p
                join products_pictures pp on (p.id = pp.product_id)
                join converted_pictures cp on (cp.picture_id = pp.picture_id)
                where
                p.status!=".DELETED.$filter." group by p.id order by p.updated desc";


        $return = array();

        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }

        return $return;
    }

    public static function getCustomFields($dbobj, $product_id){
        $sql = "select f.*
                from fields f
                where f.product_id = $product_id and f.status != " . DELETED;

        $return = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }

        return $return;
    }

    public static function getShippingOptions($dbobj, $product_id){
        $sql = "select so.*
                from shipping_options so
                join products_shipping_options pso on (pso.shipping_option_id = so.id)
                where pso.product_id = $product_id and so.status != " . DELETED;

        $return = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }

        return $return;
    }

    public static function getShippingDestinations($dbobj, $product_id){
        $sql = "select so.*, sd.name as dest_name, sd.base, sd.additional
                from shipping_options so
                left join products_shipping_options pso on (pso.shipping_option_id = so.id or so.name = 'Standard')
                join shipping_destinations sd on (sd.shipping_option_id = so.id)
                where (pso.product_id = $product_id or so.name = 'Standard') and so.status != " . DELETED . "
                group by so.name, sd.name";

        $return = array();
        if($res = $dbobj->query($sql)){
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }

        return $return;
    }

}
