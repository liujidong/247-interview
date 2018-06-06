shopinterest.controllers.admin_stores = new function() {
    
    var set_payment_solution = function() {
        $('.payment_solution').unbind('click');
        $('.payment_solution').bind('click', function(e){
            //e.preventDefault();
            var _this = $(this);
            var store_id = _this.attr('store_id');
            var transaction_fee_waive_box = _this.closest('tr').find('.transaction_fee_waived');
            var isChecked = _this.attr('checked');            
            
            $.post('/api/setpayment',{store_id:store_id, payment_solution:isChecked?1:0},function(response){
                var res = $.parseJSON(response);
                if(res.status === 'success') {
                    transaction_fee_waive_box.attr('disabled', !isChecked);
                    if(!isChecked){
                        transaction_fee_waive_box.attr('checked',false);
                    }
                }                    
            });
        });        
    };  
    set_payment_solution();
    
    var transaction_fee_waived = function() {
        $('.transaction_fee_waived').unbind('click');
        $('.transaction_fee_waived').bind('click', function(e){
            //e.preventDefault();
            var _this = $(this);
            var store_id = _this.attr('store_id');
            var isChecked = _this.attr('checked');  
            $.post('/api/settransactionfee',{store_id:store_id, transaction_fee_waived:isChecked?1:0},function(){

            });
        });        
    };  

    transaction_fee_waived();

    $('.exclude_in_search').click(function(){
        var _this = $(this);
        var value = _this.attr("checked") ? 1 : 0;
        var store_id = _this.attr("store_id");
        //console.log(store_id);console.log(product_id);
        $.post('/api/excludeproduct', {store_id: store_id, exclude_in_search: value}, function(){});
    });
    
    $('.allow_resell').change(function(){
        var _this = $(this);
        var value = _this.val();
        var store_id = _this.attr("store_id");
        $.post('/api/allowresell', {store_id: store_id, allow_resell: value}, function(){});
    });
    
    $('.merchant_email').on('keypress', function(e) {
        
        var _this = $(this);        
        var current_email = _this.attr('email');
        var new_email = _this.val();
       
        if(e.keyCode == 13 && new_email !== '' && current_email !== new_email) {
            $.post(
                '/api/updatemerchantemail', 
                {current_email: current_email, new_email: new_email}, 
                function(response){
                    var res = $.parseJSON(response);
                    if(res.status === 'success') {
                        _this.attr('email', new_email);
                        alert("Success");
                    } else {
                        alert("Failure: please make sure the input email is correct and haven't been in use");
                    }                    
                }
            );            
        }
    });    
};
