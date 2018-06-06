shopinterest.controllers.admin_store_detail = new function() {
    console.log("abc");
    var utils = shopinterest.common.utils;

    $("#product-delete-all").click(function(e){
        if(!confirm("Are you sure to delete all products?")) return;
        var post_data = {action: 'product-delete-all', data:{}};
        post_data.data.store_id = $("#store_id").val();
        utils.spinner.show();
        utils.post('/api/manage-store', {data: post_data}, function(response) {
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
                window.location.reload();
            }
            utils.spinner.close();
        });
    });

    $("#product-delete-one").click(function(e){
        var post_data = {action: 'product-delete-all', data:{}};
        var pid = $(this).parents("tr").find("input").val();
        if(!/\d+/.test(pid)){
            alert('Please enter a product id!');
            return;
        }
        if(!confirm("Are you sure to delete product whoes id is " + pid + "?")) return;
        post_data.data.store_id = $("#store_id").val();
        post_data.data.product_id = pid;
        utils.spinner.show();
        utils.post('/api/manage-store', {data: post_data}, function(response) {
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
                //window.location.reload();
            }
            utils.spinner.close();
        });
    });

};
