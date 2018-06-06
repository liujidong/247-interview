shopinterest.controllers.admin_auction = new function() {
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
        post_data = {
            search_url: encodeURIComponent($('#url').val())
        };
        //test url http://redbookmag584.shopinterest.co/products/item?id=9
        $.ajax({
            url : '/api/getflashdealdetails',
            type : 'POST',
            data : post_data,
            success: function(response){
                response_obj = $.parseJSON(response);
                data = response_obj.data;
                $('#store_id').val(data.store_id);
                $('.store_id_label').html(data.store_id);
                $('#product_id').val(data.product_id);
                $('.product_id_label').html(data.product_id);
            }
        });
    })

    $(".active_auction").on('click', function() {
        var id = $(this).parent().parent().attr('data-id');
        var _this = $(this);
        $.post('/api/updateauctionstatus', {auction_id: id, status: 2}, function(response) {
            response = $.parseJSON(response);
            if(response.status === 'success') {
                window.location.reload();
            } else {
                $('.alert-box.error').show();
            }
        });
    });

    $(".deactive_auction").on('click', function() {
        var id = $(this).parent().parent().attr('data-id');
        var _this = $(this);
        $.post('/api/updateauctionstatus', {auction_id: id, status: 1}, function(response) {
            response = $.parseJSON(response);
            if(response.status === 'success') {
                window.location.reload();
            } else {
                $('.alert-box.error').show();
            }
        });
    });

    $(".delete_auction").on('click', function() {
        var id = $(this).parent().parent().attr('data-id');
        var _this = $(this);
        $.post('/api/updateauctionstatus', {auction_id: id, status: 127}, function(response) {
            response = $.parseJSON(response);
            if(response.status === 'success') {
                var tr = _this.closest('tr');
                tr.remove();
            } else {
                $('.alert-box.error').show();
            }
        });
    });

}
