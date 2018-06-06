shopinterest.controllers.associate_products = new function() {

    shopinterest.common.utils.toggle_code_section();

    $('.section .add').click(function (e) {
        $(this).closest('.section').addClass('incart');
    });

    $('.remove').click(function(e){
        e.preventDefault();
        var _this = $(this);
        $.post('/api/removesn', {store_id: _this.attr('store_id'), product_id: _this.attr('product_id')}, function(response) {
            var response_obj = $.parseJSON(response);
            if(response_obj.status === 'success') {
                _this.closest('.section').remove();
                //innetwork.show();
            }
        });            
    }); 
    
    /* facebook feed button*/
    shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {
        $.each($('.fb'), function(i, elem) {
            var elem_obj = $(elem);         
            var section = elem_obj.closest('.section');
            var product_name = section.find('.product_name').val();
            var product_img = section.find('.prodimg img').attr('src');
            var url = section.find('.share_url').val();          
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
    }); 
    
    /* twitter tweet button*/
    shopinterest.use('modules-tweet_button', 'templates-tweet_button', function(shopinterest) {
        $.each($('.tw'), function(i, elem) {
            var elem_obj = $(elem);  
            var section = elem_obj.closest('.section');
            var product_name = section.find('.product_name').val();
            var product_img = section.find('.prodimg img').attr('src');  
            var url = section.find('.share_url').val();     
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
    });  
    
    /* pin share button*/    
    shopinterest.use('modules-pin_button', 'templates-pin_button', function(shopinterest) {
        $.each($('.pn'), function(i, elem) {
            var elem_obj = $(elem);  
            var section = elem_obj.closest('.section');
            var product_name = section.find('.product_name').val();
            var product_img = section.find('.prodimg img').attr('src');   
            var url = section.find('.share_url').val();  
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
}
