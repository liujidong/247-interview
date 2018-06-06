shopinterest.controllers.iframe_csvimport = new function() {
    
    var csv_import_lightbox = window.parent.$("#csv_import_lightbox");    
    var create_product_iframe = window.parent.$("#create_product_iframe");
    
    $('.close').click(function(e) {
        csv_import_lightbox.trigger('close');
    });
    
    $('.pick_button').click(function() {
        csv_import_lightbox.trigger('close');
        create_product_iframe.trigger({
            type : 'filepicker:popup',
            from : 'etsy_import'
        });
    });
    
}
