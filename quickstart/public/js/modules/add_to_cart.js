
shopinterest.modules.add_to_cart = function() {
    var module_name = 'add_to_cart';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var callback = null;
    var product_info = {};
    var left_quantity = 0;
    var _this = this;

    _this.render = function(tgt, pinfo, _left_quantity, cb) {
        var template = shopinterest.templates.add_to_cart;
        if(tgt.find(".resell").length>0){
            return;
        }
        if(tgt.find(".server-widget").length>0){
            container = tgt.find(".server-widget");
        } else {
            var html = substitute(template, {id: id});
            tgt.html(html);
            container = tgt.find("#"+id);
        }
        callback = cb;
        product_info = pinfo;
        left_quantity = _left_quantity;
        bindUI();
    };

    var bindUI = function() {
        //if($('body').hasClass('loggedout')){
        //    return;
        //}
        container.click(function(e){
            // 1. gat
            gat(e, "product-encart");
            // 2.post ajax, get cart num
            var q = $("input.product-quantity").val();
            q = (/^\d+$/.test(q)) ? parseInt(q) : -1;
            if(q<1){
                alert("please enter a correct quantity!");
                return false;
            }
            if(product_info.dealer != 'amazon' && left_quantity < q){
                alert("There are only "+ left_quantity +" available, please change the quantity and try again");
                return false;
            }
            product_info.quantity = q;
            var cf = $(".product-cf").attr("value");
            if($("#custom-field option").length>1){// has fileds
                if(cf.length <= 0){
                    alert("Please select a custom field!");
                    return false;
                }
                var qty_in_stock = $("input.product-cf").attr("quantity");
                if(qty_in_stock < q){
                    alert("There are only "+ qty_in_stock +" available, please change the quantity and try again");
                    return false;
                }
            }

            product_info.custom_field = cf;
            utils.spinner.show();
            $.post('/api/add2cart', product_info, function(response) {
                response = $.parseJSON(response);
                utils.spinner.close();
                if(response.status != 'success') {
                    if(response.errors.errno == 788){ // see errors.php
                        alert("There are only "+ response.errors.qty_in_stock +" available, please change the quantity and try again");
                        return;
                    }
                    alert(response.errors.msg);
                    return;
                }
                left_quantity--;
                var data = response.data;
                $("body").trigger("update-cart-num", ["set", data.cart_num]);
                if(typeof(callback) == 'function') {
                    callback(response);
                }
            });
            return false;
        });
    };
};
