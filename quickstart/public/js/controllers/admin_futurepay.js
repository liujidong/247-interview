shopinterest.controllers.admin_futurepay = new function() { 
    
    var update_payment_status = function(payment_item_id, status) {
        $.post('/api/updatepaymentitemstatus',{payment_item_id:payment_item_id,status:status},function(response){
            
        });
    };

    $('.edit_status').on('keypress', function(e) {
        var _this = $(this);
        if(e.keyCode ==13) {
            var payment_item_ids = _this.closest('tr').find('td :first').html();
            var status = _this.val();
            update_payment_status(payment_item_ids, status);
        }
    });
    
    $('.creatmasspay').click(function() {
        window.location = '/api/createmasspay';
    });
    
}
