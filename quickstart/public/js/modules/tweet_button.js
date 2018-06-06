
shopinterest.modules.tweet_button = function() {

    var module_name = 'tweet_button';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var twapi = shopinterest.libs.twapi;
    var tweet = null;
    var success = null;
    var failure = null;
    var button_icon = '';

    _this.render = function(tgt, tweet_in, success_in, failure_in) {
        tweet = tweet_in;
        button_icon = tweet.button_icon;
        var content = tweet.content;
        if(button_icon) {
            content = '<img width="' + button_icon.width + '" height="' + button_icon.height + '" src="' + button_icon.img_src + '">';
        }
        var template = shopinterest.templates.tweet_button;
        var html = substitute(template, {id: id, url: tweet.url, via: tweet.via, text: tweet.text, content: content});
        tgt.html(html);
        container = $('#'+id);
        bindUI();
    };

    var bindUI = function() {
        container.on('click', function(e) {
            e.preventDefault();
        });
    };

};
