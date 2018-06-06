shopinterest.controllers.preview_index = new function() {
    
    
    /* pinstore button*/
    shopinterest.use('modules-pinstore_button', 'templates-pinstore_button', 
    'modules-pinstore_lightbox', 'templates-pinstore_lightbox', 'templates-pinstore_lightbox_form',
    function(shopinterest) {

        var pinstore_lightbox = new shopinterest.modules.pinstore_lightbox();
        //pinstore_lightbox.render($('.tgt_pinstore_lightbox'), {current_page: 'show_login'});
        
        var pinstore_button = new shopinterest.modules.pinstore_button(pinstore_lightbox);
        pinstore_button.render($('.tgt_pinstore_button'), true, false);
        
        /* ga tracking */
        if(typeof _gaq !== "undefined") {
            var categories = shopinterest.constants.categories;
            $('.tgt_pinstore_button').click(function(e) {
                _gaq.push(['_trackEvent', categories.social, 'click', 'pinstore button']);
            });
        }
        /* end ga tracking */
        
    });
        
    /* facebook feed button*/
    shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {
        var fbfeed_button = new shopinterest.modules.fbfeed_button();
        fbfeed_button.render($('.tgt_fbfeed_button'), {
            'name': 'Check out my new ShopInterest store! It\'s so Cool! ',
            'caption': $('#my_store_name').val(),
            'description': 'I just created an amazing store in minutes. I\'m sure you will find the perfect gift! Check it out, you\'ll love it.',
            'link': $('#my_store_url').val(),
            'picture': shopinterest.constants.base_url+$('#my_store_logo').val(),
            'button_icon': {
                'img_src': 'http://w.sharethis.com/images/facebook_counter.png',
                'width': 60,
                'height': 22
            }
        });
        /* ga tracking */
        if(typeof _gaq !== "undefined") {
            var categories = shopinterest.constants.categories;
            $('.tgt_fbfeed_button').click(function(e) {
                _gaq.push(['_trackEvent', categories.social, 'click', 'facebook feed button']);
            });
        }
        /* end ga tracking */
    });

    /* facebook send button*/
    shopinterest.use('modules-fbsend_button', 'templates-fbsend_button', function(shopinterest) {
        var fbsend_button = new shopinterest.modules.fbsend_button();
        fbsend_button.render($('.tgt_fbsend_button'), {
            'name': 'Check out my new ShopInterest store - Do you like it? Tell me what you think, OK? I\'m sure you will find the perfect gift! Check it out, you\'ll love it.',
            'link': $('#my_store_url').val(),
            'button_icon': {
                'img_src': 'http://w.sharethis.com/images/email_counter.png',
                'width': 60,
                'height': 22
            }
        });
        /* ga tracking */
        if(typeof _gaq !== "undefined") {
            var categories = shopinterest.constants.categories;
            $('.tgt_fbsend_button').click(function(e) {
                _gaq.push(['_trackEvent', categories.social, 'click', 'facebook send button']);
            });
        }
        /* end ga tracking */
    });

    /* twitter tweet button*/
    shopinterest.use('modules-tweet_button', 'templates-tweet_button', function(shopinterest) {
        var tweet_button = new shopinterest.modules.tweet_button();
        tweet_button.render($('.tgt_tweet_button'), {
            'url': $('#my_store_url').val(),
            'via': 'shopinterest',
            'text': 'I just created an amazing store in minutes. I\'m sure you will find the perfect gift here!',
            'button_icon': {
                'img_src': 'http://w.sharethis.com/images/twitter_counter.png',
                'width': 60,
                'height': 22
            }
        });
        /* ga tracking */
        if(typeof _gaq !== "undefined") {
            var categories = shopinterest.constants.categories;
            $('.tgt_tweet_button').click(function(e) {
                _gaq.push(['_trackEvent', categories.social, 'click', 'twitter tweet button']);
            });
        }
        /* end ga tracking */

    });    
};
