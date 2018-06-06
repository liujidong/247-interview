
shopinterest.controllers.flashdeals_index = new function() {

    /* facebook feed button*/
    shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {

        $.each($('.tgt_fbfeed_button_2'), function(i, elem) {
            var fbfeed_button = new shopinterest.modules.fbfeed_button();
            var elem_obj = $(elem);
            var product = elem_obj.closest('.product');
            var percentage_off = product.find('.savings b').html();
            var product_img = product.find('.main_prod_img img').attr('src');
            var product_name = product.find('h2.prod_name').html();
            var product_desc = product.find('.prod_desc p').html();
            var product_reg_price = product.find('.regprice span').html();
            var product_sale_price = product.find('.saleprice span').html();

            fbfeed_button.render(elem_obj, {
                'name': 'Check out the amazing deals at Shopinterest Flash Sales',
                'caption': percentage_off+' '+product_name,
                'description': 'Checkout this amazing deal '+product_name+' sells at '+product_sale_price,
                'link': window.location.href,
                'picture': product_img,
                'button_icon': {
                    'img_src': '/img/32x32-facebook.png',
                    'width': 32,
                    'height': 32
                }
            });
        });
        $(".tgt_fbfeed_button_2 a").click(gat_handler("social-share-product", {label:  "Facebook"}));
    });

    /* twitter tweet button*/
    shopinterest.use('modules-tweet_button', 'templates-tweet_button', function(shopinterest) {

        $.each($('.tgt_tweet_button_2'), function(i, elem) {

            var elem_obj = $(elem);
            var product = elem_obj.closest('.product');
            var percentage_off = product.find('.savings b').html();
            var product_img = product.find('.main_prod_img img').attr('src');
            var product_name = product.find('h2.prod_name').html();
            var product_desc = product.find('.prod_desc p').html();
            var product_reg_price = product.find('.regprice span').html();
            var product_sale_price = product.find('.saleprice span').html();
            var tweet_button = new shopinterest.modules.tweet_button();

            tweet_button.render(elem_obj, {
                'url': window.location.href,
                'via': 'shopinterest',
                'text': 'Checkout this amazing deal '+product_name+' sells at '+product_sale_price,
                'button_icon': {
                    'img_src': '/img/32x32-twitter.png',
                    'width': 32,
                    'height': 32
                }
            });
        });
        $(".tgt_tweet_button_2 a").click(gat_handler("social-share-product", {label:  "Twitter"}));
    });


    /* twitter tweet button*/
    shopinterest.use('modules-pin_button', 'templates-pin_button', function(shopinterest) {

        $.each($('.tgt_pin_button_2'), function(i, elem) {

            var elem_obj = $(elem);
            var product = elem_obj.closest('.product');
            var percentage_off = product.find('.savings b').html();
            var product_img = product.find('.main_prod_img img').attr('src');
            var product_name = product.find('h2.prod_name').html();
            var product_desc = product.find('.prod_desc p').html();
            var product_reg_price = product.find('.regprice span').html();
            var product_sale_price = product.find('.saleprice span').html();
            var pin_button = new shopinterest.modules.pin_button();

            pin_button.render(elem_obj, {
                'url': window.location.href,
                'img_url': product_img,
                'description': 'Checkout this amazing deal '+product_name+' sells at '+product_sale_price,
                'button_icon': {
                    'img_src': '/img/32x32-pinterest.png',
                    'width': 32,
                    'height': 32
                }
            });
        });
        $(".tgt_pin_button_2 a").click(gat_handler("social-share-product", {label:  "Pinterest"}));
    });

    $(".main_prod_img a").click(gat_handler("product-view-common", {label:  "PRODUCT - from flashdeals"}));
    $(".store_url").click(gat_handler("store-view-common", {label:  "STORE - from flashdeals"}));
    $(".return-policy").click(gat_handler("shopping-return-policy", {label:  "RETURN POLICY - from flashdeals"}));
};
