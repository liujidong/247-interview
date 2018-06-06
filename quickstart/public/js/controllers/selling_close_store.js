shopinterest.controllers.selling_close_store = new function() {

    $("input[name='close-store']").click(function(e){
        e.preventDefault();
        if(!confirm("Are you really sure to delete you store?")){
            return false;
        }
        $.post('/api/close-store', {}, function(response) {
            response = $.parseJSON(response);
            if(response.status === 'success') {
                window.location.href = "/dashboard/";
            } else {
                alert("Can not delete your store!");
            }
        });
    });
};
