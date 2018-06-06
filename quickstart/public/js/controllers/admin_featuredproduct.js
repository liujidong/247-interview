shopinterest.controllers.admin_featuredproduct = new function() {
    
    var update_featuredproduct = function(store_id, product_id, score) {
        $.post('/api/updatefeaturedproduct',{store_id:store_id, product_id:product_id, score:score});
    };

    var remove_featuredproduct = function(store_id, product_id, callback) {
        $.post('/api/updatefeaturedproduct',{store_id:store_id, product_id:product_id, featured:0},function(response) {
            var res = $.parseJSON(response);
            if(res.status === 'success' && callback) {
                if($.isFunction(callback)) {
                    callback();
                }                
            }
        });
    };
    
    $('.delete_featuredproduct').on('click', function() {   
        var _this = $(this);  
        var container = _this.closest('tr');        
        var store_id = container.find('.store_id').html();
        var product_id = container.find('.product_id').html();
        var user_cfm = confirm("Do you really want to delete product " + product_id + " from featured products?");
        if(!user_cfm) return;
        remove_featuredproduct(store_id, product_id, function(){
            container.remove();
        });
    });
    
    $('.edit_score').on('keypress', function(e) {
        var _this = $(this);
        var container = _this.closest('tr');    
        if(e.keyCode == 13) {
            var store_id = container.find('.store_id').html();
            var product_id = container.find('.product_id').html();
            var score = _this.val();
            update_featuredproduct(store_id, product_id, score);
        }
    });    
    
}
