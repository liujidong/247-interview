
shopinterest.libs.fbapi = new function() {

    var fbapi_template = shopinterest.templates.fbapi;
    var _this = this;

    $('body').prepend(fbapi_template);
  
    window.fbAsyncInit = function() {
        // init the FB JS SDK
        FB.init({
            appId      : shopinterest.constants.fb_app_id, // App ID from the App Dashboard
            channelUrl : shopinterest.constants.base_url+'/'+'channel.php', // Channel File for x-domain communication
            status     : true, // check the login status upon init?
            cookie     : true, // set sessions cookies to allow your server to access the session?
            xfbml      : true  // parse XFBML tags on this page?
        });

        // Additional initialization code such as adding Event Listeners goes here
    };

    // Load the SDK's source Asynchronously
    (function(d){
        var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement('script');
        js.id = id;
        js.async = true;
        js.src = '//connect.facebook.net/en_US/all.js';
        ref.parentNode.insertBefore(js, ref);
    }(document));

    // fb feed dialog
    _this.showFeedDialog = function(feed, success, failure) {

        FB.ui({
            method: 'feed',
            name: feed.name,
            caption: feed.caption,
            description: feed.description,
            link: feed.link,
            picture: feed.picture
        }, function(response) {
                if (response && response.post_id) {
                    if(success) {
                        success();
                    }
                } else {
                    if(failure) {
                        failure();
                    }
                }
            }
        );
        
        
    };
    
    // fb send dialog
    _this.showSendDialog = function(msg) {
        FB.ui({
            method: 'send',
            name: msg.name,
            link: msg.link
        });
    };
    
    // fb connect dialog
    _this.showLoginDialog = function(success, failure) {
        FB.login(function(response) {
            if (response.authResponse) {
                var access_token = response.authResponse.accessToken;
                // connected
                if(success) {
                    success(access_token);
                }
            } else {
                // cancelled
                if(failure) {
                    failure();
                }
            }
        }, {scope: 'email'});
    };
};















