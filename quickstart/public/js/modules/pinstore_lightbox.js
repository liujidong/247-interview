
shopinterest.modules.pinstore_lightbox = function() {
    
    var module_name = 'pinstore_store';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var current_page = '';
    var pinterest_email = '';
    var pinterest_password = '';
    var plogin_url = shopinterest.constants.base_service_url+'/plogin';
    var uploadpins_url = shopinterest.constants.base_service_url+'/uploadpins';
    var pinterest_account = null;
    var pinterest_boards = null;
    var tgt = null;
    var default_boardname = 'My Shopintoit Store';
    
    _this.render = function(tgt_in, data_in) {
        tgt = tgt_in;
        tgt.html('');
        var template = shopinterest.templates.pinstore_lightbox;
        current_page = data_in.current_page;
        data_in.id = id;
        data_in[current_page] = true;
        var html = substitute(template, data_in); 
        tgt.html(html);
        container = $('#'+id);
        _this.render_form(container, data_in);
    };
    
    _this.render_form = function(container, data_in) {
        container.html(substitute(shopinterest.templates.pinstore_lightbox_form, data_in));
        bindUI();
    }
    
    _this.show = function(tgt_in) {
        tgt = tgt_in;
        var data_in = {current_page: 'show_login'};
        data_in.id = id;
        _this.render(tgt, data_in);
        container.reveal();
    }
    
    var bindUI = function() {
        
        container.unbind('click');
        $('.close-reveal-modal').click(function(e) {
            container.trigger('reveal:close');
        });
        var data = {};
        if(current_page === 'show_login') {
            container.on('click', '#pinstore_lightbox_submit', function(e) {
                pinterest_email = $('#pinterest_email').val();
                pinterest_password = $('#pinterest_password').val();
                $.post(plogin_url, $.query.set('pinterest_email', pinterest_email).set('pinterest_password', pinterest_password).toString().replace('?', ''), function(response) {
                    
                    var response_obj = $.parseJSON(response);
                    if(response_obj.status === false) {
                        // show error msg
                        data[current_page] = true;
                        data['error_msg'] = response_obj.data.error_msg;
                        _this.render_form(container, data);
                    } else {
                        pinterest_account = response_obj.data.account;
                        pinterest_boards = response_obj.data.boards;
                        // show the create board form
                        current_page = 'show_createboard';
                        data[current_page] = true;
                        data['default_boardname'] = default_boardname;
                        _this.render_form(container, data);
                    }
                });
                
                
                
                
            });
        } else if(current_page === 'show_createboard') {
            container.on('click', '#pinstore_lightbox_submit', function(e) {
                //container.trigger('reveal:close');
                $.post(uploadpins_url, {pinterest_boardname: $('#boardname').val()}, function(response) {
                    
                    var response_obj = $.parseJSON(response);
                    if(response_obj.status === false) {
                        // show error msg
                        data[current_page] = true;
                        data['error_msg'] = 'Error on creating a new board';
                        _this.render_form(container, data);
                    } else {
                        //container.trigger('reveal:close');
                        // show the confirmation
                        current_page = 'show_confirmation';
                        data[current_page] = true;
                        data['button_text'] = 'Close';
                        _this.render_form(container, data);
                    }
                });
            });
            $('#select_boards').click(function(e) {
                e.preventDefault();
                var data = {};
                current_page = 'show_selectboards';
                data[current_page] = true;
                data['boards'] = pinterest_boards;
                _this.render_form(container, data);
            });        
        } else if(current_page === 'show_selectboards') {
            container.on('click', '#pinstore_lightbox_submit', function(e) {
                //container.trigger('reveal:close');
                $.post(uploadpins_url, {pinterest_board_id: $('#board_options').val()}, function(response) {
                    //console.log(response);
                    var response_obj = $.parseJSON(response);
                    if(response_obj.status === false) {
                        // show error msg
                        data[current_page] = true;
                        data['error_msg'] = 'Upload products to Pinterest error, try again...';
                        _this.render_form(container, data);
                    } else {
                        //container.trigger('reveal:close');
                        // show the confirmation
                        current_page = 'show_confirmation';
                        data[current_page] = true;
                        data['button_text'] = 'Close';
                        _this.render_form(container, data);
                    }
                    
                });
                
                
            });
            $('#create_board').click(function(e) {
                e.preventDefault();
                var data = {};
                current_page = 'show_createboard';
                data[current_page] = true;
                data['default_boardname'] = default_boardname;
                _this.render_form(container, data);
            });
        } else if(current_page === 'show_confirmation') {
            container.on('click', '#pinstore_lightbox_submit', function(e) {
                container.trigger('reveal:close');
            });
            
            
        }    
    };
    
    
};


