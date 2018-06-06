shopinterest.controllers.ticket_index = new function(){

    var utils = shopinterest.common.utils;
    
    $('#ticket-form').on('invalid', function(e) {

    }).on('valid', function(e) {
        var _this = $(this);
        var email = utils.get_post_data(_this);

        utils.spinner.show();

        utils.post('/api/ticket', email, function(response) {
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
