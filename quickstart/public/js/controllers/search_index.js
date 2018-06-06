
shopinterest.controllers.search_index = new function() {
    
    shopinterest.common.utils.init_masonry();
    
    // fill in the q string
    var q = $.trim($.query.get('q'));
    if( q.length !== 0) {
        $('#store_search').val(q);
    }

    $(".product-link").click(gat_handler("product-view-common", {label:"PRODUCT - from searcg page"}));
    $(".store-link").click(gat_handler("store-view-common", {label:"STORE - from searcg page"}));
};
