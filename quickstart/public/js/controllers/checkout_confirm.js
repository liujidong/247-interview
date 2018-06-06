shopinterest.controllers.checkout_confirm = new function() {

    var utils = shopinterest.common.utils;
    var popup_receipt =  new shopinterest.modules.popup_receipt();
    var data ={};
    var get_data = function(){
        data.shipping_date = "";
        data.shipping_name = $("#shipping_name").text();
        data.shipping_addr1 = $("#shipping_addr1").text();
        data.shipping_addr2 = $("#shipping_addr2").text();
        data.shipping_cs = $("#shipping_cs").text();
        data.shipping_country = $("#shipping_country").text();
        data.shipping_zip = $("#shipping_zip").text();
        data.order_subtotal = $("#order_price").text();
        data.order_tax = $("#order_tax").text();;
        data.order_shipping = $("#order_shipping").text();
        data.order_total = $("#order_total").text();
        data.order_id = $("#order_id").text();
        data.order_details = []; // {name, quantity, subtotal}
        $(".cart-item").each(function(){
            var q = $(this).find(".item-quantity").text();
            var name = $(this).find(".item-name").text();
            var subtotal = $(this).find(".item-subtotal").text();
            data.order_details.push({"name":name, "quantity": q, "subtotal": subtotal});
        });
    };
    get_data();

    $(document).foundation({
        reveal: { close_on_background_click: false }
    });

    var gray_panel = $('#gray-panel');
    var receipt = $('#receipt');
    var btn_finish = $('#button-finish');

    var wait = function(text){
        btn_finish.hide();
        if(text){
            utils.spinner.show({msg: text});
        } else {
            utils.spinner.show();
        }
        /*
        $('#gray-panel').foundation('reveal', 'open');
        gray_panel.reveal({
            closeOnBackgroundClick: false
        });
         */
    };

    var unwait = function(){
        btn_finish.show();
        utils.spinner.close();
        /*
        $('#gray-panel').foundation('reveal', 'close');
        $('.reveal-modal-bg').css({'display' : 'none'});
        gray_panel.trigger("reveal:close");
         */
    };

    var do_payment = function(){
        var params = {};
        var matches = window.location.href.match(/.*PayerID=([^#&]+).*/);
        if(matches && matches.length > 1){
            params['payer_id'] = matches[1];
        }
        $.post('/api/ncpayment', params, function(response) {
            response = $.parseJSON(response);
            if(response.status != 'success') {
                if(response.errors.errno == 788){ // see errors.php
                    alert("some ptoducts in your cart have been sold out, please check again");
                    window.location.href="/cart";
                    return false;
                }
                alert("ERROR:" + response.errors.msg);
                return false;
            }
            unwait();
            get_data();
            popup_receipt.render(receipt, data);
            receipt.reveal({
                closeOnBackgroundClick: false
            });
            return false;
        });
    };

    btn_finish.click(function(e){
        var next_action = $(this).attr('next_action');
        if(next_action == 'paypal_confirm') {
            wait("Redirecting to Paypal");
            gat(e, "shopping-checkout", {label:"payment: paypal confirm"});
            $.post('/api/ncpaypalconfirm', {}, function(response) {
                response = $.parseJSON(response);
                if(response.status != 'success') {
                    unwait();
                    alert("ERROR:" + response.errors.msg);
                    return;
                }
                window.location.href = response.data.redirect_url;
            });
        } else if(next_action == 'creditcard_payment'){ // execute payment
            wait('Paying with your creditcard');
            gat(e, "shopping-checkout", {label:"payment: pay with credit card"});
            do_payment();
        }
        return false;
    });

    var next_action =  btn_finish.attr('next_action');
    if(next_action == 'paypal_payment') { // auto execute paypal payment
        wait('Paying with your paypal account');
        do_payment();
    }

    $("select.shipping-option").change(function(e){
        var _this = $(this);
        var shipping_info=_this.val().split(":", 4);
        var shipping_base = parseFloat(shipping_info[0]);
        var shipping_addi = parseFloat(shipping_info[1]);
        var shipping_name = shipping_info[3];
        var order_id = _this.closest(".cart-list").attr("order_id");
        var currency_symbol = $("#order_total").text().replace(/[.0-9]+$/, "");
        gat(e, "shopping-checkout", {label:"change shipping option"});
        wait("Updating shipping option");
        $.post('/api/setordershipping',
               {order_id: order_id, shipping_name: shipping_name},
               function(response) {
                   response = $.parseJSON(response);
                   if(response.status != 'success') {
                       alert("ERROR:" + response.errors.msg);
                   } else {
                       var total_shipping = 0;
                       var store_total = 0;
                       $(_this.closest('.cart-list')).find(".cart-item").each(function(i, e){
                           console.log(i);
                           e = $(e);
                           var item_shipping = 0;
                           var q = parseInt(e.find(".item-price .item-quantity").text());
                           if(i==0){
                               item_shipping = (shipping_base + (q-1) * shipping_addi);
                           } else {
                               item_shipping = (q * shipping_addi);
                           }
                           //total_shipping += item_shipping;
                           var item_price = parseFloat(e.find(".item-price .item-sprice").text().replace(/^\D+/gi, ""));
                           var item_tax = parseFloat(e.find(".item-tax").text().replace(/^\D+/gi, ""));
                           e.find(".item-shipping").text(''+currency_symbol + item_shipping);
                           e.find(".item-subtotal").text(currency_symbol + (item_shipping + item_price*q + item_tax));
                           store_total += (item_shipping + item_price*q + item_tax);
                       });
                       $(_this.closest('.cart-list')).find(".store-total").text(currency_symbol + store_total);
                       $("select.shipping-option").each(function(i, e2){
                           var text_val = $(e2).val();
                           text_val = (text_val.split(":", 4))[2];
                           total_shipping += parseFloat(text_val);
                       });
                       $("#order_shipping").text(currency_symbol + total_shipping);
                       var order_price = parseFloat($("#order_price").text().replace(/^\D+/gi, ""));
                       var order_tax = parseFloat($("#order_tax").text().replace(/^\D+/gi, ""));
                       $("#order_total").text(currency_symbol + (total_shipping + order_tax + order_price));
                   }
                   unwait();
               });
    });
};
