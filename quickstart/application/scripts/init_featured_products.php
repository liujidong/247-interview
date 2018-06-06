<?php

require_once('includes.php');

// refresh slider featured products in redis
$slider_ck = lck_featured_products(SLIDER_FEATURED);
BaseMapper::getCachedObjects($slider_ck, array("force" => true));

// refresh ad featured products in redis
$ad_ck = lck_featured_products(AD_FEATURED);
BaseMapper::getCachedObjects($ad_ck, array("force" => true));

// refresh category featured products in redis
$l1_category_keys = DAL::get(lck_categories(true));
foreach($l1_category_keys as $category_key) {
    $category = DAL::get(CacheKey::q($category_key));
    BaseMapper::getCachedObjects(lck_featured_products(CATEGORY_FEATURED, $category['id']), array("force" => true));
}

/*
slider featured products key:
featured_products?featured=1&global_category_id!=0&name!=&pictures!=&price>0&quantity>0&status!=127&store_status=2&sort[featured_score]

ad featured product
featured_products?featured=3&global_category_id!=0&name!=&pictures!=&price>0&quantity>0&status!=127&store_status=2&sort[featured_score]

art
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=1&status!=127&store_status=2&sort[featured_score]

babykids
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=25&status!=127&store_status=2&sort[featured_score]

electronics
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=39&status!=127&store_status=2&sort[featured_score]

healthbeauty
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=53&status!=127&store_status=2&sort[featured_score]

home
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=71&status!=127&store_status=2&sort[featured_score]

jewelry
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=87&status!=127&store_status=2&sort[featured_score]

men
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=107&status!=127&store_status=2&sort[featured_score]

women
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=127&status!=127&store_status=2&sort[featured_score]

vintage
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=143&status!=127&store_status=2&sort[featured_score]

everythingelse
featured_products?featured=2&name!=&pictures!=&price>0&quantity>0&root_category_id=171&status!=127&store_status=2&sort[featured_score]

 */