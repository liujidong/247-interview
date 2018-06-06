
shopinterest.modules.pinpicker_selectboards = function(pinpicker_uploader_in) {
    
    var module_name = 'picker_files';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var container = $('#'+module_name);
    var _this = this;
    var pinpicker_uploader = pinpicker_uploader_in;
    
    var get_boards = function(pinterest_username, callback) {
        $.post('/api/getboards', {pinterest_username: pinterest_username}, function(response) {
            callback($.parseJSON(response));
        });
    }
    
    _this.render = function(pinterest_username) {
        var template = shopinterest.templates.pinpicker_selectboards;
        // get pins and render them
        get_boards(pinterest_username, function(board_info) {
            var html = substitute(template, board_info);
            container.html(html);
            bindUI();
        });
        
    };
    
    
    
    var bindUI = function() {
        container.find('li a').click(function(e) {
            e.preventDefault();
            var that = $(this);
            var board_id = that.attr('board-id');
            $('.picker_files_header').hide();
            $('#picker_files').hide();

            // show the pinpicker_selectpins
            var pinpicker_selectpins = new shopinterest.modules.pinpicker_selectpins(pinpicker_uploader);
            pinpicker_selectpins.render(board_id);
            $('#pinlist').show();
            $('#pinlist-spacer').show();
        });
        
        
        
    };
    
    
};


