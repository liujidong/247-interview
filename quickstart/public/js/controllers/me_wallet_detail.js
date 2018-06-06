shopinterest.controllers.wallet_detail = new function() {
    var utils = shopinterest.common.utils;

    $("#withdraw").click(function(e){
        var wa_id = $("#wallet_activity_id").val();
        utils.spinner.show();

        $.post('/api/wallet-withdraw-request', {wallet_activity_id: wa_id}, function(response) {
            response = $.parseJSON(response);
            if(response.status === 'success') {
                alert("Your request is sent, we will contact you soon!");
            } else {
                alert("Sorry, request send failed");
            }
            utils.spinner.close();
        });
        return false;
    });
};
