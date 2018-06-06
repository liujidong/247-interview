
shopinterest.modules.pinpicker_lightbox = function() {
    var module_name = 'pinpicker-lightbox';
    var utils = shopinterest.common.utils;
    var container = null;
    var _this = this;
    var spinner = utils.spinner;
    
    _this.render = function() {
        var template = shopinterest.templates.pinpicker_lightbox;
        var html = template; 
        $('body').append(html);
        container = $('#'+ module_name);      
        bindUI();        
    };
    
    _this.show = function() {
        container.show();
    };
    
    var bindUI = function() {
        container.bind('pinpicker:close', function(e) {
            container.hide();
        });
        
        container.bind('pinpicker_upload:start', function(e) {
            //console.log('pinpicker_upload:start --- base.js');
            // start to show spinner
            spinner.show();                      
        });
        container.bind('pinpicker_upload:finish', function(e) {
            //console.log('pinpicker_upload:finish --- base.js');
            spinner.close();
            location.href = "/selling/products?status=inactive";            
        });               
    };
        
};


