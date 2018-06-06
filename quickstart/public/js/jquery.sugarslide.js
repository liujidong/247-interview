/**
 * User: yuguo
 * Date: 13-10-24
 * Time: 3:03 pm
 * https://github.com/yuguo/sugarslide/
 * Modified: Alan Ouyang
 */

(function( $ ) {

    $.fn.sugarSlide = function( options ) {

        var settings, intFrameWidth, element, slideToNextPage, slideToPreviousPage, startAutoSlide, stopAutoSlide, isSliding, autoSlideInterval, controller, controllerWidth, items, itemWidth, slideWidth, userAgent, slideBoxes, cacheLeft;

        if (screen.width <= 640) {
            return;
        }

        settings = {
            time: 1000,
            autoSlideTime: 5000,
            prevClass: 'sugarslide-previous',
            nextClass: 'sugarslide-next',
            disabledClass: 'disabled'
        };

        $.extend(settings, options);

        intFrameWidth = settings.frameWidth || window.innerWidth;

        element = this;

        element
            .mouseover(function(){stopAutoSlide();})
            .mouseout(function(){startAutoSlide();});

        controller = element.find('.sugarslide-controller');
        items = controller.children();
        itemWidth = items.eq(0).outerWidth();
        controllerWidth = items.length * itemWidth;

        controller.css({
            width: controllerWidth + 'px',
            height: '100%',
            position: 'absolute'
        });

        items.wrapAll('<div class="slide-box" style="width:' + controllerWidth + 'px;" />');
        controller.append('<div class="slide-box" style="width:' + controllerWidth + 'px;margin-left:' + controllerWidth + 'px;" />');
        controller.find('.slide-box:last-child').append(items.clone());
        slideBoxes = controller.find('.slide-box');

        element.append('<a class="'+settings.prevClass+'"><span>Previous</span></a> <a class="'+settings.nextClass+'"><span>Next</span></a>');

        // items.each(function(i, that){
        //     if (!slideWidth &&
        //         (i + 1) * itemWidth >= intFrameWidth) {
        //         slideWidth = i * itemWidth;
        //     }
        // });
        slideWidth = itemWidth;

        $('.'+settings.nextClass).click(function(e) {
            e.preventDefault();

            if (isSliding) return;
            slideToNextPage();
        });

        $('.'+settings.prevClass).click(function(e) {
            e.preventDefault();

            if (isSliding) return;
            slideToPreviousPage();
        });

        slideToNextPage = function(){
            var left, width, thisBox, tempBoxes;

            isSliding = true;
            width = parseInt(slideBoxes.eq(0).css('marginLeft'));

            if ( Math.abs(width) === controllerWidth ) {
                slideBoxes.eq(0).css('marginLeft', 0);
                slideBoxes.eq(1).css('marginLeft', controllerWidth + 'px');
            }

            $.each(slideBoxes, function(i) {
                left = parseInt($(this).css('marginLeft')) - slideWidth;
                $(this).animate({'marginLeft': left});
            });

            setTimeout(function() {
                isSliding = false;
            }, settings.time + 150);
        };

        slideToPreviousPage = function(){
            var left, width, thisBox, tempBoxes;

            isSliding = true;
            width = parseInt(slideBoxes.eq(0).css('marginLeft'));

            if ( Math.abs(width) === 0 ) {
                slideBoxes.eq(0).css('marginLeft', - controllerWidth + 'px');
                slideBoxes.eq(1).css('marginLeft', 0);
            }

            $.each(slideBoxes, function() {
                left = parseInt($(this).css('marginLeft')) + slideWidth;
                $(this).animate({'marginLeft': left});
            })

            setTimeout(function() {
                isSliding = false;
            }, settings.time + 100);
        };

        startAutoSlide = function(){
            var timer = 0;
            autoSlideInterval = window.setInterval(function(){
                if ( timer / 2 >= controllerWidth / slideWidth ) {
                    stopAutoSlide();

                    setTimeout(function() {
                        startAutoSlide();
                    }, 60000);

                    return;
                }

                timer += 1;
                slideToNextPage();
            }, settings.autoSlideTime);
        };

        stopAutoSlide = function(){
            if (autoSlideInterval) {
                window.clearInterval(autoSlideInterval);
                autoSlideInterval = null;
            }
        };

        startAutoSlide();

        return this;

    };

}( jQuery ));
