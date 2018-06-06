
shopinterest.modules.etsy_import_lightbox = function() {

    var module_name = 'etsy_import_lightbox';
    var container = null;
    var _this = this;
    
    _this.render = function() {
        var template = shopinterest.templates.etsy_import_lightbox;
        var html = template;
        $('body').append(html);
        container = $('#'+module_name);         
        bindUI();        
    };

    _this.show = function() {
        container.show();
    };
    
    var bindUI = function() {
        container.bind('close', function(){
            container.hide();
        });
    };
};


