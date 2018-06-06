
shopinterest.modules.password_editor = function() {
    var service_url = shopinterest.constants.base_service_url+'/updatepassword';
    var module_name = 'password_editor';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var save_password_error_box;
    var current_password_elem;
    var new_password_elem;
    var confirm_password_elem;
    var _this = this;

    _this.render = function(tgt) {
        var template = shopinterest.templates.password_editor;
        var html = substitute(template, {id: id}); 
        tgt.append(html);
        container = $('#'+id);
        save_password_error_box = $('#save_password_error_box');
        current_password_elem = $('#current_password');
        new_password_elem = $('#new_password');
        confirm_password_elem = $('#confirm_password');         
        bindUI();
    };
    
    _this.show = function() {
        $('#'+id).reveal();
    };
    
    var bindUI = function() {
                
        container.on('click', '#save_password', function(e) {
            
            var current_password = $.trim(current_password_elem.val());
            var new_password = $.trim(new_password_elem.val());
            var confirm_password = $.trim(confirm_password_elem.val()); 
            
            if(current_password === '' || new_password === '' || confirm_password === '') {
                save_password_error_box.show();
            } else {
                $.post(service_url, {
                    current_password: current_password,
                    new_password: new_password,
                    confirm_password: confirm_password
                }, function(response) {
                    if(response === 'success') {
                        $('#profile').prepend('<div class="alert-box success row">Your password gets updated successfully.<a href="" class="close">×</a></div>');                        
                        $('.close-reveal-modal').trigger('click');
                    } else {
                        save_password_error_box.show();
                    }
                });
            }
        });
        
        container.on('click', '.close-reveal-modal', function(e) {
            save_password_error_box.hide();
            current_password_elem.val('');
            new_password_elem.val('');
            confirm_password_elem.val('');           
        });        
    };
};


