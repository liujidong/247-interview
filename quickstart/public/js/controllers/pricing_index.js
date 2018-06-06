shopinterest.controllers.pricing_index = new function() {
    var popup_signup = new shopinterest.modules.popup_signup();
    popup_signup.render($('body'), 'signup');

    $('.plan-item-button').click(function(){
        popup_signup.show();        
    });
};
