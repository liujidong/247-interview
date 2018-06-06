$( document ).ready(function() {

    var root = new Firebase('https://shopintoit.firebaseio.com');
    var do_login = false;
    var response = {};

    // Before logging users in, instantiate the FirebaseSimpleLogin by passing 
    // a Firebase reference and a callback. This callback will be invoked any 
    // time that the user's authentication state changed
    auth = new FirebaseSimpleLogin(root, function(error, user) {
        if (error) {
            // an error occurred while attempting login
            console.log(error);
        } else if (user) {
            // user authenticated with Firebase
            console.log(user);
//            if(window.location.pathname === '/test/firebase-signin') {
//                window.location.href = '/test/firebase-home';
//            }

            if(do_login) {
                $.post('/test/firebase-auth', user, function(response) {
                    response = $.parseJSON(response);
                    if(response) {
                        window.location.href = '/test/firebase-home';
                        do_login = false;
                    }
                });
            }
        } else {
            // user is logged out
            console.log('logged out');
//            if(window.location.pathname !== '/test/firebase-signin') {
//                window.location.href = '/test/firebase-signin';
//            }
            
        }
    });

    $('.login').click(function(e) {
        e.preventDefault();
        
        var _this = $(this);
        var action_type = _this.attr('action-type');
        auth.login(action_type, {
            'scope': 'email',
            'email': $('#email').val(),
            'password': $('#password').val()
        });
        do_login = true;
    });

    $('.signup').click(function(e) {
        e.preventDefault();
        
        var email = $('#email').val();
        var password =  $('#password').val();
        auth.createUser(email, password, function(error, user) {
            console.log(error, user);
            if (!error) {
                auth.login('password', {
                    'email': email,
                    'password': password
                }); 
                do_login = true;
            }
        });
    });


    $('.logout').click(function(e) {
        e.preventDefault();
        auth.logout();
        window.location.href='/test/firebase-signin';
    });
    
    
});