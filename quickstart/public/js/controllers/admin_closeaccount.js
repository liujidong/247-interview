
shopinterest.controllers.admin_closeaccount = new function() { 
    
    $('.close-account').click(function(){
        var _this = $(this);
        var user_id = _this.closest('tr').find('td :first').html();
        var status = _this.attr('checked') ? 1 : 0;
        $.post('/api/updateaccountstatus',{user_id: user_id, status:status});
    });
    
};