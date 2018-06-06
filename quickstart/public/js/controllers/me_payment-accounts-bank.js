shopinterest.controllers['me_payment-accounts-bank'] = new function() {

    var utils = shopinterest.common.utils;

    $('#payment-bank-form').on('invalid', function(e) {

    }).on('valid', function(e) {
        var _this = $(this);
        var user = utils.get_post_data(_this);

        utils.spinner.show();

        utils.post('/api/save-user-settings', {user: user}, function(response) {
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




    });

};
