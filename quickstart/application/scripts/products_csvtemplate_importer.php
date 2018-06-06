<?php

// argv[1]: csv file path
// argv[2]: merchant account email
if(empty($argv[1])) {
    die('missing the csv file path: php products_csvtemplate_importer.php <csv file path> <merchant account email>');
}

if(empty($argv[2])) {
    die('missing the merchant account email: php products_csvtemplate_importer.php <csv file path> <merchant account email>');
}

$csv_file_path = $argv[1];
$merchant_username = $argv[2];
$delimiter = ',';

require_once('includes.php');

$products = parse_csv($csv_file_path, $delimiter);

$merchant = new Merchant($account_dbobj);
$merchant->findOne("username='".$account_dbobj->escape($merchant_username)."'");
$merchant_id = $merchant->getId();
if(empty($merchant_id)) {
    die("There is no such merchant $merchant_username\n");
}
$store_id = MerchantsMapper::getStoreId($merchant_id, $account_dbobj);
if(empty($store_id)) {
    die("There is no such store $store_id\n");
}
$store = new Store($account_dbobj);
$store->findOne('id='.$store_id);
$store_dbobj = DbObj::getStoreDBObj($store->getHost(), $store_id);

//[external_ref_id] => 1002
//[name] => Spicy Lamb Chops with Creamed Butter Beans
//[description] => Spicy Lamb Chops with Creamed Butter Beans
//[quantity] => 14
//[price] => 60
//[shipping] => 
//[ext_ref_url] => http://redbookmag.staging.shopinterest.co:8083/products/item?id=6
//[brand] => redbook
//[category] => food
//[picture_urls] => http://media-cache-ec2.pinterest.com/upload/277252920780736998_0es7JGRB_f.jpg
//[misc] => 
ddd($products);
foreach($products as $product) {
    
    // basic validation
    if(empty($product['ext_ref_id']) ||
            empty($product['name']) || 
            empty($product['picture_urls'])) {
        ddd('ext_ref_id or product name or picture urls not valid, continue to next product');
        continue;
    }
  
    // check the product exists or not first
    $product_obj = new Product($store_dbobj);
    $product_obj->findOne("ext_ref_id='".$store_dbobj->escape($product['ext_ref_id'])."'");
    $product_id = $product_obj->getId();
    // delete any association of the product and its pictures
    ProductsMapper::deletePictures($product_id, $store_dbobj);
    $pictures = explode(',', $product['picture_urls']);
    ddd($pictures);
    $pic_id = array();
    foreach($pictures as $picture) {
        $picture_obj = new Picture($store_dbobj);
        $picture_obj->setSource('customized_import');
        $picture_obj->setUrl($product['picture_urls']);
        $picture_obj->save();
        array_push($pic_id, $picture_obj->getId());
    }
   
    // create the category
    $category = new Category($store_dbobj);
    if($category->setCategory($product['category'])) {
        $category->findOne("category='".$category->getCategory()."'");
        $category->setDescription($product['category']);
        $category->save();
    }
    
    
    $service = new ProductService();
    $service->setMethod('createProduct');
    $service->setParams(array(
        'store_dbobj' => $store_dbobj,
        'post_data' => array(
            'product_description' => $product['description'],
            'product_quantity' => $product['quantity'],
            'product_name' => $product['name'],
            'product_price' => $product['price'],
            'product_id' => $product_id,
            'pic_id' => $pic_id,
            'ext_ref_id' => $product['ext_ref_id'],
            'ext_ref_url' => $product['ext_ref_url'],
            'brand' => $product['brand'],
            'misc' => $product['misc'],
            'product_category' => $category->getId(),
        )
    ));
    $service->call();
    
    $errnos = $service->getErrnos();
    ddd($product);
    if(!empty($errnos)) {
        ddd($errnos);
        die("ERROR: ProductService call\n");
    }

}

/*
delete from products;
delete from pictures;
delete from products_pictures;
delete from categories;
delete from products_categories;
 */

