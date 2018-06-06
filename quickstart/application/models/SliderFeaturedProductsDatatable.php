<?php
class SliderFeaturedProductsCache extends FeaturedProductsCache {
    
}
SliderFeaturedProductsCache::$ck = lck_slider_featured_products();
SliderFeaturedProductsCache::$featured_type = SLIDER_FEATURED;