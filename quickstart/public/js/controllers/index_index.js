shopinterest.controllers.index_index = new function() {

    var utils = shopinterest.common.utils;
    var pagination = utils.show_pagination;
    var init_masonry = utils.init_masonry;

    var options = {
        $AutoPlay: true,
        $DisplayPieces: 2,
        $DirectionNavigatorOptions: {
            $Class: $JssorDirectionNavigator$,
            $ChanceToShow: 2
        }
    },
    $slider = $('#feature-slider'),
    css;

    css = {
        width: $slider.width() + 'px',
        height: $slider.height() + 'px'
    };
    options.$SlideWidth = 400;
    $slider.css(css);
    $slider.find('.slide-list').css(css);
    var jssor_slider = new $JssorSlider$('feature-slider', options);

    // version 2 with foundation 5
    // if ( $('#feature-slider')[0] ) {

    //     $('#feature-slider').sugarSlide({
    //         time: 500,
    //         frameWidth: $('#feature-slider').width()
    //     });

    // } else {

    //     pagination();
    //     init_masonry();

    //     $('#slider').sugarSlide({
    //         time:700
    //     });

    //     $('.cart_list01_content').on( "mouseover", function (e) {
    //         $(e.currentTarget).find('.hiddenButtons').show();
    //     });

    //     $('.cart_list01_content').on( "mouseout", function (e) {
    //         $(e.currentTarget).find('.hiddenButtons').hide();
    //     });

    // }

    $('#register_form').submit(function() {
        var signup_button = $('#getstartedbutton');
        signup_button.addClass('in-progress');
        signup_button.attr('disabled', 'disabled');
    });

    /* ga tracking */
    $(".slide-item").click(gat_handler("product-view-common", {label: "PRODUCT - from homepage slider"}));
    $(".cat-featured-product-item").click(gat_handler("product-view-cat-featured", {label: "PRODUCT - from homepage"}));
    /* end ga tracking */

    var $side = $('#homepage').find('.sidebar-column'),
    $mainSection = $('#homepage').find('.main-section');

    if ( $mainSection.height() + 385 < $side.height() ) {
        $mainSection.css('height', $side.height() - 355 );
    }
};

