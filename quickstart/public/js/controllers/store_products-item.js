shopinterest.controllers['store_products-item'] = new function() {

    var aid;
    var product_id;
    var ssn;
    var is_active_associate = shopinterest.common.utils.isActiveAssociate();
    var id = $.query.get('id').toString();
    var parts = id.split('_');
    if(parts.length === 2) {
        aid = parts.shift();
    } else {
        aid = $.query.get('aid').toString();
    }
    product_id = parseInt(parts.shift());

    var url = window.location.href;
    var domain = url.replace(/^\w+:\/\//, "").replace(/\/.*$/gi,"").toLowerCase();
    var domain_parts = domain.match(/([^.]+)(\.staging)?\.shopinterest\.co$/, "");
    if(domain_parts){
        var new_url = "http://www" + (domain_parts[2]? domain_parts[2] : "") + ".shopintoit.com/store/" + domain_parts[1];
        new_url = new_url + url.replace(/^\w+:\/\/.*?\//i, "/");
        console.log(new_url);
        //window.location.href = new_url;
    }

    var share_url = window.location.href.replace(/\?.*$/, "") + "?id=" + $("#aid").val() + "_" + product_id;

    var pre_product_quantity = 0;
    var order_id = 0;

    // native checkout add-to-cart button
    shopinterest.use('modules-add_to_cart', function(shopinterest) {
        var add_to_cart = new shopinterest.modules.add_to_cart();
        var cdiv = $(".native_add_to_cart");
        var pinfo = {
            store_id: cdiv.attr("store_id"),
            product_id: cdiv.attr("product_id"),
            currency: cdiv.attr("currency"),
            dealer: cdiv.attr('dealer'),
            external_id: cdiv.attr('external_id'),
            quantity: 1,
            custom_field: '',
            aid: aid
        };
        add_to_cart.render(cdiv, pinfo, cdiv.attr("product_quantity"), null);
    });

    $("#custom-field").change(function(e){
        var opt = $(this).find(":selected");
        var cf = opt.attr('value');
        var qty = opt.attr('quantity');
        var target = $('.product-cf');
        if(!cf || !qty){
            target.attr('value', '');
            target.attr('quantity', 0);
        } else {
            target.attr('value', cf);
            target.attr('quantity', qty);
        }
    });


    /* Contact Function */
    shopinterest.use('modules-contact_merchant', 'templates-contact_merchant', function(shopinterest){
        var contact_merchant = new shopinterest.modules.contact_merchant();
        contact_merchant.render($('#product-item'));

        $('#contact').click(function(e) {
            gat(e, "user-contact-seller");
            e.preventDefault();
            if($('body').hasClass('loggedin')) {
                contact_merchant.show($('.contact_merchant_toemail').val(), $('.contact_merchant_toname').val());
            }
        });
    });


    /* facebook feed button*/
    shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {

        $.each($('.tgt_fbfeed_button_2'), function(i, elem) {
            var fbfeed_button = new shopinterest.modules.fbfeed_button();
            var elem_obj = $(elem);
            var product_img = $('.orbit-slides-container img').attr('src');
            var product_name = $('h2.product-item-name').text();

            fbfeed_button.render(elem_obj, {
                'name': 'Check out the amazing product at Shopintoit',
                'caption': 'Amazing product at Shopintoit:' + product_name,
                'description': 'Checkout this amazing product '+ product_name,
                'link': share_url,
                'picture': product_img ,
                'content': '32x32-facebook.png',
                'button_icon': null
            });
        });
        $(".tgt_fbfeed_button_2 a").click(gat_handler("social-share-product", {label:  "Facebook"}));
    });

    /* twitter tweet button*/
    shopinterest.use('modules-tweet_button', 'templates-tweet_button', function(shopinterest) {

        $.each($('.tgt_tweet_button_2'), function(i, elem) {

            var elem_obj = $(elem);
            var product_img = $('.orbit-slides-container img').attr('src');
            var product_name = $('h2.product-item-name').text();

            var tweet_button = new shopinterest.modules.tweet_button();

            tweet_button.render(elem_obj, {
                'url': share_url,
                'via': 'shopintoit',
                'text': 'Checkout this amazing product '+product_name+' at Shopintoit',
                'content': '32x32-twitter.png',
                'button_icon': null
            });

        });
        $(".tgt_tweet_button_2 a").click(gat_handler("social-share-product", {label:  "Twitter"}));
    });


    /* pinterest pin button*/
    shopinterest.use('modules-pin_button', 'templates-pin_button', function(shopinterest) {

        $.each($('.tgt_pin_button_2'), function(i, elem) {
            var elem_obj = $(elem);
            var product_img = $('.orbit-slides-container img').attr('src');
            var product_name = $('h2.product-item-name').text();

            var pin_button = new shopinterest.modules.pin_button();

            pin_button.render(elem_obj, {
                'url': share_url,
                'img_url': product_img,
                'description': 'Checkout this amazing product ' + product_name + ' at Shopintoit ',
                'content': '32x32-pinterest.png',
                'button_icon': null
            });
        });
        $(".tgt_pin_button_2 a").click(gat_handler("social-share-product", {label:  "Pinterest"}));
    });

    $(".store-link").click(gat_handler("store-view-common", {label:  "STORE - from product item page"}));
    $(".cart-link").click(gat_handler("cart-view-common", {label:  "STORE - from product item page"}));
};
