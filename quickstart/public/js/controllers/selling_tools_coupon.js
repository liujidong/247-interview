shopinterest.controllers.selling_tools_coupon = new function(){
    var datatable = new shopinterest.modules.datatable($('#tgt_datatable'), 'store_coupon');

    $('#start-time').datepicker({ dateFormat: "yy-mm-dd" });
    $('#end-time').datepicker({ dateFormat: "yy-mm-dd" });

    $('#scope').on('change', function() {
        var product_url_input_box = $('#product-url-input-box');
        if ( this.value === '3' ) {
            product_url_input_box.fadeIn('fast')
            .focus();
        } else {
            product_url_input_box.hide();
        }
    });
    
};
