shopinterest.controllers.auction_index = new function() {

    /* facebook feed button*/
    shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {

        $.each($('.tgt_fbfeed_button_2'), function(i, elem) {
            var fbfeed_button = new shopinterest.modules.fbfeed_button();
            var elem_obj = $(elem);
            var auction = elem_obj.closest('.auction-box');
            var init_price = '$' + auction.find('.init_price').val();
            var product_img = auction.find('.auction-picture-holder img').attr('src');
            var product_name = auction.find('.auction-product-name').text().trim();

            fbfeed_button.render(elem_obj, {
                'name': 'Check out the amazing auctions at Shopinterest Auctions',
                'caption': 'Auction starts at ' + init_price + ' for ' + product_name,
                'description': 'Checkout this amazing auction '+product_name+' starts at '+ init_price,
                'link': window.location.href,
                'picture': product_img,
                'button_icon': {
                    'img_src': '/img/32x32-facebook.png',
                    'width': 32,
                    'height': 32
                }
            });
        });
        $(".tgt_fbfeed_button_2 a").click(gat_handler("social-share-auction", {label:  "Facebook"}));
    });

    /* twitter tweet button*/
    shopinterest.use('modules-tweet_button', 'templates-tweet_button', function(shopinterest) {

        $.each($('.tgt_tweet_button_2'), function(i, elem) {

            var elem_obj = $(elem);
            var auction = elem_obj.closest('.auction-box');
            var init_price = '$' + auction.find('.init_price').val();
            var product_img = auction.find('.auction-picture-holder img').attr('src');
            var product_name = auction.find('.auction-product-name').text().trim();
            var tweet_button = new shopinterest.modules.tweet_button();

            tweet_button.render(elem_obj, {
                'url': window.location.href,
                'via': 'shopinterest',
                'text': 'Checkout this amazing auction '+product_name+' starts at '+ init_price,
                'button_icon': {
                    'img_src': '/img/32x32-twitter.png',
                    'width': 32,
                    'height': 32
                }
            });

        });
        $(".tgt_tweet_button_2 a").click(gat_handler("social-share-auction", {label:  "Twitter"}));
    });


    /* pinterest pin button*/
    shopinterest.use('modules-pin_button', 'templates-pin_button', function(shopinterest) {

        $.each($('.tgt_pin_button_2'), function(i, elem) {

            var elem_obj = $(elem);
            var auction = elem_obj.closest('.auction-box');
            var init_price = '$' + auction.find('.init_price').val();
            var product_img = auction.find('.auction-picture-holder img').attr('src');
            var product_name = auction.find('.auction-product-name').text().trim();
            var pin_button = new shopinterest.modules.pin_button();

            pin_button.render(elem_obj, {
                'url': window.location.href,
                'img_url': product_img,
                'description': 'Checkout this amazing auction '+product_name+' starts at '+ init_price,
                'button_icon': {
                    'img_src': '/img/32x32-pinterest.png',
                    'width': 32,
                    'height': 32
                }
            });
        });
        $(".tgt_pin_button_2 a").click(gat_handler("social-share-auction", {label:  "Pinterest"}));
    });


    if($("#container").attr("active") != 1) return;

    var auctions = $(".auction-list-item");
    var auction_ids = [];
    auctions.each(function(i,a){
        auction_ids.push($(a).attr("id").replace("auction_", ""));
    });
    auction_ids = auction_ids.join(',');

    function refresh_current_bid_info(){
        $.post('/api/auctioncurrentbidprices', {ids: auction_ids}, function(response) {
            response = $.parseJSON(response);
            if(response.status != 'success') {
                return;
            }
            for(var i in response.data){
                var info=response.data[i];
                var str_bids = info.bid_times > 1 ? " bids" : " bid";
                var html = info.currency_symbol + info.current_bid_price + " <small>( " + info.bid_times + str_bids + " )</small>";
                var target = $("#auction_" + info.id + " .current_bid_info strong");
                var next_price = parseFloat(info.current_bid_price) + parseFloat(info.min_bid_increment);
                var text = $("#auction_" + info.id + " .bid_price");
                text.attr("placeholder", next_price);
                target.empty();
                target.append(html);
            }
        });
    }

    setInterval(refresh_current_bid_info, 2000);

    $(".time_countdown").each(function() {
        var _this = $(this);
        var time_end = _this.closest(".auction-detail").find(".auction_end_time").text();
        var box = _this.closest(".auction-list-item");
        var countdown = new shopinterest.modules.time_countdown();
        var end_cb = function() {
            box.fadeOut();
        };
        countdown.render(_this, time_end, 1000, end_cb,$("#time_now").val());
    });
};
