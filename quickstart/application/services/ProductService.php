<?php

class ProductService extends BaseService {
    
    // input:
    // [post_data]
    // product_description
    // product_shipping
    // product_quantity
    // product_name
    // product_price
    // pic_id (the picture needs be created already)
    // product_id (optional, if set, then it is an update to the product info)
    // product_category (optional, if set, then create an assoc bw the prod and the category, also the category needs be created already)
    // ext_ref_id
    // ext_ref_url
    // brand
    // misc
    // [store_dbobj]
    public function createProduct(){
        
        $store_id = empty($this->params['store_id'])?0:$this->params['store_id'];
        $store_optin_salesnetwork = empty($this->params['store_optin_salesnetwork'])?0:$this->params['store_optin_salesnetwork'];       
        $store_dbobj = $this->params['store_dbobj'];
        $post_data = $this->params['post_data'];
        
        $product_description = empty($post_data['product_description'])?'':$post_data['product_description'];
        $product_shipping = empty($post_data['product_shipping'])?0:$post_data['product_shipping'];
        $product_quantity = empty($post_data['product_quantity'])?0:$post_data['product_quantity'];
        $product_commission = empty($post_data['product_commission'])?0:$post_data['product_commission'];
        // newly added fields
        // alter table products add column `ext_ref_id` varchar(50) not null default '' after `pinterest_pin_id`;
        // alter table products add column `ext_ref_url` varchar(255) not null default '' after `ext_ref_id`;
        // alter table products add column `brand` varchar(255) not null default '' after `ext_ref_url`;
        // alter table products add column `misc` text not null default '' after `brand`;
        $product_ext_ref_id = empty($post_data['ext_ref_id'])?'':$post_data['ext_ref_id'];
        $product_ext_ref_url = empty($post_data['ext_ref_url'])?'':$post_data['ext_ref_url'];
        $product_brand = empty($post_data['brand'])?'':$post_data['brand'];
        $product_misc = empty($post_data['misc'])?'':$post_data['misc'];

        if(empty($post_data['product_name'])) {
            $this->errnos[PRODUCT_NAME_ERROR] = 1;
        } else {
            $product_name = strip_tags($post_data['product_name']) ;
        }
        
        $product_price = floatval2($post_data['product_price']);
        if(empty($product_price)) {
            $this->errnos[PRODUCT_PRICE_ERROR] = 1;
        }
        if(!is_numeric($product_shipping)) {
            $this->errnos[PRODUCT_SHIPPING_ERROR] = 1;
        } else {
            $product_shipping = floatval2($product_shipping);
        }

        if(($store_optin_salesnetwork === ACTIVATED) && (!is_numeric($product_commission) || ($product_commission*$product_price/100<1))) {
            $this->errnos[PRODUCT_COMMISSION_ERROR] = 1;
        }        
        
        $product_quantity = intval2($product_quantity);
        $product_commission = intval2($product_commission);       
        /*
        if(empty($product_quantity)){
            $this->errnos['PRODUCT_QUANTITY_ERROR'] = 1;
        }
        */
        
        if(empty($post_data['pic_id']) && empty($post_data['product_id'])){
            $this->errnos[PRODUCT_PIC_ERROR] = 1;
        }else{
            $product_pic_ids = empty($post_data['pic_id'])?0:$post_data['pic_id'] ;
        }
        
        if(empty($this->errnos)) {

            if(empty($product_quantity)) {
                $this->errnos[PRODUCT_QUANTITY_ERROR] = 1; 
            }
           
            $product = new Product($store_dbobj);
            if(isset($post_data['product_id'])){
                $product->findOne('id='.$post_data['product_id']); //this is used for update
            } else {
                $product->setPinterestPinId(uniqid()); // temporarily fix pinterest_pin_id unique key issue
            }
            
            $product->setName($product_name);
            $product->setDescription($product_description);
            $product->setQuantity($product_quantity);
            $product->setShipping($product_shipping);
            $product->setPrice($product_price);
            $product->setExtRefId($product_ext_ref_id);
            $product->setExtRefUrl($product_ext_ref_url);
            $product->setBrand($product_brand);
            $product->setMisc($product_misc);
            $product->setCommission($product_commission);
            $product->save();
            $product_id=$product->getId();
            if(!empty($product_pic_ids) && is_array($product_pic_ids)) {
                // we create picture on the product create page, not on the product view page
                $picture = new Picture($store_dbobj);
                ProductsMapper::deletePictures($product_id, $store_dbobj);
                foreach($product_pic_ids as $product_pic_id){
                    $picture->findOne("id=".$product_pic_id);
                    BaseMapper::saveAssociation($product, $picture, $store_dbobj);
                }
                $service = new ProductPhotosService();
                $service->setMethod('create_product_photo');
                $service->setParams(array(
                    'store_id' => $store_id,
                    'product_id' => $product_id,
                    'picture_ids' => $product_pic_ids,
                    'store_dbobj' => $store_dbobj
                ));
                $service->call();                
            }
            
            // $post_data['product_category'] is the category id
            if(!empty($post_data['product_category']) && $post_data['product_category'] !== 'create_category'){

                $category_obj = new Category($store_dbobj);
                $category_obj->setId($post_data['product_category']);

                ProductsMapper::deleteCategory($product_id, $store_dbobj);
                BaseMapper::saveAssociation($product, $category_obj, $store_dbobj);
            }
        }
        if(!empty($this->errnos)) {
            $this->status = 1;           
        }
    }
    
