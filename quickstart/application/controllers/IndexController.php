<?php

class IndexController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    // babykids_featured_products
    // electronics_featured_products
    // everythingelse_featured_products
    // healthbeauty_featured_products
    // home_featured_products
    // jewelry_featured_products
    // men_featured_products
    // women_featured_products
    // vintage_featured_products
    public function indexAction() {
        global $category_map;
        $slider_ck = lck_featured_products(SLIDER_FEATURED);
        $this->view->slider_featured_products = BaseMapper::getCachedObjects($slider_ck);
        $l1_category_keys = DAL::get(lck_categories(true));
        $this->view->category_featured_products = array();
        foreach($l1_category_keys as $category_key) {
            $category = DAL::get(CacheKey::q($category_key));
            $category_name = sanitize_string($category['name']);
            $view_category_var = $category_name.'_featured_products';
            // 1. get native products
            $this->view->category_featured_products[$category['name']] = BaseMapper::getCachedObjects(
                lck_featured_products(CATEGORY_FEATURED, $category['id'])
            );
            if($this->view->category_featured_products[$category['name']]['total_rows'] == 0) {
                // 2. get local stored amazon products
                $this->view->category_featured_products[$category['name']] = BaseMapper::getCachedObjects(
                    lck_featured_amazon_products_by_cat(CATEGORY_FEATURED, $category['name'])
                );
                if($this->view->category_featured_products[$category['name']]['total_rows'] == 0) {
                    // 3. search products from amazon
                    $this->view->category_featured_products[$category['name']] =
                        AmazonSearchService::getFeaturedProducts(
                            $category_map[$category['name']]['keyword'], $category_map[$category['name']]['category']
                        );
                }
            }
        }

        $annoucement_obj = new Annoucement($this->account_dbobj, 1);
        $this->view->content = $annoucement_obj->getContent();
    }
}
