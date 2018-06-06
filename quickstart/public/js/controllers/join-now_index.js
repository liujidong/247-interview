shopinterest.controllers['join-now_index'] = new function() {

    /* ga tracking */
    if(typeof _gaq !== "undefined") {
        var categories = shopinterest.constants.categories;
        $('.startfree').click(function(e) {
            _gaq.push(['_trackEvent', categories.signup, 'click', 'Start Free Trial (Join Now)']);
        });
        $('#getstartedbutton').click(function(e) {
            _gaq.push(['_trackEvent', categories.signup, 'click', 'Get Started (Join Now)']);
        });
        $('#register_form a.cancel').click(function(e) {
            _gaq.push(['_trackEvent', categories.signup, 'click', 'Cancel (Join Now)']);
        });
    }
    /* end ga tracking */

    $(".startfree").click(function(e){
        var popup_signup_panel = new shopinterest.modules.popup_signup();
        popup_signup_panel.render($('.tgt_signup_lightbox'), 'signup');
        popup_signup_panel.show();
    });
};
