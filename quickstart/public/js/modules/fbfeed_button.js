
shopinterest.modules.fbfeed_button = function() {

    var module_name = 'fbfeed_button';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var fbapi = shopinterest.libs.fbapi;
    var feed = null;
    var success = null;
    var failure = null;
    var button_icon = null;

    _this.render = function(tgt, feed_in, success_in, failure_in) {
        feed = feed_in;
        button_icon = feed.button_icon;
        success = success_in;
        failure = failure_in;
        var content = feed.content;
        if(button_icon) {
            content = '<img width="' + button_icon.width + '" height="' + button_icon.height + '" src="' + button_icon.img_src + '">';
        }
        var template = shopinterest.templates.fbfeed_button;
        var html = substitute(template, {id: id, content: content});
        tgt.html(html);
        container = $('#'+id);
        bindUI();
    };

    var bindUI = function() {
        container.on('click', function(e) {
            e.preventDefault();
            fbapi.showFeedDialog(feed, success, failure);
        });
    };


};
