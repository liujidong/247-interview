shopinterest.controllers.deals_index = new function() {
    var $slider = $('#feature-slider'),
        $slideItem = $slider.find('li'),
        slideItemW = $slideItem.eq(0).width(),
        slideItemH = $slideItem.eq(0).height();

    var options = {
        $AutoPlay: true,
        $DisplayPieces: 3,
        $DirectionNavigatorOptions: {
            $Class: $JssorDirectionNavigator$,
            $ChanceToShow: 2
        }
    },
    css;

    $slideItem.css({
        width: slideItemW + 'px',
        height: slideItemH + 'px'
    });

    css = {
        width: $slider.width() + 'px',
        height: slideItemH + 'px'
    };

    $slider.find('.slides').css(css);
    $slider.css(css);

    options.$SlideWidth = slideItemW;
    var jssor_slider = new $JssorSlider$('feature-slider', options);
};
