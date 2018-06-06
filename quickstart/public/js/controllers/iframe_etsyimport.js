shopinterest.controllers.iframe_etsyimport = new function() {
    
    var etsy_import_lightbox = window.parent.$("#etsy_import_lightbox");    
    var create_product_iframe = window.parent.$("#create_product_iframe");
    
    $('.close').click(function(e) {
        etsy_import_lightbox.trigger('close');
    });
    
    $('.pick_button').click(function() {
        etsy_import_lightbox.trigger('close');
        create_product_iframe.trigger({
            type : 'filepicker:popup',
            from : 'etsy_import'
        });
    });
    
}
