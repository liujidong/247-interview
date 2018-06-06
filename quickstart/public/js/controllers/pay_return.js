shopinterest.controllers.pay_return = new function() {

    /* facebook feed button*/
    shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {
        $.each($('.fb'), function(i, elem) {

            var elem_obj = $(elem);
            var section = elem_obj.closest('tr');
            var product_name = section.find('.td_product_name').html();
            var product_img = section.find('.td_img img').attr('src');
            var url = section.find('.td_img a').attr('href');
            var fbfeed_button = new shopinterest.modules.fbfeed_button();

            fbfeed_button.render(elem_obj, {
                'name': 'Check out the amazing deals at Shopinterest',
                'caption': product_name,
                'description': 'Checkout this amazing deal '+product_name,
                'link': url,
                'picture': product_img,
                'button_icon': {
                    'img_src': '/img/32x32-facebook.png',
                    'width': 32,
                    'height': 32
                }
            });
        });
        $(".fb a").click(gat_handler("social-share-product", {label:  "Facebook"}));
    });

    /* twitter tweet button*/
    shopinterest.use('modules-tweet_button', 'templates-tweet_button', function(shopinterest) {
        $.each($('.tw'), function(i, elem) {
            var elem_obj = $(elem);
            var section = elem_obj.closest('tr');
            var product_name = section.find('.td_product_name').html();
            var product_img = section.find('.td_img img').attr('src');
            var url = section.find('.td_img a').attr('href');
            var tweet_button = new shopinterest.modules.tweet_button();

            tweet_button.render(elem_obj, {
                'url': url,
                'via': 'shopinterest',
                'text': 'Checkout this amazing deal '+product_name,
                'button_icon': {
                    'img_src': '/img/32x32-twitter.png',
                    'width': 32,
                    'height': 32
                }
            });
        });
        $(".tw a").click(gat_handler("social-share-product", {label:  "Twitter"}));
    });

    /* pin share button*/
    shopinterest.use('modules-pin_button', 'templates-pin_button', function(shopinterest) {
        $.each($('.pn'), function(i, elem) {
            var elem_obj = $(elem);
            var section = elem_obj.closest('tr');
            var product_name = section.find('.td_product_name').html();
            var product_img = section.find('.td_img img').attr('src');
            var url = section.find('.td_img a').attr('href');
            var pin_button = new shopinterest.modules.pin_button();

            pin_button.render(elem_obj, {
                'url': url,
                'img_url': product_img,
                'description': 'Checkout this amazing deal '+product_name,
                'button_icon': {
                    'img_src': '/img/32x32-pinterest.png',
                    'width': 32,
                    'height': 32
                }
            });
        });   
    });  

    $(".pn a").click(gat_handler("social-share-product", {label:  "Pinterest"}));
    $("#product_url").click(gat_handler("product-view-common", {label : "PRODUCT - from pay.return page"}));
    $("#store_url").click(gat_handler("store-view-common", {label : "STORE - from pay.return page"}));
    $("#buybutton").click(gat_handler("shopping-buy-again"));
};
