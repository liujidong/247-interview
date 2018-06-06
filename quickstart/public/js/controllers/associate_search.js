shopinterest.controllers.associate_search = new function() {
    var query = $('#sn_search').val();
//    var price_range = $('');
//    var commission_range = $('');
    var sn_search_button = $('#sn_search_button');

    sn_search_button.click(function(e){
        //e.preventDefault();
        
    });
    $('.add2sn').click(function(){
        var _this = $(this);
        $.post('/api/add2sn', {store_id: _this.attr('store_id'), product_id: _this.attr('product_id')}, function(response) {
            var response_obj = $.parseJSON(response);
            if(response_obj.status === 'success') {
                _this.closest('.section').remove();
                //window.location.reload();
                //innetwork.show();
            }
        });            
    }); 
    
}
