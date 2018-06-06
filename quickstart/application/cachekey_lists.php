<?php

global $dbconfig;
$account_dbname = $dbconfig->account->name;
$store_dbname = $dbconfig->store->name;

/************************
 *  Common Conditions   *
 ************************/

$cond_active_product_normal = CacheKey::c('name!=&price>0&quantity>0&pictures!=&status!=127');
$cond_active_product = $cond_active_product_normal->copy()->_and("global_category_id!=0");

/************************************
 * List CacheKey Generator Function *
 ************************************/

// HOMEPAGE
function lck_featured_products($featured = SLIDER_FEATURED, $root_category_id='_') {
    if($featured === SLIDER_FEATURED || $featured === AD_FEATURED) {
        return CacheKey::q('featured_products')->_and($GLOBALS['cond_active_product'])->
        _and("store_status=2")->_and('featured='.$featured)->desc('featured_score');
    } else {
        return CacheKey::q('featured_products')->_and($GLOBALS['cond_active_product_normal'])->
        _and("store_status=2")->_and('featured='.$featured)->
        _and('root_category_id='.$root_category_id)->desc('featured_score');
    }
}

function lck_categories($l1 = false) {
    if(!$l1) {
        return CacheKey::q($GLOBALS['account_dbname'] . ".global_categories")->_and("status!=127")->asc("rank");
    } else {
        return CacheKey::q($GLOBALS['account_dbname'] . ".global_categories")->_and("status!=127")->_and('parent_id=0')->asc("rank");
    }
}

// STORE HOMEPAGE
// list: store actove products
function lck_store_active_products($store_dbname, $order_by = 'updated') {
    return CacheKey::q($store_dbname.'.products')->_and($GLOBALS['cond_active_product'])->desc($order_by);
}

function lck_store_inactive_products($store_dbname) {
    return CacheKey::q($store_dbname.'.products?status!=127&pictures!=')->
        _and(CacheKey::c("name=|price<=0|quantity<=0|global_category_id<=0"))->desc('updated')->
        label("INACTIVE");
}

function lck_store_resell_products($store_dbname) {
    return CacheKey::q($store_dbname.'.products')->
        //_and($GLOBALS['cond_active_product'])->
        _and('status!=127')->
        _and('resell!=0')->
        desc('updated')->
        label("RESELL");
}

function lck_store_tag_products($store_dbname, $tag, $orderby = 'updated'){
    return CacheKey::q($store_dbname.'.products')->_and($GLOBALS['cond_active_product'])->
        _and("tag<-$tag")->desc($orderby);
}

function lck_store_category_products($store_name, $cat_l1, $cat_l2='', $orderby='updated') {
    $ret = CacheKey::q($store_name.'.products')->_and($GLOBALS['cond_active_product_normal']);
    if(!empty($cat_l1) && !empty($cat_l2)) {
        return $ret->_and("category_l1=". $cat_l1)->_and("category_l2=". $cat_l2)->desc($orderby);
    } else if(!empty($cat_l1)) {
        return $ret->_and("category_l1=". $cat_l1)->desc($orderby);
    }
}

// global category for store
function lck_store_categories($store_name){
    $ret = CacheKey::q($store_name.'.store_global_categories?id>0&product_cnt>0')->desc('rank');
    return $ret;
}

// tags
function lck_store_tags($store_name){
    $ret = CacheKey::q($store_name.'.categories?product_cnt>0')->desc('product_cnt');
    return $ret;
}

// STORES
function lck_stores($status, $sort = 'updated') {
    return CacheKey::q($GLOBALS['account_dbname'].'.stores?status=' . $status)->desc($sort);
}

// MYORDERS
function lck_myorders_base(){
    $q = CacheKey::q($GLOBALS['account_dbname'].'.myorders?status!=' . DELETED)->desc('updated');
    return $q;
}

function lck_myorders_for_store($sid, $pstatus = NULL){
    $q = lck_myorders_base();
    $q->_and("store_id=$sid");
    $q->label("FOR_STORE");
    if(!empty($pstatus)) {
        $q->_and("payment_status=$pstatus");
    }
    return $q;
}

function lck_myorders_for_shopper($uid, $pstatus = NULL){
    $q = lck_myorders_base();
    $q->_and("user_id=$uid");
    $q->label("FOR_SHOPPER");
    if(!empty($pstatus)) {
        $q->_and("payment_status=$pstatus");
    }
    return $q;
}

// customers

function lck_store_customers(){
    return CacheKey::q($GLOBALS['store_dbname'].'.customers')->_and('status!='.DELETED)->desc('updated');
}

