shopinterest.controllers.selling_preview = new function() {    

    $(".launch-store-x").click(function(){
        var action = $(this).hasClass("unlaunch") ? "false" : "true";
        $.post('/api/launch-store', {
            launch: action
        }, function(response) {
            response = $.parseJSON(response);
            if(response.status === 'success') {
                window.location.href = "/store/" + $("#subdomain").val();
            } else {
                alert("Can not change your store status!");
            }
        });
    });    
};
