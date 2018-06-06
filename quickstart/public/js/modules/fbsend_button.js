
shopinterest.modules.fbsend_button = function() {
    
    var module_name = 'fbsend_button';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var fbapi = shopinterest.libs.fbapi;
    var msg = null;
    var button_icon = '';
    
    _this.render = function(tgt, msg_in) {
        msg = msg_in;
        button_icon = msg.button_icon;
        var template = shopinterest.templates.fbsend_button;
        var html = substitute(template, {id: id, img_src: button_icon.img_src, width: button_icon.width, height: button_icon.height}); 
        tgt.html(html);
        container = $('#'+id);
        bindUI();
    };
    
    var bindUI = function() {
        container.on('click', 'img', function(e) {
            e.preventDefault();
            fbapi.showSendDialog(msg);
            
        });
    };
    
    
};