// wallet activities
function lck_wallet_activities($uid = NULL, $status = NULL) {
    $q = CacheKey::q($GLOBALS['account_dbname'].'.wallet_activities')->desc('updated');
    if(!empty($uid)){
        $q->_and("user_id=$uid");
    }
    if(empty($status)){
        $q->_and("status!=" . DELETED);
    }else{
        $q->_and("status=$status");
    }
    return $q;
}

// resell order items
function lck_resell_order_items($uid = NULL, $status = NULL) {
    $q = CacheKey::q($GLOBALS['account_dbname'].'.resell_order_items')->desc('updated');
    if(!empty($uid)){
        $q->_and("user_id=$uid");
    }
    if(empty($status)){
        $q->_and("status!=" . DELETED);
    }else{
        $q->_and("status=$status");
    }
    return $q;
}

function lck_store_coupon($store_id = NULL) {
    $q = CacheKey::q($GLOBALS['account_dbname'].'.mycoupons?status!=127')->desc('updated');
    if(!empty($store_id)){
        $q->_and('store_id='.$store_id);
    }
    return $q;
}

function lck_deal_coupon($oper = 'admin') {
    $q = CacheKey::q($GLOBALS['account_dbname'].'.mycoupons?status!=127')->desc('updated');
    $q->_and('scope='. PRODUCT);
    $q->_and('operator='. $oper);
    $q->_and('is_deal=1');
    return $q;
}

function lck_amazon_products($keyword, $searchIndex, $page=1) {
    return CacheKey::q('amazon_products')->_and('keyword='.$keyword)->_and('searchIndex='.$searchIndex)->_and('page='.$page);
}

function lck_amazon_products_base() {
    $q = CacheKey::q($GLOBALS['account_dbname'].'.amazon_products?status!=127')->desc('updated');
    return $q;
}

function lck_featured_amazon_products_by_cat($f, $cat_l1, $cat_l2='', $orderby='featured_score') {
    $ret = CacheKey::q($GLOBALS['account_dbname'].'.amazon_products?status!=127&featured=' . $f);
    if(!empty($cat_l1) && !empty($cat_l2)) {
        return $ret->_and("category_l1=". $cat_l1)->_and("category_l2=". $cat_l2)->desc($orderby);
    } else if(!empty($cat_l1)) {
        return $ret->_and("category_l1=". $cat_l1)->desc($orderby);
    }
}

/************************
 *  Pre-Defined List    *
 ************************/
$maintain_list = array(
    'product' => array(
        // HOMEPAGE
        lck_featured_products(), // slider featured products
        lck_featured_products(AD_FEATURED), // ad featured products
        lck_featured_products(CATEGORY_FEATURED), // category featured products
        // STORE HOMEPAGE
        lck_store_active_products($dbconfig->store->name), // store active products
        lck_store_active_products($dbconfig->store->name, 'price'), // store active products order by price
        lck_store_inactive_products($dbconfig->store->name), // store inactive products
        lck_store_resell_products($dbconfig->store->name), // store resell products
        lck_store_tag_products($dbconfig->store->name, '_'), // store active products by tag
        lck_store_tag_products($dbconfig->store->name, '_', 'price'), // store active products by tag
        lck_store_category_products($dbconfig->store->name, '_', NULL, 'updated'),
        lck_store_category_products($dbconfig->store->name, '_', NULL, 'price'),
        lck_store_category_products($dbconfig->store->name, '_', '_', 'updated'),
        lck_store_category_products($dbconfig->store->name, '_', '_', 'price')
    ),
    'category' => array(
        lck_store_tags($dbconfig->store->name),
    ),
    'global_category' => array(
        lck_categories(), // global categories ordered by rank
        lck_categories(true), // top level global categories ordered by rank
    ),
    'store_global_category' => array(
        lck_store_categories($dbconfig->store->name),
    ),
    'store' => array(
        lck_stores('_')
    ),
    'user' => array(),
    'myorder' => array(
        lck_myorders_for_store('_'),
        lck_myorders_for_store('_', '_'),
        lck_myorders_for_shopper('_'),
        lck_myorders_for_shopper('_', '_'),
    ),
    'wallet_activity' => array(
        lck_wallet_activities(NULL, NULL),
        lck_wallet_activities(NULL, '_'),
        lck_wallet_activities('_', NULL),
        lck_wallet_activities('_', '_'),
    ),
    'customer' => array(
        lck_store_customers($dbconfig->store->name)
    ),
    'mycoupon' => array(
        lck_store_coupon('_'),
        lck_store_coupon()
    ),
    'amazon_product' => array(
        lck_featured_amazon_products_by_cat('_', '_'),
    ),
);
