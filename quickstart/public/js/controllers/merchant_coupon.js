
shopinterest.controllers.merchant_coupon = new function() {
    
    
    var parse_product_id_from_url = function(product_url) {
        var my_store_url = $('#my_store_url').val().split('//')[1];
        var re = new RegExp(my_store_url+"/products/item\\?id=(\\d+)$");
        var result = product_url.match(re);
        if(result === null) {
            alert("Failure: please check the product url.");
            return;
        }
        return result[1];
    };
    
    if(site_version === 2) {
        $('#start_time').datepicker({ dateFormat: "yy-mm-dd" });
        $('#end_time').datepicker({ dateFormat: "yy-mm-dd" });
        
        
        $('#search_btn').click(function() {
            
            var product_url = $('#input_url').val();
            $('#input_url_hidden').val(product_url);
            var product_id = parse_product_id_from_url(product_url);
            $('#product_id').val(product_id);
            $('.product_id_label').html(product_id);
            var scope_val = product_id ? 3 : 2;
            $('#scope').val(scope_val);
        });
        
        $('.delete').click(function(){
            
            if(confirm("Sure you want to delete this coupon?")) {
                var _this = $(this);           
                var tr = _this.closest('tr');

                $.post('/api/deletecoupon', {code: _this.attr('code')}, function(response){
                    var res = $.parseJSON(response);
                    if(res.status === 'success') {
                        tr.remove();
                    }                  
                });
            }            
        });
    }
};