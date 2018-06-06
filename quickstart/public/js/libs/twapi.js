
shopinterest.libs.twapi = new function() {


    // Load the SDK's source Asynchronously
    (function(d){
        var js, id = 'twitter-jssdk', ref = d.getElementsByTagName('script')[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement('script');
        js.id = id;
        js.async = true;
        js.src = '//platform.twitter.com/widgets.js';
        ref.parentNode.insertBefore(js, ref);
    }(document));
    

};















