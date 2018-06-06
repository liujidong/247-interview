<?php
class ProductPhotosService extends BaseService {

    private function get_multi_photo($picture_url, $skip_upload_original_photo, $convert_type = array(ORIGINAL, CONVERTED45, CONVERTED70, CONVERTED192,
        CONVERTED236, CONVERTED550, CONVERTED736)) {

        $filepicker = new Filepicker();
        $return = array();
        
        if(validate($picture_url, 'pinterest_image_url')) {
            // get multi image from pinterest 
            $response = get_image_url_from_exist_image($picture_url);
            $original_picture_url = '';
            foreach ($response as $key => $value) {
                switch ($key) {
                    case 'image_45':
                        if(in_array(CONVERTED45, $convert_type)) {
                            $return[0]['image_url'] = $value;
                            $return[0]['image_type'] = CONVERTED45;                              
                        }
                        break;
                    case 'image_70':
                        if(in_array(CONVERTED70, $convert_type)) {
                            $return[1]['image_url'] = $value;
                            $return[1]['image_type'] = CONVERTED70; 
                        }
                        break;
                    case 'image_192':
                        if(in_array(CONVERTED192, $convert_type)) {
                            $return[2]['image_url'] = $value;
                            $return[2]['image_type'] = CONVERTED192;   
                        }
                        break;
                    case 'image_236':
                        if(in_array(CONVERTED236, $convert_type)) {
                            $return[3]['image_url'] = $value;
                            $return[3]['image_type'] = CONVERTED236;   
                        }
                        break;
                    case 'image_550':
                        if(in_array(CONVERTED550, $convert_type)) {
                            $return[4]['image_url'] = $value;
                            $return[4]['image_type'] = CONVERTED550;   
                        }
                        break;
                    case 'image_736':
                        $original_picture_url = $value;
                        if(in_array(CONVERTED736, $convert_type)) {
                            $return[5]['image_url'] = $value;
                            $return[5]['image_type'] = CONVERTED736;  
                        }
                        break;                
                    default:
                        break;
                }
            }
            if(in_array(ORIGINAL, $convert_type) && $skip_upload_original_photo === false) {
                array_push($return, array(
                    'image_url' => $original_picture_url,
                    'image_type' => ORIGINAL
                ));                   
            }
    
        } else {
            // use FilePicker Api to convert image 
            $image_resource = $filepicker->store_image($picture_url);
            //$image_type = array(CONVERTED45, CONVERTED70, CONVERTED192, CONVERTED236, CONVERTED550, CONVERTED736);  
            $image_type = $convert_type;
            foreach ($image_type as $i => $type) {
                
                if($type === ORIGINAL && $skip_upload_original_photo === false) {
                    array_push($return, array(
                        'image_url' => $image_resource['url'],
                        'image_type' => ORIGINAL
                    )); 
                } else {
                    $options = array(
                        'w' => $type, 
                        'format' => 'jpg', 
                        'quality' => '100',
                        'fit' => 'max'
                    ); 
                    if($type === CONVERTED45) {
                        $options['h'] = $type;
                        $options['fit'] = 'crop';
                    }             
                    $converted_image_url = $filepicker->convert_image($image_resource, $options);    
                    $return[$i]['image_url'] = $converted_image_url;
                    $return[$i]['image_type'] = $type;                      
                }              
            }
        }
        return array_values($return);   
    }

    // input : store_id, product_id, store_dbobj
    // options : account_dbobj, refresh, skip_upload_original_photo
    public function create_product_photo() {
        $store_id = $this->params['store_id'];        
        $product_id = $this->params['product_id'];
        $store_dbobj = $this->params['store_dbobj'];
        $account_dbobj = isset($this->params['account_dbobj']) ? $this->params['account_dbobj'] : null;
        $refresh = isset($this->params['refresh'])?$this->params['refresh']:false;
        $skip_upload_original_photo = isset($this->params['skip_upload_original_photo'])?$this->params['skip_upload_original_photo']:false;
        
        $picture_ids = ProductsMapper::getPictureIds($product_id, $store_dbobj);
        foreach ($picture_ids as $picture_id) {
            $uniqid = uniqid();
            $picture_obj = new Picture($store_dbobj);
            $picture_obj->findOne('id='.$picture_id);
            $pinterest_pin_id = $picture_obj->getPinterestPinId();
            $picture_url = $picture_obj->getUrl();
            
            if(!empty($pinterest_pin_id) && !empty($account_dbobj)) {
                if(!empty2(get_image_url_from_pin_id($account_dbobj, $pinterest_pin_id))) {
                    $picture_url = get_image_url_from_pin_id($account_dbobj, $pinterest_pin_id);
                    echo "will use pincture url from pinterest to convert: $picture_url\n";                    
                }
            }
            
            if($refresh) {
                ConvertedPicturesMapper::delete_converted_picture($picture_id, $store_dbobj);
            }
            // get the type which need to be converted
            $convert_type = ConvertedPicturesMapper::get_convert_type($picture_id, $store_dbobj);
         
            if(empty($convert_type) || !checkRemoteFile($picture_url)) {
                continue;
            }
            
            // only get the unconvert type
            $muti_pictures = $this->get_multi_photo($picture_url, $skip_upload_original_photo , $convert_type);
            // upload to s3 use image_upload and save retured url, save assocaita between product and new_picture    
            array_walk($muti_pictures, function (&$array, $key, $user_data) {
                list($store_id, $uniqid) = $user_data;
                $array['dst'] = get_product_image_upload_dst($store_id, $uniqid, $array['image_type']);
                $array['src'] = $array['image_url'];
            }, array($store_id, $uniqid));
            foreach ($returned_pic_urls = @upload_image2($muti_pictures) as $type => $returned_pic_url) {
                                
                if($type === ORIGINAL) {
                    
                    if(!checkRemoteFile($returned_pic_url)) {
                        break;
                    }
                    $picture_obj->setUrl($returned_pic_url);
                    $picture_obj->setPicUploadTime(get_current_datetime());
                    $picture_obj->save();  
                    
                    Log::write(INFO, "origin picture id is: {$picture_obj->getId()}");                    
                } else {
                    $converted_picture_obj = new ConvertedPicture($store_dbobj);
                    $converted_picture_obj->setPictureId($picture_obj->getId());
                    $converted_picture_obj->setType($type);
                    $converted_picture_obj->setUrl($returned_pic_url);                    
                    $converted_picture_obj->save();  
                    
                    Log::write(INFO, "converted_picture id: {$converted_picture_obj->getId()}");                          
                }
            }
            $this->response[] = $returned_pic_urls;
        }
    }

    public function upload_original_photo() {
        
        $store_id = $this->params['store_id'];
        $picture_id  = $this->params['picture_id'];
        $store_dbobj = $this->params['store_dbobj'];
        $salt = isset($this->params['salt']) ? $this->params['salt'] : uniqid();

        $picture_obj = new Picture($store_dbobj, $picture_id);
        $original_url = $picture_obj->getUrl();
        $dst = get_product_image_upload_dst($store_id, $salt, ORIGINAL);
        
        if($dst_url = upload_image($dst, $original_url)) {
            $picture_obj->setUrl($dst_url);
            $picture_obj->setPicUploadTime(get_current_datetime());
            $picture_obj->save();     
            $this->response = $dst_url;            
        }                

    }
    
}


 