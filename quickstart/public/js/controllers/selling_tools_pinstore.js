shopinterest.controllers.me_settings = (function() {

    var utils = shopinterest.common.utils,
        post = utils.post,
        substitute = utils.substitute,
        spinner = utils.spinner,
        plogin_url = shopinterest.constants.base_service_url+'/plogin',
        uploadpins_url = shopinterest.constants.base_service_url+'/uploadpins',
        pinterest_account = null,
        pinterest_boards = null,
        $pininfo = $('.pinterest-info'),
        $selectboard = $('.select-board'),
        $nameboard = $('.name-board'),
        $pinning = $('.pinning'),
        board_options_tpl = "{{#boards}}<option value={{id}}>{{name}}</option>{{/boards}}";

    $('.pinterest-info').on('invalid', function(e) {

    }).on('valid', function(e) {
        var _this = $(this);
        var user = utils.get_post_data(_this);
        spinner.show();
        post(
            plogin_url,
            {pinterest_email: user.email, pinterest_password: user.password},
            function(response_obj){
                spinner.close();
                if(response_obj.status === false) {
                    // show error msg
                    utils.alertBox({
                        container: $('.alert-field'),
                        type: 'error',
                        message: response_obj.data.error_msg
                    });
                } else {
                    $pininfo.hide();
                    
                    pinterest_account = response_obj.data.account;
                    pinterest_boards = response_obj.data.boards;

                    // show the create board form
                    var data = {boards: pinterest_boards};
                    var html = substitute(board_options_tpl, data);console.log(board_options_tpl, data, html);
                    $selectboard.find('#board_options').append(html);
                    $selectboard.fadeIn('100');
                }
            }
        );
    });
    
    // show select a pinterest board
    $('.show-selectboard').click(function() {
        $nameboard.hide();
        $selectboard.fadeIn('100');
    });

    // show name a pinterest board
    $('.show-nameboard').click(function() {
        $selectboard.hide();
        $nameboard.fadeIn('100');
    });

    // submit name a board
    $('.submit-nameboard').click(function() {
        post(uploadpins_url, {pinterest_boardname: $('#boardname').val()}, function(response_obj) {
            $nameboard.hide();
            if(response_obj.status === false) {
                // show error msg
                utils.alertBox({
                    container: $('.alert-field'),
                    type: 'error',
                    message: 'Error on creating a new board'
                });
            } else {
                $pinning.fadeIn('100');
            }
        });
        return false;
    });

    // submit select a board
    $('.submit-selectboard').click(function() {
        post(uploadpins_url, {pinterest_board_id: $('#board_options').val()}, function(response_obj) {
            $selectboard.hide();
            if(response_obj.status === false) {
                // show error msg
                utils.alertBox({
                    container: $('.alert-field'),
                    type: 'error',
                    message: 'Upload products to Pinterest error, try again...'
                });
            } else {
                $pinning.fadeIn('100');
            }
        });
        return false;
    });

    // pin more
    $('.pin-more').click(function() {
        $pinning.hide();
        $pininfo.fadeIn('100');
    });

})();