    public function deleteProduct(){
        $post_data = $this->params['post_data'];
        
        $store_dbobj = $this->params['store_dbobj'];
        
    }
    
    public function getProduct(){
        $post_data = $this->params['post_data'];
        $store_dbobj = $this->params['store_dbobj'];
        $prod_id = $post_data['prod_id'];
        $products = ProductsMapper::getProductById($prod_id, $store_dbobj);
       
        if(empty($products)) {
            $this->status = 1;
            $this->errnos[PRODUCT_CANT_FIND_ERROR] = 1;
            return;
        } else {
            $this->response = $products[0];            
        }

    }
    
    public function get_product_from_csv() {
        $file_path = $this->params['file_path'];
        
        $fields_mapper = array(
            'TITLE' => 'name',
            'DESCRIPTION' => 'description',
            'PRICE' => 'price',
            'QUANTITY' => 'quantity',
            'IMAGE1' => 'pictures',
            'IMAGE2' => 'pictures',
            'IMAGE3' => 'pictures',
            'IMAGE4' => 'pictures',
            'IMAGE5' => 'pictures',            
            'TAGS' => 'categories'
        );        
        
        $service = new CSVService2();
        $service->setMethod('parse');
        $service->setParams(array(
            'file_path' =>  $file_path
        ));
        $service->call();
        $csv_details = $service->getResponse();

        $products = array();

        foreach ($csv_details as $i => $csv_detail) {
            
            foreach ($csv_detail as $k => $data) {
                
                if(empty($data)) {
                    continue;
                }
                
                if(in_array($k, array_keys($fields_mapper))) {

                    if($fields_mapper[$k] === 'pictures') {
                        $products[$i]['pictures'][] = array(
                            'url' => $data,
                            'source' => 'csvimport'
                        );
                    } else if ($fields_mapper[$k] === 'categories') {
                        
                        $categorys = explode(',', $data);
                        
                        foreach ($categorys as $category) {
                            $products[$i]['categories'][] = array(
                                'category' => $category
                            );                               
                        }
                     
                    } else {
                        $products[$i][$fields_mapper[$k]] = $data;                        
                    }                   
                }
            }

            if($i ===0 && empty($products)) {
                $this->errnos[CSV_FILE_HEADER_ERROR] = 1;
                $this->status = 0;
                return false;                
            }
        }

        $this->response = $products;
    }
    
}