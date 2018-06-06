shopinterest.controllers.affiliatestore_index = new function() {
     
    /* facebook feed button*/
    shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {
        $.each($('.fb'), function(i, elem) {
            var elem_obj = $(elem);  
            var row = elem_obj.closest('.row');
            var product_name = row.find('.product_name').val();
            var product_img = row.find('.prodimg_img').attr('src');  
            var url = row.find('.share_url').val();               
            var fbfeed_button = new shopinterest.modules.fbfeed_button();

            fbfeed_button.render(elem_obj, {
                'name': 'Look at this amazing product at the shopinterest store',
                'caption': product_name,
                'description': 'Share this amazing product '+product_name,
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
            var row = elem_obj.closest('.row');
            var product_name = row.find('.product_name').val();
            var product_img = row.find('.prodimg_img').attr('src');  
            var url = row.find('.share_url').val();     
            var tweet_button = new shopinterest.modules.tweet_button();

            tweet_button.render(elem_obj, {
                'url': url,
                'via': 'shopinterest',
                'text': 'Checkout this amazing product '+product_name,
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
            var row = elem_obj.closest('.row');
            var product_name = row.find('.product_name').val();
            var product_img = row.find('.prod_img').attr('src');  
            var url = row.find('.share_url').val();     
            var pin_button = new shopinterest.modules.pin_button();

            pin_button.render(elem_obj, {
                'url': url,
                'img_url': product_img,
                'description': 'Checkout this amazing product '+product_name,
                'button_icon': {
                    'img_src': '/img/32x32-pinterest.png',
                    'width': 32,
                    'height': 32
                }
            });
        });   
    });    
 
     /* facebook send button*/
   shopinterest.use('modules-fbsend_button', 'templates-fbsend_button', function(shopinterest) {
         $.each($('.tgt_fbsend_button'), function(i, elem) {
            var fbsend_button = new shopinterest.modules.fbsend_button(); 
            var elem_obj = $(elem);  
            var row = elem_obj.closest('.row');
            var product_name = row.find('.product_name').val();
            var product_img = row.find('.prodimg_img').attr('src');  
            var url = row.find('.share_url').val();     

            fbsend_button.render(elem_obj, {
                'name': 'Check out this amazing product.',
                'caption': product_name,
                'description': 'I just found an amazing product. I\'m sure you will like the perfect gift! Check it out, you\'ll love it.',
                'link': url,
                'picture': product_img,
                'button_icon': {
                    'img_src': '/img/emailus.jpg'
                }
            });
        });   
    });  
}
