
shopinterest.controllers.iframe_creatreselleproducts = new function() {

    var create_product_lightbox = window.parent.$("#create_resell_product_lightbox");

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
};
