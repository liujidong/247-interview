shopinterest.controllers.admin_payhistory = new function() {

    var update_payment_status = function(payment_id, status, callback) {
        $.post('/api/updatepaymentstatus',{payment_id:payment_id,status:status},function(response) {
            var res = $.parseJSON(response);
            if(res.status === 'success' && callback) {
                if($.isFunction(callback)) {
                    callback();
                }                
            }
        });
    };

    var delete_payment = function(payment_id, callback) {
        update_payment_status(payment_id, 127, callback);
    };
    
    $('.edit_status').on('keypress', function(e) {
        var _this = $(this);
        if(e.keyCode ==13) {
            var payment_id = _this.closest('tr').find('td :first').html();
            var status = _this.val();
            update_payment_status(payment_id, status);
        }
    });
    
    $('.delete_payment').on('click', function() {
        var _this = $(this);  
        var payment_id = _this.closest('tr').find('td :first').html();
        delete_payment(payment_id,function(){
            _this.closest('tr').remove();
        });
    });
    
}
