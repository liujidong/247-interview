<?php

require_once('includes.php');

$featured_products = SearchProductsMapper::getProducts(
            $account_dbobj, 
            array(
                'where' => 'featured!=0'
            )
        );

$popular_products = SearchProductsMapper::getProducts(
            $account_dbobj,
            array(
                'orderby' => array(
                    'page_views' => 'desc'
                )
            )
        );

$homepage_products = array_merge($featured_products, $popular_products);

$new_homepage_products = unset_array_keys($homepage_products, array(
    'store_host', 'host'
));

$return = array('products' => $new_homepage_products);

dddd(json_encode($return));