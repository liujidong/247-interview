shopinterest.controllers.admin_coupon = new function() {
    var datatable = new shopinterest.modules.datatable($('#tgt_datatable'), 'admin_coupon');

    $('#start-time').datepicker({ dateFormat: "yy-mm-dd" });
    $('#end-time').datepicker({ dateFormat: "yy-mm-dd" });

    $('#scope').on('change', function() {
        var product_url_input_box = $('#product-url-input-box');
        var amazon_product_url_input_box = $('#amazon-product-url-input-box');
        var store_url_input_box = $('#store-url-input-box');        
        if ( this.value === '3' ) {
            product_url_input_box.fadeIn('fast').focus();
            store_url_input_box.hide();
            amazon_product_url_input_box.hide();
        } else if(this.value === '4'){
            product_url_input_box.hide();
            store_url_input_box.hide();
            amazon_product_url_input_box.fadeIn('fast').focus();
        } else {
            store_url_input_box.fadeIn('fast').focus();
            product_url_input_box.hide();
            amazon_product_url_input_box.hide();
        }
    });

};
