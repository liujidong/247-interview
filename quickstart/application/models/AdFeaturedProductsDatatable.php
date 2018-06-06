<?php

class AdFeaturedProductsCache extends FeaturedProductsCache {
    
}
AdFeaturedProductsCache::$ck = lck_ad_featured_products();
AdFeaturedProductsCache::$featured_type = AD_FEATURED;