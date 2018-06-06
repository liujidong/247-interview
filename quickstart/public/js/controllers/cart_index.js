shopinterest.controllers.cart_index = new function() {

    function coupon_reload(){
        var l = $("#coupon-code").text().length;
        if(l>0) window.location.reload();
    }

    function update_cart(response){
        var data = response.data;
        $("body").trigger("update-cart-num", ["set", data.cart_num]);
        $("#total-price").html(data.total_prices||"USD $0");
        coupon_reload();
    }

    $("input.quantity").change(function(e){
        var _this = $(this);
        var qty = _this.val();
        if(!/^\d+$/.test(qty)){
            alert("bad quantity");
            return false;
        } else {
            qty = parseInt(qty);
        }
        gat(e, "shopping-cart-operation", {label:"change item quantity"});
        var p = $(this).closest(".cart-item");
        var s = $(this).closest(".cart-list");
        var q = p.find(".quantity");
        var product_info = {};
        product_info['store_id'] = p.attr('store_id');
        product_info['product_id'] = p.attr('product_id');
        product_info['dealer'] = p.attr('dealer');
        product_info['external_id'] = p.attr('external_id');
        product_info['quantity'] = qty;
        product_info['currency'] = p.attr('currency');
        var cf_span = p.find(".tag");
        if(cf_span.length>0){
            product_info['custom_field'] = cf_span.text();
        }
        product_info['action'] = 'set';
        $.post('/api/add2cart', product_info, function(response) {
            response = $.parseJSON(response);
            if(response.status != 'success') {
                if(response.errors.errno == 788){ // see errors.php
                    alert("There are only "+ response.errors.qty_in_stock +" available, please change the quantity and try again");
                    q.val(response.errors.qty_in_cart);
                }
                return;
            }
            q.val(response.data.product_num);
            p.find("span.subtotal").text(q.val() * p.attr('price'));
            if(response.data.product_num<1) {
                p.fadeOut(500, function(){
                    p.remove();
                    if(s.find(".cart-item").length<1){
                        s.remove();
                    }
                });
            }
            update_cart(response);
        });
        return false;
    });

    $(".update-item").click(function(e){
        var p = $(this).closest(".cart-item");
        var s = $(this).closest(".cart-list");
        var q = p.find(".quantity");
        var quantity = q.val();
        var product_info = {};
        product_info['store_id'] = p.attr('store_id');
        product_info['product_id'] = p.attr('product_id');
        product_info['dealer'] = p.attr('dealer');
        product_info['external_id'] = p.attr('external_id');
        product_info['quantity'] = $(this).hasClass("add")? 1 : -1;
        gat(e, "shopping-cart-operation", {label: ($(this).hasClass("add")? "increase": "decrease") + " item quantity"});
        product_info['currency'] = p.attr('currency');
        var cf_span = p.find(".tag");
        if(cf_span.length>0){
            product_info['custom_field'] = cf_span.text();
        }
        $.post('/api/add2cart', product_info, function(response) {
            response = $.parseJSON(response);
            if(response.status != 'success') {
                if(response.errors.errno == 788){ // see errors.php
                    alert("There are only "+ response.errors.qty_in_stock +" available, please change the quantity and try again");
                }
                return;
            }
            q.val(response.data.product_num);
            p.find("span.subtotal").text(q.val() * p.attr('price'));
            if(response.data.product_num<1) {
                p.fadeOut(500, function(){
                    p.remove();
                    if(s.find(".cart-item").length<1){
                        s.remove();
                    }
                });
            }
            update_cart(response);
        });
    });

    $(".item-delete").click(function(e){
        var p = $(this).closest(".cart-item");
        var s = $(this).closest(".cart-list");
        var q = p.find(".quantity");
        var quantity = q.val();
        var cf_span = p.find(".tag");
        var product_info = {};
        product_info['store_id'] = p.attr('store_id');
        product_info['product_id'] = p.attr('product_id');
        product_info['dealer'] = p.attr('dealer');
        product_info['external_id'] = p.attr('external_id');
        product_info['quantity'] = - quantity;
        product_info['currency'] = p.attr('currency');
        gat(e, "shopping-cart-operation", {label: "remove item"});
        if(cf_span.length>0){
            product_info['custom_field'] = cf_span.text();
        }
        $.post('/api/add2cart', product_info, function(response) {
            response = $.parseJSON(response);
            if(response.status != 'success') {
                return;
            }
            p.fadeOut(500, function(){
                p.remove();
                if(s.find(".cart-item").length<1){
                    s.remove();
                }
            });
            update_cart(response);
        });
    });

    $("#submit-coupon").click(function(e){
        var coupon = $("#input-coupon").val();
        gat(e, "shopping-cart-operation", {label: "apply coupon"});
        $.post('/api/apply-coupon', {coupon: coupon}, function(response) {
            response = $.parseJSON(response);
            if(response.status != 'success') {
                alert("Bad Coupon Code!");
            } else {
                window.location.reload();
            }
        });
    });

    $("#remove-coupon").click(function(e){
        var coupon = $("#coupon-code").text();
        $.post('/api/clear-coupon', {coupon: coupon}, function(response) {
            response = $.parseJSON(response);
            if(response.status != 'success') {
                //alert("Bad Coupon Code!");
            } else {
                window.location.reload();
            }
        });
    });

    $('.checkout-button').click(gat_handler("shopping-checkout", {label:  "goto checkout from cartpage"}));
};
