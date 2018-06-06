shopinterest.controllers.base = new function() {

    var pageControllerName = shopinterest.common.utils.getPageControllerName();
    var controller_action = shopinterest.common.utils.get_controller_action();
    var controller = controller_action[0];
    var action = controller_action[1];
    var subdomain_type = shopinterest.common.utils.getSubdomainType();
    var is_user = shopinterest.common.utils.isUser();
    var is_merchant = shopinterest.common.utils.isMerchant();
    var is_associate = shopinterest.common.utils.isAssociate();
    var is_anonymous = shopinterest.common.utils.isAnonymous();
    var user_id = shopinterest.common.utils.getUserId();
    var merchant_id = shopinterest.common.utils.getMerchantId();
    var associate_id = shopinterest.common.utils.getAssociateId();
    var is_store_launched = shopinterest.common.utils.isStoreLaunched();

    var router = function() {
        var popup_login_panel = new shopinterest.modules.popup_signup();
        popup_login_panel.render($('.tgt_popup_signup'));

        var popup_signup_panel = new shopinterest.modules.popup_signup();
        popup_signup_panel.render($('.tgt_signup_lightbox'), 'signup');
        // bind login popup
        if($('body').hasClass('loggedout')){
            var target_elements = $('.loggedout .need_login');
            $.each(target_elements, function(i, item ){
                var target_element = $(item);
                target_element.unbind('click').bind('click', function(e){
                    e.preventDefault();
                    e.stopPropagation();

                    if(target_element.is('.signup')) {
                        popup_signup_panel.show();
                    } else {
                        popup_login_panel.show();
                    }
                });
            });
        };

        var cart = new shopinterest.modules.nav_cart();
        cart.render($("#top-nav-cart"));

        setup_simple_gat();

        if(shopinterest.map[pageControllerName]) {
            shopinterest.use(pageControllerName, function(shopinterest) {
            });
        }

        $(".nav-cart-link").click(gat_handler("cart-view-common", {label:  "STORE - from nav header"}));
    };
    // route to the specific controller/action
    router();

    // event handlers for merchant type pages
    if(subdomain_type === 'merchant') {
        /* search box */
        $('.searchform').submit(function(e) {
            var q = $.trim($(this).find('#store_search').val());
            if(q.length !== 0) {
                return true;
            } else {
                return false;
            }
        });

        // event handlers for merchants
        if(is_merchant) {

            // event handlers for merchants who launched the store
            if(is_store_launched) {
                /* pinstore button*/
                shopinterest.use('modules-pinstore_button', 'templates-pinstore_button',
                                 'modules-pinstore_lightbox', 'templates-pinstore_lightbox', 'templates-pinstore_lightbox_form',
                                 function(shopinterest) {

                                     var pinstore_lightbox = new shopinterest.modules.pinstore_lightbox();
                                     var pinstore_button = new shopinterest.modules.pinstore_button(pinstore_lightbox);
                                     pinstore_button.render($('.tgt_pinstore_button_2'), false, true);

                                 });
            }
        }
    }

    // event handlers for store subdomain type of pages and home page
    if(subdomain_type === 'store' || (controller === 'index' && action === 'index')) {
        /* facebook feed button*/
        shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {
            var fbfeed_button = new shopinterest.modules.fbfeed_button();
            fbfeed_button.render($('.tgt_fbfeed_button'), {
                'name': 'Check out my new ShopInterest store! It\'s so Cool! ',
                'caption': $('#my_store_name').val(),
                'description': 'I just created an amazing store in minutes. I\'m sure you will find the perfect gift! Check it out, you\'ll love it.',
                'link': $('#my_store_url').val(),
                'picture': $('#my_store_logo').val(),
                'button_icon': {
                    'img_src': 'http://w.sharethis.com/images/facebook_counter.png',
                    'width': 60,
                    'height': 22
                }
            });
            /* ga tracking */
            $(".tgt_fbfeed_button a").click(gat_handler("social-share-store", {label:  "Facebook"}));
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
            $(".tgt_fbsend_button a").click(gat_handler("social-share-store", {label:  "EMail"}));
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
            $(".tgt_tweet_button a").click(gat_handler("social-share-store", {label:  "Twitter"}));
            /* end ga tracking */

        });
    }
};
