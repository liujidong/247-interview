
shopinterest.modules.email_lightbox = function() {
    
    var module_name = 'email_lightbox';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var toemail_box = null;
    var subject_box = null;
    var content_box = null;
    var send_button = null;
    var toname_box = null;
    var go_view = null;
    var go_compose = null;
    var compose_email = null;
    var view_email = null;
    var show_venue = null;
    var fromemail_field = null;
    var replyto_field = null;
    var toemail_field = null;
    var subject_field = null;
    var content_field = null;
    var row_ids_input = null;
    
    _this.render = function(tgt) {
        var template = shopinterest.templates.email_lightbox;
        var html = substitute(template, {id: id});
        tgt.append(html);
        container = $('#'+id);
        toemail_box = container.find('.email_lightbox_toemail');
        subject_box = container.find('.email_lightbox_subject');
        content_box = container.find('.email_lightbox_content');
        send_button = container.find('.email_lightbox_submit');
        toname_box = container.find('.email_lightbox_toname'); 
        go_view = container.find('.go_view');
        go_compose = container.find('.go_compose');
        compose_email = container.find('#compose_email');
        view_email = container.find('#view_email');
        show_venue = container.find('.show_venue');
        fromemail_field = container.find('.fromemail');
        replyto_field = container.find('.replyto');
        toemail_field = container.find('.toemail');
        subject_field = container.find('.subject');
        content_field = container.find('.content');
        row_ids_input = container.find('.row_ids');
        bindUI();
    };
    
    _this.show = function(email, name, row_ids) {
        toemail_box.val(email);
        toname_box.val(name);
        row_ids_input.val(row_ids);
        container.reveal();
    };

    _this.close = function() {
        container.trigger('reveal:close'); 
    };
    
    var bindUI = function() {  

        container.find('form').foundation('abide');
        
        go_view.click(function(e) {
            e.preventDefault();
            compose_email.hide();
            // render the html of the email content
            var replyto_email = $('#replyto_email').val();
            var replyto_name = $('#replyto_name').val();
            fromemail_field.html(replyto_name+' '+'&lt;xxx@shopinterest.co>&gt;');
            replyto_field.html(replyto_name+' '+'&lt;'+replyto_email+'&gt;');
            toemail_field.val(toemail_box.val());
            subject_field.html(subject_box.val());
            content_field.val(content_box.val());
            view_email.show();
        });
        go_compose.click(function(e) {
            e.preventDefault();
            view_email.hide();
            
            compose_email.show();
        });
        
        container.bind('reveal:close', function(e) {
            view_email.hide();
            subject_box.val('');
            content_box.val('');
            compose_email.show();
        });
 
    };
}; 
