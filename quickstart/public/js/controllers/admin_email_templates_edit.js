shopinterest.controllers.admin_email_templates_edit = new function() {
    var utils = shopinterest.common.utils;

    var ready = false;
    $('#edit-form').on('invalid', function(e) {
        ready = false;
    }).on('valid', function(e) {
        var _this = $(this);
        var tpl = utils.get_post_data(_this);
        tpl.content = tinyMCE.get('content').getContent();
        //tpl.content = $('#content').val();

        utils.spinner.show();

        utils.post('/api/save-email-template', {template: tpl}, function(response) {
            if(response.status === 'failure') {
                utils.alertBox({
                    container: $('.alert-field'),
                    type: 'error'
                });
            } else {
                $('tpl-id').val(response.data.id);
                utils.alertBox({
                    container: $('.alert-field'),
                    type: 'success',
                    autohide: 'true'
                });
                var id = $.query.get('id');
                if(!id){
                    window.location.href = window.location.href + "?id=" + response.data.id;
                }
            }
            utils.spinner.close();
        });
    });
};
