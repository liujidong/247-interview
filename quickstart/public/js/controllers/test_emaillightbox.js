shopinterest.controllers.test_emaillightbox = new function() {

    shopinterest.use('modules-email_lightbox', 'templates-email_lightbox', function(shopinterest) {
        
        var email_lightbox = new shopinterest.modules.email_lightbox();
        email_lightbox.render($('#container'));
        
        $('.emailto').click(function(e) {
            e.preventDefault();
            email_lightbox.show($(this).html(), $(this).siblings('span.emailtoname').html());
        });
        
        
    });
    
};