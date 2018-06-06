shopinterest.controllers['me_payment-accounts-creditcard'] = new function() {

    var utils = shopinterest.common.utils;

    $("#save-and-continue").click(function(e){
        $(this).addClass('continue');
    });

    $('#payment-creditcard-form').on('invalid', function(e) {

    }).on('valid', function(e) {
        var _this = $(this);
        var credit_card = utils.get_post_data(_this);

        utils.spinner.show();

        utils.post('/api/save-credit-card', {credit_card: credit_card}, function(response) {
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
                var data = response.data;
                if(data.verified == 1){
                    $(".card-verified").text('YES');
                }
                if($("#save-and-continue").hasClass("continue")){
                    window.location.href="/dashboard";
                }
            }
            $("#save-and-continue").removeClass('continue');
            utils.spinner.close();
        });
    });
};
