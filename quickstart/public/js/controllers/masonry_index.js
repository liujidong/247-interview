shopinterest.controllers.masonry_index = (function() {

    var utils = shopinterest.common.utils,
    $loading = $('#masonry-loading'),
    $container = $('.masonry-products');

    if ( $container ) {
        $.getScript('/js/masonry.min.js')
        .done(function() {
            $container.imagesLoaded(function() {
                $container.masonry({
                    isFitWidth: true,
                    isResizeBound: true
                });

                $loading.hide();
                $container.css('visibility', 'visible');
            });
        })
    }

})()