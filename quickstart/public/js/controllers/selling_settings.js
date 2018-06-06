shopinterest.controllers.selling_settings = new function() {

    var store_logo = $('#store-logo');
    var utils = shopinterest.common.utils;

    // upload the store logo
    store_logo.add('#upload').click(function(e) {

        filepicker.pick({
            mimetypes: ['image/*']
        },
        function(InkBlob) {

            utils.spinner.show();
            utils.post(shopinterest.constants.base_service_url+'/set-store-avatar', {params: InkBlob}, function(response) {

                if(response.status === 'failure') {
                    utils.alertBox({
                        container: $('.alert-field'),
                        type: 'error'
                    });
                } else {

                    store_logo.attr('src', response.data.logo_url);

                    utils.alertBox({
                        container: $('.alert-field'),
                        type: 'success',
                        autohide: 'true',
                        message: 'Upload succeeded.'
                    });
                }

                utils.spinner.close();
            });

        });

    });

    $("#save-and-continue").click(function(e){
        $(this).addClass('continue');
    });

    $('#selling-settings-form').on('invalid', function(e) {
    }).on('valid', function(e) {
        var _this = $(this);
        var store = utils.get_post_data(_this);

        utils.spinner.show();

        utils.post('/api/save-selling-settings', {store: store}, function(response) {
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
