shopinterest.controllers.login_index = new function() {

    var popup_signup = function(k){
        var popup_signup = new shopinterest.modules.popup_signup();
        popup_signup.render($('.tgt_popup_signup'), k);
        popup_signup.show();
    };

    $(".popup-forget-pass").click(function(e){
        popup_signup('reset');
    });
    $(".popup-signup").click(function(e){
        popup_signup('signup');
    });

    var email_to_fill = $.query.get('user').toString() ||
            $.query.get('username').toString() ||
            $.query.get('email').toString();
    if(email_to_fill.length>1){
        $("#signin-email").val(email_to_fill);
    }

    $("input.submit-button.signin-button").click(gat_handler("user-login", {label:  "from /login page"}));
};
