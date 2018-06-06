shopinterest.controllers.me_settings = new function() {

    var utils = shopinterest.common.utils;

    $("#save-and-continue").click(function(e){
        $(this).addClass('continue');
    });

    $('#me-settings-form').on('invalid', function(e) {

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
                if($("#save-and-continue").hasClass("continue")){
                    window.location.href="/dashboard";
                }
            }
            $("#save-and-continue").removeClass('continue');
            utils.spinner.close();
        });
    });

};
