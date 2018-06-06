shopinterest.controllers.auction_item = new function() {

    // click thumb picture to change the large image url
    $('.auction-picture-list img').click(function(){
        var _this = $(this);
        var large_image_url = _this.attr('large_image_url');
        var holder = _this.closest('.auction-picture').find('.auction-picture-holder img');
        holder.attr('src', large_image_url);
        _this.closest('.auction-picture-list').removeClass('thumb_pic_selected');
        _this.addClass('thumb_pic_selected');
    });

    /* facebook feed button*/
    shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {

        $.each($('.tgt_fbfeed_button_2'), function(i, elem) {
            var fbfeed_button = new shopinterest.modules.fbfeed_button();
            var elem_obj = $(elem);
            var auction = elem_obj.closest('.auction-box');
            var init_price = auction.find('.init_price').text();
            var product_img = auction.find('.auction-picture-holder img').attr('src');
            var product_name = auction.find('h1').text();

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
            var init_price = auction.find('.init_price').text();
            var product_img = auction.find('.auction-picture-holder img').attr('src');
            var product_name = auction.find('h1').text();
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
            var init_price = auction.find('.init_price').text();
            var product_img = auction.find('.auction-picture-holder img').attr('src');
            var product_name = auction.find('h1').text();
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

    $(".bid_button").click(function(event){
        gat(event, "auction-bid");
        var current_bid = parseFloat($(".current_bid_price", $(this).parent()).val());
        var min_incr = parseFloat($(".min_bid_increment", $(this).parent()).val());
        var auction_id = $(".auction_id", $(this).parent()).val();

        var bid_price = $(".bid_price", $(this).parent().parent()).val();
        if(!/^\d+(\.\d\d?)?$/.test(bid_price)) {
            alert("Please enter a decimal number (like 5.70) as your bid price!");
            return;
        }
        bid_price = parseFloat(bid_price);
        if(bid_price < current_bid + min_incr){
            alert("Please provide a valid bid price(more than current bid price);");
            return;
        }
        $.post('/api/bidauction', {
            auction_id: auction_id,
            my_bid_price: bid_price,
            current_bid_price: current_bid
        }, function(response) {
                response = $.parseJSON(response);
                if(response.status === 'success') {
                    alert("Bid Success!");
                } else {
                    if(response.err === "selfbid") {
                        alert("You can not bid on your own auction!");
                    } else {
                        alert("Bid Error, Please try again after the page reload!");
                        window.location.reload();
                    }
                }
        });
    });

    var auctions = $(".auction-box");
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
                if($("#auction_" + info.id).length < 1) continue;
                var str_bids = info.bid_times > 1 ? " bids" : " bid";
                var html = info.currency_symbol + info.current_bid_price + " <small>( " + info.bid_times + str_bids + " )</small>";
                var target = $("#auction_" + info.id + " .current_bid_info strong");
                var next_price = parseFloat(info.current_bid_price) + parseFloat(info.min_bid_increment);
                var text = $("#auction_" + info.id + " .bid_price");
                $("#auction_" + info.id + " .current_bid_price").val(info.current_bid_price);
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
        var countdown = new shopinterest.modules.time_countdown();
        var end_cb = function() {
            var btn = _this.closest(".auction-detail").find(".bid_button");
            btn.val("Bid Ended");
            btn.attr('disabled', true);
            btn.css({"background-color": "#C0B3AE", "border-color": "#C0B3AE"});
            var text = _this.closest(".auction-detail").find(".bid_price");
            text.val('');
            text.attr('placeholder', '');
            text.attr('disabled', true);
            var pics = _this.closest(".auction-box").find(".auction_picture");
            pics.css("opacity", "0.4");
        };
        var end_cb2 = function() {
            window.location.reload();
        };
        countdown.render(_this, time_end, 1000, end_cb2, $("#time_now").val());
    });

};
