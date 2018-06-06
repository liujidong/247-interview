shopinterest.controllers.admin_payment_detail = new function() {

    var utils = shopinterest.common.utils;

    $("#disburse").click(function(e){
        var id = $("#wa-id").text();
        utils.spinner.show();
        $.post('/api/nc-disburse', {id:id}, function(response) {
            response = $.parseJSON(response);
            if(response.status != 'success') {
                alert("ERROR!");
                return false;
            }
            window.location.reload();
            return false;
        });
    });
};
