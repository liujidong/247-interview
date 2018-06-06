
shopinterest.controllers.iframe_createproducts = new function() {

    var create_product_lightbox = window.parent.$("#create_product_lightbox");

    // close button
    $('.close-modal').click(function(e) {
        e.preventDefault();
        create_product_lightbox.trigger('create_product_lightbox:close');
    });
    
    // select photos from filepicker
    $('.from_filepicker').click(function(e) {
        e.preventDefault();
        create_product_lightbox.trigger({
            type : 'filepicker:popup',
            from : 'social_import'
        });        
    });
    
    
    // select photos from pinterest
    $('.from_pinterest').click(function(e) {
        e.preventDefault();
        create_product_lightbox.trigger('pinterest:popup');
    });
    
    // create products from Etsy
    $('.from_etsy').click(function(e) {
        e.preventDefault();
        create_product_lightbox.trigger('etsy:popup');
    });
    
    // create products from csv
    $('.from_csv').click(function(e) {
        e.preventDefault();
        create_product_lightbox.trigger('csv:popup');
    });
};
