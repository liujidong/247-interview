shopinterest.controllers.admin_categorizing = new function() {
    
    $('.product_category').change(function(){
        var _this = $(this);
        var store_id = _this.closest('tr').find('.store_id').val();
        var product_id = _this.closest('tr').find('.product_id').val();
        var global_category_id = _this.val();
        //console.log(store_id);console.log(product_id);
        $.post('/api/categorizing', {store_id: store_id, product_id: product_id, global_category_id: global_category_id}, function(){});
    });

    $('.exclude_in_search').click(function(){
        var _this = $(this);
        var value = _this.attr("checked") ? 1 : 0;
        var store_id = _this.closest('tr').find('.store_id').val();
        var product_id = _this.closest('tr').find('.product_id').val();
        //console.log(store_id);console.log(product_id);
        $.post('/api/excludeproduct', {store_id: store_id, product_id: product_id, exclude_in_search: value}, function(){});
    });

}
