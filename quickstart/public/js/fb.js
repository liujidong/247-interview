window.fbAsyncInit = function() {
        FB.init({
        appId      : '212167575573301',
        status     : true, 
        cookie     : true,
        xfbml      : true
        });
        /* All the events registered */
        FB.Event.subscribe('auth.login', function(response) {
            //console.log('auth.login', response);
            // pass the access token to the server
            var access_token = response.authResponse.accessToken;console.log(access_token);
//            if(access_token) {
//                $.post('/login/auth', {
//                    'access_token': access_token
//                }, function(auth) {
//                    if(auth==="1") {
//                        window.location.href = '/';
//                    }
//
//                });
//            }
            
        });
        FB.Event.subscribe('auth.logout', function(response) {
         //console.log('auth.logout', response);
         // do something with response
         //logout();
        });

        FB.getLoginStatus(function(response) {
         //console.log('getLoginStatus', response);
         if (response.session) {
             // logged in and connected user, someone you know
             login();
         }
        });
        FB.api('/me', function(user) {
            //console.log('/me', user);
//            if (user) {
//              var image = document.getElementById('image');
//              image.src = 'http://graph.facebook.com/' + user.id + '/picture';
//              var name = document.getElementById('name');
//              name.innerHTML = user.name
//            }
        });
    };
    
    (function(d){
       var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
       js = d.createElement('script'); js.id = id; js.async = true;
       js.src = "//connect.facebook.net/en_US/all.js";
       d.getElementsByTagName('head')[0].appendChild(js);
     }(document));


