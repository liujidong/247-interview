shopinterest.controllers.selling_terms = new function() {

    var utils = shopinterest.common.utils;

    $('.agree-terms').click(function(e) {

        utils.spinner.show();
        utils.post('/api/register-merchant', {agree: 1}, function(response){
            if(response.status === 'failure') {
                utils.alertBox({
                    container: $('.alert-field'),
                    type: 'error',
                    message: 'There is a problem on joining the selling venue. Please try again.'
                });
            } else {
                window.location.href = '/selling/products';
            }

            utils.spinner.close();
            return false;
        });
        return false;        
    });
};
