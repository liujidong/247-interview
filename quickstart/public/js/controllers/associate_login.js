shopinterest.controllers.associate_login = new function() {
    
    /* Show and reveal functions for sign in box */
    /* hides sign in section, reveals enter email section on click forgot pswrd txt */
    $('.lost_password').click(function (e) {
        $('.email_reminder').slideDown('medium').fadeTo('medium', 1.0);
        $('.signup_box').slideUp('medium').fadeTo('medium', 0);
    });

    /* hides enter email section, reveals sign in section on cancel */
    $('.email_reminder .button.secondary').click(function (e) {
        $('.email_reminder').slideUp('medium').fadeTo('medium', 0);
        $('.signup_box').slideDown('medium').fadeTo('medium', 1.0);
    });


    /* hides enter email section, reveals sign in section on cancel */
    $('.lost_password').click(function (e) {
        $('.alert-box').hide();
    });

    /* hides enter email section, reveals sign in section on reset password click */
    $('.email_reminder .button.success').click(function (e) {

        // reset the password and send an email
        $.post('/api/resetpass', {
            'email': $('#email_reminder').val()
        }, function(response) {
            if(response === 'success') {
                $('.email_reminder').slideUp('medium').fadeTo('medium', 0);
                $('.signup_box').slideDown('medium').fadeTo('medium', 1.0);
                $('.alert-box.success').css({ opacity: 0.0 }).slideDown('fast').fadeTo('slow', 1.0).delay(10000).fadeTo('slow', 0).slideUp('medium');
            } else {
                $('.email_reminder').slideUp('medium').fadeTo('medium', 0);
                $('.signup_box').slideDown('medium').fadeTo('medium', 1.0);
                $('.alert-box.alert').css({ opacity: 0.0 }).slideDown('fast').fadeTo('slow', 1.0).delay(10000).fadeTo('slow', 0).slideUp('medium');
            }
        });
    });
};


