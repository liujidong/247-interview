<?php

class GlobalProductsService extends BaseService {

    public function __construct() {
        parent::__construct();
    }

    public static function sync($store_id, $product_id, $product=array()) {

        global $dbconfig;
        
        $account_dbname = $dbconfig->account->name; 
        $store_dbname = getStoreDBName($store_id);
        $account_dbobj = DBObj::getAccountDBObj();
        
        // validate if the store is active
        $store = BaseModel::findCachedOne($account_dbname.'.store?id='.$store_id);
        if($store['status'] != ACTIVATED) {
            $sql = "delete from global_products where store_id=$store_id and product_id=" . $product_id;
            $account_dbobj->query($sql);
            return false;
        }
        
        if(empty($product)) {
            $product = BaseModel::findCachedOne($store_dbname.'.product?id='.$product_id);
        }
        if(empty($product)){
            return false;
        }
        // validate if the product is active
        global $cond_active_product;
        if(!$cond_active_product->test($product)) {
            $sql = "delete from global_products where store_id=$store_id and product_id=" . $product['id'];
            $account_dbobj->query($sql);
            return false;
        }
        // we only sync the active products in the active store
        $global_category = BaseModel::findCachedOne($account_dbname.'.global_category?id='.$product['global_category_id']);
            
        // fill the global_products
        $global_product = new GlobalProduct($account_dbobj);
        $global_product->setStoreId($store_id);
        $global_product->setProductId($product['id']);
        $global_product->setProductStatus($product['status']);        
        $global_product->setProductName($product['name']);
        $global_product->setProductDescription($product['description']);
        $global_product->setProductQuantity($product['quantity']);
        $global_product->setProductPrice($product['price']);
        $global_product->setProductCommission($product['commission']);
        $global_product->setProductGlobalCategoryId($product['global_category_id']);
        $global_product->setProductGlobalCategoryPath($global_category['path']);
        $global_product->setProductResell($product['resell']);
        $global_product->setProductPurchaseUrl($product['purchase_url']);
        $global_product->setProductFeatured($product['featured']);
        $global_product->setProductFeaturedScore($product['featured_score']);
        $global_product->setProductCreated($product['created']);
        $global_product->setProductUpdated($product['updated']);
        $global_product->setProductPictures(json_encode($product['pictures']));
        $global_product->setProductPictureCount($product['picture_count']);
        $global_product->setProductTags($product['tags']);
        $global_product->save();

        
    }
    
    
}
