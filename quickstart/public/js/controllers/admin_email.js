shopinterest.controllers.admin_email = new function() {
    var utils = shopinterest.common.utils;

    $('#template_type').on('change', function(e) {
        var _this = $(this);
        var type = $("#template_type").val();

        utils.spinner.show();

        utils.post('/api/get-email-template', {type: type}, function(response) {
            if(response.status === 'failure') {
                utils.alertBox({
                    container: $('.alert-field'),
                    type: 'error'
                });
            } else {
                $("#subject").val(response.data.subject);
                //tinyMCE.get('content').setContent(response.data.content);
                $('#content').val(response.data.content);
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
