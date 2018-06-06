shopinterest.controllers.iframe_pinpicker = new function() {
    
    // render the pinpicker uploader section
    var pinpicker_uploader = new shopinterest.modules.pinpicker_uploader();
    pinpicker_uploader.render($('#preview'));
    
    var pinpicker_lightbox = window.parent.$('#pinpicker-lightbox');
    
    // render the pinpicker_selectboards
    var pinterest_username = $('.pinterest_username a').html();
    var pinpicker_selectboards = new shopinterest.modules.pinpicker_selectboards(pinpicker_uploader);
    pinpicker_selectboards.render(pinterest_username);
    
    // go to select boards page
    $('.pinterest_username a').click(function(e) {
        $('#pinlist').hide();
        $('#pinlist-spacer').hide();
        $('.picker_files_header').show();
        $('#picker_files').show();
    });
    
    $('.close-modal').click(function(e) {
        pinpicker_lightbox.trigger('pinpicker:close');
    });
    
    $('.pinterest_username input').blur(function(e) {
        var pinterest_username = $.trim($(this).val());
        if(pinterest_username === '') {
            return;
        }
        
        // save the pinterest username
        $.post('/api/updatepinterestusername', {pinterest_username: pinterest_username}, function(response) {
            
            var response_obj = $.parseJSON(response);
            if(response_obj.status === 'success') {
                // get the list of boards
                
                pinpicker_selectboards.render(pinterest_username);
                $('.pinterest_username_input').hide();
                $('.pinterest_username').append("<a class=\"pinterest_username_anchor\">"+pinterest_username+"</a>");
                $('.pinterest_username a').click(function(e) {
                    $('#pinlist').hide();
                    $('#pinlist-spacer').hide();
                    $('.picker_files_header').show();
                    $('#picker_files').show();
                });
            }
        });
    });
    
    
    var get_boards = function(pinterest_username) {
        
        $.post('/api/getboards', {pinterest_username: pinterest_username}, function(response) {
            var response_obj = $.parseJSON(response);
            console.log(response_obj);
        });
        
        
    };

    $(document).bind('pinpicker_upload:start', function(e) {
//        console.log('pinpicker_upload:start');
        pinpicker_lightbox.trigger('pinpicker_upload:start'); 
    });  
    
    $(document).bind('pinpicker_upload:finish', function(e) {
//        console.log('pinpicker_upload:finish');
        pinpicker_lightbox.trigger('pinpicker_upload:finish');        
    });  
}
