
shopinterest.controllers.flashdealstest_index = new function() {

//    $('.fb_connect').click(function(e) {
//        e.preventDefault();
//        var fbapi = shopinterest.libs.fbapi;
//        fbapi.showLoginDialog();
//        
//        
//    });
    /* facebook connect button*/
    shopinterest.use('modules-fbconnect_button', 'templates-fbconnect_button', function(shopinterest) {
        var fbconnect_button = new shopinterest.modules.fbconnect_button();
        var success = function() {
            FB.api('/me', function(response) {
                console.log(response);
                fbconnect_button.hide();
            });
        };
        var failure = function() {
            
        };
        
        fbconnect_button.render($('.tgt_fbconnect_button'), success);
    });
    
    
};

