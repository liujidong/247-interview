
shopinterest.modules.pinstore_button = function(pinstore_lightbox) {
    
    var module_name = 'pinstore_button';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    
    _this.render = function(tgt, button_image, button_text) {
        var template = shopinterest.templates.pinstore_button;
        var html = substitute(template, {id: id, button_image: button_image, button_text: button_text}); 
        tgt.html(html);
        container = $('#'+id);
        bindUI();
    };
    
    var bindUI = function() {
        container.click(function(e) {
            e.preventDefault();
            pinstore_lightbox.show($('.tgt_pinstore_lightbox'));
        });
    };
    
    
};


