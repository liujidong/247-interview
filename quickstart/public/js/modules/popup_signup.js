shopinterest.modules.popup_signup = function() {

    var module_name = 'popup_signup';
    var utils = shopinterest.common.utils;
    var fbapi = shopinterest.libs.fbapi;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var show_message = utils.show_message;
    var _this = this;
    var container = null;
    var tips = null;
    var close_button = null;
    var register_panel = null;
    var fb_register_button = null;
    var first_name = null;
    var last_name = null;
    var register_email = null;
    var register_password = null;
    var open_store = null;
    var register_submit_button = null;
    var change_login_button = null;

    var login_panel = null;
    var fb_login_button = null;
    var login_email = null;
    var login_password = null;
    var login_submit_button = null;
    var change_register_button = null;

    var reset_pwd_panel = null;
    var reset_email =null;
    var reset_submit_button =null;
    var change_reset_button = null;

    _this.render = function(tgt, first_panel) {
        var template = shopinterest.templates.popup_signup;
        var data = {id: id, signup: true, signin: true, reset: true};
        //signup/signin/reset
        if(first_panel == 'signup'){
            data.signup = false;
        } else if(first_panel == 'reset'){
            data.reset = false;
        } else {
            data.signin = false;
        }
        var html = substitute(template, data);
        tgt.append(html);
        container = $('#'+id);
        close_button = container.find('.close-modal');
        register_panel = container.find('#popup-signup');
        fb_register_button = register_panel.find('.button-fb');
        first_name = register_panel.find('.first-name');
        last_name = register_panel.find('.last-name');
        register_email = register_panel.find('.email');
        register_password = register_panel.find('.password');
        open_store = register_panel.find('.open-store');
        register_submit_button = register_panel.find('.submit');
        change_register_button = container.find('.change-popup-signup');

        login_panel = container.find('#popup-signin');
        fb_login_button = login_panel.find('.button-fb');
        login_email = login_panel.find('.email');
        login_password = login_panel.find('.password');
        login_submit_button = login_panel.find('.submit');
        change_login_button = container.find('.change-popup-signin');

        reset_pwd_panel = container.find("#popup-reset-password");
        reset_email = reset_pwd_panel.find('.email');
        reset_submit_button = reset_pwd_panel.find('.submit');
        change_reset_button = container.find('.change-popup-reset');

        bindUI();
    };

    _this.show = function() {
        container.reveal();
    };

    var get_tips = function(container){
        return container.find('.tips');
    };

    var bindUI = function() {

        close_button.click(function(e){
            e.preventDefault();
            container.trigger('reveal:close');
        });

        // click to sign in
        change_login_button.click(function(e){
            e.preventDefault();
            register_panel.hide();
            reset_pwd_panel.hide();
            login_panel.show();
        });

        // click to sign up
        change_register_button.click(function(e){
            e.preventDefault();
            login_panel.hide();
            reset_pwd_panel.hide();
            register_panel.show();
        });

        // click to reset pwd
        change_reset_button.click(function(e){
            e.preventDefault();
            login_panel.hide();
            register_panel.hide();
            reset_pwd_panel.show();
        });

        // click register_submit_button
        register_submit_button.click(function(e){
            e.preventDefault();
            gat(e, "user-signup");
            tips = get_tips(register_panel);
            var _first_name = $.trim(first_name.val());
            var _last_name = $.trim(last_name.val());
            var _email = $.trim(register_email.val());
            var _password = $.trim(register_password.val());
            var _open_store = open_store.prop('checked')?1:0;

            if(_first_name === '' || _last_name === '') {
                show_message('failure', tips, "First Name or Last Name missing.");
                return false;
            }

            if(_email === '' || _password === '') {
                show_message('failure', tips, "Email or password missing.");
                return false;
            }

            utils.spinner.show();

            $.post('/api/register', {
                first_name : _first_name,
                last_name : _last_name,
                username: _email,
                password: _password,
                open_store: _open_store},
                function(response) {
                    utils.spinner.close();
                    var response_obj = $.parseJSON(response);
                    if(response_obj.status === 'success') {
                        if(_open_store && response_obj.data.merchant_id !== 0) {
                            window.location.href = '/dashboard';
                        } else {
                            window.location.reload();
                        }
                    } else {
                        var error_msg = response_obj.data;
                        show_message('failure', tips, error_msg);
                        return;
                    }
            });
            return false;
        });

        // click login_submit_button
        login_submit_button.click(function(e){
            e.preventDefault();
            gat(e, "user-login");
            tips = get_tips(login_panel);
            var _email = login_email.val();
            var _password = login_password.val();
            if(_email === '' || _password ==='') {
                show_message('failure', tips, "Email or password missing.");
                return false;
            }

            utils.spinner.show();

            $.post('/api/login', {username: _email, password: _password}, function(response){
                utils.spinner.close();
                var response_obj = $.parseJSON(response);

                if(response_obj.status === 'success') {
                    // for existing merchants first login to new designed store
                    window.location.reload();
                } else {
                    var error_msg = response_obj.data;
                    show_message('failure', tips, error_msg);
                }
            });
            return false;
        });

        // click reset_submit_button
        reset_submit_button.click(function(e){
            e.preventDefault();
            //gat(e, "user-login");
            tips = get_tips(reset_pwd_panel);
            var _email = reset_email.val();
            if(_email === '') {
                show_message('failure', tips, "Email missing.");
                return false;
            }

            utils.spinner.show();
            gat(e, "user-login", {label: 'reset password from lightbox'});
            $.post('/api/resetpass', {
                'email': _email,
                'role' : 2
            }, function(response) {
                utils.spinner.close();
                if(response === 'success') {
                    register_panel.hide();
                    reset_pwd_panel.hide();
                    login_panel.show();
                    tips = get_tips(login_panel);
                    var msg = "Your password is reset, please check your email.";
                    show_message('success', tips, msg, -1);
                    return;
                } else {
                    var error_msg = "Sorry, but this account does not exist.";
                    show_message('failure', tips, error_msg);
                    return;
                }
            });
            return false;
        });

        $.each([fb_register_button, fb_login_button], function( i, obj ) {
            obj.on('click', function(e){
                e.preventDefault();
                if(i == 0) { //signup
                    gat(e, "user-signup", {label: "facebook signup from lightbox"});
                } else { //login
                    gat(e, "user-login", {label: "facebook login from lightbox"});
                }
                var success = function(access_token) {
                        window.location.href = shopinterest.constants.base_service_url+'/register?access_token='+access_token;
                };
                fbapi.showLoginDialog(success);
            });
        });

    };
};
