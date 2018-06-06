
shopinterest.modules.nav_cart = function() {
    var module_name = 'nav_cart';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var cart_num = $.cookie("cart-num") || 0;
    var server_widget= false;

    var update_cart_num = function(e, how, num) {
        var cart_num = $.cookie("cart-num") || 0;
        //if(!num) num = 1;
        how = how.toLowerCase();
        if(how == "reset"){
            cart_num = 0;
        }else if(how == "incr"){
            cart_num = parseInt(cart_num) + parseInt(num);
        }else if(how == "set"){
            cart_num = num;
        }else{
            return;
        }
        var badge = container.find("i span.badge-count");
        badge.show();
        badge.text(cart_num);
    };

    _this.render = function(tgt) {
        var template = shopinterest.templates.nav_cart;
        var html = substitute(template, {id: id});
        container = tgt;
        if(tgt.find(".server-widget").length>0){
            server_widget = true;
            cart_num = tgt.find("i").text();
        } else {
            tgt.html(html);
            container.find("i").text(cart_num);
        }
        $("body").on("update-cart-num", update_cart_num);
    };
};
