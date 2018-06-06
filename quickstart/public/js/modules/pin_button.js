
shopinterest.modules.pin_button = function() {

    var module_name = 'pin_button';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var feed = null;
    var button_icon = null;

    _this.render = function(tgt, feed_in) {
        feed = feed_in;
        button_icon = feed.button_icon;
        var content = feed.content;
        if(button_icon) {
            content = '<img width="' + button_icon.width + '" height="' + button_icon.height + '" src="' + button_icon.img_src + '">';
        }
        var template = shopinterest.templates.pin_button;
        var html = substitute(template, {id: id, content: content, url: feed.url, description: feed.description, img_url: feed.img_url});
        tgt.html(html);
        container = $('#'+id);
    };

};
