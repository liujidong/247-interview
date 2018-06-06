shopinterest.controllers.shopbuilder_index = new function() {

    var utils = shopinterest.common.utils;

    var height = $(document).height(),
        $items = $('.landing-item');

    $('#landing-page').css('height', $items.length * height + 'px');
    $items.each(function(i) {
        $(this).css({
            top: i * height + 'px',
            zIndex: 0,
            height: height + 'px',
            display: 'block'
        })
        .find('.more').css('visibility', 'visible');
    });

    $('.more').on('click', function(e) {
        e.preventDefault();

        $('body').animate({
            scrollTop: $(this).parents('.landing-item').next().offset().top
        }, 400);
    });

    $('.try-it').on('click', function(e) {
        e.preventDefault();

        $('body').animate({
            scrollTop: $('.landing-last').offset().top
        }, 400);

        setTimeout(function() {
            $('.landing-last').find('input').eq(0).focus();
        }, 450);
    });

    $('.contact-form').on('invalid', function(e) {

    }).on('valid', function(e) {
        var _this = $(this);
        var data = utils.get_post_data(_this);

        utils.spinner.show();

        utils.post('/api/send-email-to-us', data, function(response) {
            if(response.status === 'failure') {
                utils.alertBox({
                    container: $('.alert-field'),
                    message: 'send error',
                    type: 'error'
                });
            } else {
                utils.alertBox({
                    container: $('.alert-field'),
                    type: 'success',
                    message: 'send success',
                    autohide: 'true'
                });
            }
            utils.spinner.close();
        });

    });
};
