
shopinterest.modules.fbconnect_button = function() {
    
    var module_name = 'fbconnect_button';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var fbapi = shopinterest.libs.fbapi;
    var feed = null;
    var success = null;
    var failure = null;
    
    _this.render = function(tgt, success_in, failure_in) {
        success = success_in;
        failure = failure_in;
        var template = shopinterest.templates.fbconnect_button;
        var html = substitute(template, {id: id}); 
        tgt.html(html);
        container = $('#'+id);
        bindUI();
    };
    
    _this.hide = function() {
        container.hide();
    };
    
    var bindUI = function() {
        container.on('click', 'img', function(e) {
            e.preventDefault();
            fbapi.showLoginDialog(success, failure);
            
        });
    };
    
    
};


