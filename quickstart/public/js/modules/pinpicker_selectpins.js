
shopinterest.modules.pinpicker_selectpins = function(pinpicker_uploader_in) {
    
    var module_name = 'pinpicker_selectpins';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var container = $('#'+module_name);
    var _this = this;
    var board_id = 0;
    var next_page_url = '';
    var template = shopinterest.templates.pinpicker_selectpins;
    var in_process = false;
    var pinpicker_uploader = pinpicker_uploader_in;
    var page_num = 0;

    _this.render = function(board_id_in) {
        board_id = board_id_in;
        
        // get pins and render them
        get_pins(function(pin_info) {
            var html = substitute(template, pin_info);
            container.html(html);
            bindUI();
        });
        
    };
    
    var get_pins = function(callback) {
        $.post('/api/getpins', {board_id: board_id, next_page_url: next_page_url}, function(response) {
            var response_obj = $.parseJSON(response);
            next_page_url = $.trim(response_obj.next_page_url);
            in_process = false;
            page_num++;
            response_obj.page_num = page_num;
            callback(response_obj);
        });
    }
    
    container.scroll(function(e) {
        var position = parseInt(container.scrollTop());
        if(position > 900) {
            if(next_page_url !== '' && !in_process) {
                in_process = true;
                get_pins(function(pin_info) {
                    var html = substitute(template, pin_info);
                    container.append(html);
                    bindUI();
                });
            }
        }
    });
    
    var bindUI = function() {
        
        // hover to a pin
        $(substitute('li[page-num="{{page_num}}"] .pin-container', {page_num: page_num})).hover(function(e) {
            var _this = $(this);
            var overlay_add_pin = _this.find('.overlay-image.add-pin');
            var overlay_pin_added = _this.find('.overlay-image.pin-added');
            var overlay_remove_pin = _this.find('.overlay-image.remove-pin');

            if(!_this.hasClass('selected')) {
                overlay_add_pin.show();
            } else {
                overlay_pin_added.hide();
                overlay_remove_pin.show();
            }


            }, 
            function(e) {

            var _this = $(this);
            var overlay_add_pin = _this.find('.overlay-image.add-pin');
            var overlay_pin_added = _this.find('.overlay-image.pin-added');
            var overlay_remove_pin = _this.find('.overlay-image.remove-pin');

            if(!_this.hasClass('selected')) {
                overlay_add_pin.hide();
            } else {
                overlay_remove_pin.hide();
                overlay_pin_added.show();
            }
        });

        // click a pin
        $(substitute('li[page-num="{{page_num}}"] .pin-container', {page_num: page_num})).click(function() {
            var _this = $(this);
            var overlay_add_pin = _this.find('.overlay-image.add-pin');
            var overlay_pin_added = _this.find('.overlay-image.pin-added');
            var overlay_remove_pin = _this.find('.overlay-image.remove-pin');
            var pin_url = _this.find('.pin45_url').attr('src');
            var pin_description = _this.siblings('h5').html();
            var pin_id = _this.find('.pin45_url').attr('pin-id');

            if(!_this.hasClass('selected')) {
                overlay_add_pin.hide();
                overlay_pin_added.show();
                _this.addClass('selected');
                // add a photo
                pinpicker_uploader.add_pin(pin_id, pin_url, pin_description);


            } else {
                overlay_pin_added.hide();
                overlay_remove_pin.hide();
                _this.removeClass('selected');
                // remove a photo
                pinpicker_uploader.remove_pin(pin_id);
            }

        });
    }
};


