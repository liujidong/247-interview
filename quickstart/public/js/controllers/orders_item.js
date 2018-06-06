shopinterest.controllers.orders_item = new function() {

    shopinterest.use('modules-shiptrack_lightbox', 'templates-shiptrack_lightbox', function(shopinterest){
        
        var shiptrack_lightbox = new shopinterest.modules.shiptrack_lightbox();
        shiptrack_lightbox.render($('#tgt_shiptrack_lightbox'), $.query.get('id'));          
        $('#fulfill_order').click(function(e){
            e.preventDefault();
            shiptrack_lightbox.show();
        });
        
        $('#tgt_shiptrack_lightbox').bind('fullfill:remove', function(e) {
            
            $('#fulfill_order').remove();
            
            $('.order-info').before('<div class="alert-box success row">\n\
                                            The order status has been updated as shipped.\n\
                                            <a href="" class="close">&times;</a>\n\
                                            </div>');  
        });
        
    });
    
}
