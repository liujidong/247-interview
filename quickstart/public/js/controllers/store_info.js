shopinterest.controllers.store_index = new function() {

    /* Contact Function */
    shopinterest.use('modules-contact_merchant', 'templates-contact_merchant', function(shopinterest){
        var contact_merchant = new shopinterest.modules.contact_merchant();
        contact_merchant.render($('#storepage'));

        $('#contact').click(function(e) {
            gat(e, "user-contact-seller");
            e.preventDefault();
            if($('body').hasClass('loggedin')) {
                contact_merchant.show($('.contact_merchant_toemail').val(), $('.contact_merchant_toname').val());
            } else {
                //$('.signup').trigger('click');
            }
        });
    });
};
