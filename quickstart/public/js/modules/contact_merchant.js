
shopinterest.modules.contact_merchant = function() {
    
    var module_name = 'contact_merchant';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var toemail_box = null;
    var toname_box= null;
    var reply_box= null;
    var subject_box = null;
    var content_box = null;
    var send_button = null;
    var alert_box_success = null;
    var alert_box_error = null;
    
    _this.render = function(tgt) {
        var template = shopinterest.templates.contact_merchant;
        var html = substitute(template, {id: id});
        tgt.append(html);
        container = $('#'+id);
        toemail_box = container.find('.contact_merchant_toemail');
        toname_box = container.find('.contact_merchant_toname'); 
        reply_box = container.find('.contact_merchant_replyto');         
        subject_box = container.find('.contact_merchant_subject');
        content_box = container.find('.contact_merchant_content');
        send_button = container.find('.contact_merchant_submit');
        alert_box_success = container.find('.alert-box.success');
        alert_box_error = container.find('.alert-box.alert');

        bindUI();
    };
    
    _this.show = function(email, name) {
        alert_box_error.hide();
        alert_box_success.hide();
        toemail_box.val(email);
        toname_box.val(name);
        $('#'+id).reveal();
    };
    
    var bindUI = function() {
        send_button.click(function(e) {
            utils.spinner.show();
            $.post('/api/sendemail', {
                toemail: toemail_box.val(), 
                toname: toname_box.val(),
                subject: subject_box.val(),
                text: content_box.val(),
                replyto: reply_box.val()
            }, function(response) {
                var response_obj = $.parseJSON(response);
                if(response_obj.status === 'success') {
                    alert_box_success.show();
                    container.slideUp('slow', function() {
                        container.trigger('reveal:close');
                    });
                } else {
                    alert_box_error.html(response_obj.data.errors[0].msg);
                    alert_box_error.show();
                }
                utils.spinner.close();
            });
            return false;
        });
      
        container.bind('reveal:close', function(e) {
            reply_box.val('');
            subject_box.val('');
            content_box.val('');
        });
    };
};


