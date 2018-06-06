shopinterest.controllers.admin_orders = new function() {
    var datatable = new shopinterest.modules.datatable($('#tgt_datatable'), 'orders_for_admin');
    var utils = shopinterest.common.utils;
    $('.cancel').click(function(e){
        var oid = $(this).attr("data-order-id");
        if(!confirm("Are you sure to cancel order [" + oid + "]?")){
            return;
        }
        utils.spinner.show();
        $.post('/api/cancel-order', {order: {order_id: oid}}, function(response) {
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
