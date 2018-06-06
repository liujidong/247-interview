shopinterest.controllers.admin_flashdeal = new function() {
    AnyTime.picker( "start_time",
            {   format: "%Y-%m-%d %H:%i:00",
                askSecond:false
            } 
    );
    
    AnyTime.picker( "end_time",
            {   format: "%Y-%m-%d %H:%i:00",
                askSecond:false
            } 
    );
    
    $('.search_btn').on('click', function(e){
        
        e.preventDefault();
        var post_data;
        if(this.id.indexOf("coupon") == -1){
            value_tag = 'url';
            post_data = {
                search_url: encodeURIComponent($('#'+value_tag).val())
            };
            //test url http://redbookmag584.shopinterest.co/products/item?id=9
        }
        else{
            value_tag = 'code';
            post_data = {
                    coupon_code: $('#'+value_tag).val()
            };
        }
        $.ajax({
            url : '/api/getflashdealdetails',
            type : 'POST',
            data : post_data,
            success: function(response){
                response_obj = $.parseJSON(response);
                data = response_obj.data;
                $('#store_id').val(data.store_id);
                $('#product_id').val(data.product_id);
                //$('#store_id').val(data.store_id);
                $('#deal_status').val(data.status);
                $('#scope').val(data.scope);
                $('#start_time').val(data.start_time);
                $('#end_time').val(data.end_time);
                
                $('#price_offer_type').val(data.price_offer_type);
                $('#shipping_offer_type').val(data.shipping_offer_type);
                $('#quantity').val(data.usage_limit);
                $('#quantity').val(data.usage_limit);
                $('#description').val(data.offer_description);
                $('#description').val(data.offer_description);
                if(data.code != undefined)
                    $('#coupon_code').val(data.code);
                if(data.offer_details != undefined && data.offer_details != ''){
                    var offer_details = $.parseJSON(data.offer_details);
                    if(offer_details.price != undefined){
                        if(offer_details.price.flat_value_off != undefined)
                            $('#price_offer_value').val(offer_details.price.flat_value_off);
                        if(offer_details.price.percentage_off != undefined)
                            $('#price_offer_value').val(offer_details.price.percentage_off);
                    }
                    if(offer_details.shipping != undefined){
                        if(offer_details.shipping.flat_value_off != undefined)
                            $('#shipping_offer_value').val(offer_details.shipping.flat_value_off);
                        if(offer_details.shipping.percentage_off != undefined)
                            $('#shipping_offer_value').val(offer_details.shipping.percentage_off);
                    }
                }
            }
        });
    })

}
