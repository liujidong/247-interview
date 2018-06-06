
shopinterest.modules.spinner = function(){

    var module_name = 'spinner';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var show_message = utils.show_message;    
    var container = null;
    var _this = this;
    
    _this.render = function(tgt) {
        container = $('#'+id);
        var template = shopinterest.templates.spinner;
        var html = substitute(template, {id: id});
        tgt.append(html);
    };
    
    _this.show = function() {
        $('#'+id).css({'visibility':'', 'display' : 'block'});
    };
    
    _this.close = function() {
        $('#'+id).css({'visibility':'hidden', 'display' : 'none'});
    };    
};


