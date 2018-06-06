shopinterest.controllers.selling_orders_detail = new function() {
    var utils = shopinterest.common.utils;

    $('#shipping_date').datepicker({ dateFormat: "yy-mm-dd" });

    $('#shipping-form').on('invalid', function(e) {
    }).on('valid', function(e) {

        var date = $('#shipping_date').val();
        if (date === "0000-00-00" || date < $(".order-time-date").text()) {
            $('#shipping_date').parents('.data-abide-input-container').addClass('error');
            return false;
        }

        var _this = $(this);
        var o = utils.get_post_data(_this);
        utils.spinner.show();
        utils.post('/api/fulfill-order', {order: o}, function(response) {
            if(response.status === 'failure') {
                utils.alertBox({
                    container: $('.alert-field'),
                    type: 'error'
                });
            } else {
                utils.alertBox({
                    container: $('.alert-field'),
                    type: 'success',
                    autohide: 'true'
                });
            }
            utils.spinner.close();
        });
        return false;
    });

    $('#shipping_provider').on('change', function() {
        if ( this.value === 'other' ) {
            $(this).next('#extra_shipping_provider').fadeIn('fast').focus();
            $(this).next('#extra_shipping_provider').attr("required", "true");
        } else {
            $(this).next('#extra_shipping_provider').hide();
            $(this).next('#extra_shipping_provider').removeAttr("required");
        }
    });

    $('.cancel').click(function(e){
        if(!confirm("Are you sure to cancel this order? The money will be refunded to the buyer and deducted from your wallet's current balance. ")){
            return;
        }
        utils.spinner.show();
        var o = {order_id:$("#order_id").val()};
        $.post('/api/cancel-order', {order: o}, function(response) {
            response = $.parseJSON(response);
            utils.spinner.close();
            if(response.status != 'success') {
                alert("Error!");
            } else {
                window.location.reload();
            }
        });
    });
};
