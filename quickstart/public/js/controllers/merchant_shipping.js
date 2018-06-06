shopinterest.controllers.shipping_options = new function() {
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var show_message = utils.show_message;
    var tips = $(".msg");

    // add shipping option item
    $('.add-shipping').click(function() {
        var html = shopinterest.templates.shipping_options;
        html = substitute(html, {countries: countries});
        $('.shipping-options-list').append($(html));
    });

    var std_options = ["Standard", "Priority", "Express"];
    // switch service type and save options

    var type_switcher = function(e) {
        if ($(this).find('option:checked').val() === 'Custom') {
            $(this).siblings('.extra-service-type').show();
        } else {
            $(this).siblings('.extra-service-type').hide();
            save_option($(this));
        }
    };
    $(".service-type").each(function(){
        if ($(this).find('option:checked').val() === 'Custom') {
            $(this).siblings('.extra-service-type').show();
        } else {
            $(this).siblings('.extra-service-type').hide();
        }
    });

    var save_option = function(node) {
        var opt_name = node.find('option:checked').val();
        if (opt_name === 'Custom') {
            opt_name = node.siblings('.extra-service-type').val();
        }
        if(opt_name === '') {
            alert("please fill the option name");
            return;
        }
        var opt_id = node.attr("opt_id") || 0;
        var old_opt = node.attr("old_opt");
        $.post(
            '/api/saveshippingopt',
            {shipping: JSON.stringify({id: opt_id, name: opt_name})},
            function(response){
                var response_obj = $.parseJSON(response);
                if(response_obj.status === 'success') {
                    var new_shipping = response_obj.data[0];
                    node.attr("opt_id", new_shipping.id);
                    console.log(new_shipping);
                    show_message('success', tips, 'Shipping Option Saved');
                } else {
                    show_message('failure', tips, 'Option name already exists');
                    var selected = old_opt;
                    if(std_options.indexOf(selected) < 0) {
                        selected = 'Custom';
                    }
                    console.log(old_opt);
                    node.find(".service-type").val(selected);
                    if (selected == 'Custom') {
                        var cf = node.siblings('.extra-service-type');
                        opt_name = cf.val(old_opt);
                        cf.show();
                    }
                }
            });
    };

    $('.shipping-options-list').on('focusin', '.service-type', function(e){
        var node = $(this);
        var opt_name = node.find('option:checked').val();
        if (opt_name === 'Custom') {
            opt_name = node.siblings('.extra-service-type').val();
        }
        node.attr("old_opt", opt_name);
    });
    $('.shipping-options-list').on('change', '.service-type', type_switcher);
    $('.shipping-options-list').on('focusout', '.extra-service-type', function(e){
        var node = $(this).siblings('.service-type');
        save_option(node);
    });

    // add destination item
    var save_destination = function(node) {
        var item = $(node).closest(".dest-item");
        var option_item = item.closest(".shipping-options-item").find(".service-type");
        var option_id = option_item.attr("opt_id") || 0;
        if(option_id == 0){
            alert("Please save the shipping option first!");
            return;
        }
        var option_name = option_item.val();
        if (option_name === 'Custom') {
            option_name = option_item.siblings('.extra-service-type').val();
            //if(option_name == '')return;
        }
        var fields = {
            base:  item.find(".base"),
            additional: item.find(".additional"),
            fromdays:  item.find(".from"),
            todays:  item.find(".to")
        };
        var values = {};
        for(var $k in fields){
            var v = fields[$k].val();
            if(v == ''){
                alert("please fill all destionation fields");
                fields[$k].focus();
                return;
            }
            values[$k] = v;
        }
        values.name = item.find(".dest").val();
        values.id = item.attr("dest_id") || 0;
        values.shipping_option_id = option_id;
        $.post(
            '/api/saveshippingdest',
            {shipping: JSON.stringify(values)},
            function(response){
                var response_obj = $.parseJSON(response);
                if(response_obj.status === 'success') {
                    var new_shipping = response_obj.data[0];
                    item.attr("dest_id", new_shipping.id);
                    show_message('success', tips, 'Destination saved');
                } else {
                    show_message('failure', tips, 'Destination save error');
                }
            });
    };

    $('.shipping-options-list').on('click', '.add-dest', function() {
        var destWrap = $(this).parents('.dest-wrap'),
            $html = destWrap.find('.dest-item').eq(0).clone();

        // init dest item
        $html.find('select option').eq(0).prop('checked', true);
        $html.find('.input-dollar').attr('value', '');
        $html.attr("dest_id", 0);
        $html.insertBefore($(this).parent());
    });

    $('.shipping-options-list').on('click', '.dest-save', function(e){save_destination(this);});

    // delete shipping item
    $('.shipping-options-list').on('click', '.dest-delete', function() {
        var thisItem = $(this).parents('.shipping-options-item');
        var _this = $(this);
        var _dest_id = _this.closest(".dest-item").attr("dest_id") || 0;
        var _opt_id = _this.closest(".shipping-options-item").find('.service-type').attr("opt_id") || 0;

        if ( thisItem.find('.dest-item').length === 1 ) {
            if(_dest_id == 0 && _opt_id == 0){
                _this.parents('.dest-item').remove();
                thisItem.remove();
                return;
            }
            // if the last dest item, remove the whole shippping item
            $.post(
                '/api/deleteshippingopt',
                {id:  _opt_id},
                function(response){
                    var response_obj = $.parseJSON(response);
                    if(response_obj.status === 'success') {
                        thisItem.remove();
                    } else {
                        show_message('failure', tips, 'ERROR');
                    }
                });
        } else {
            if(_dest_id == 0){
                _this.parents('.dest-item').remove();
                return;
            }
            $.post(
                '/api/deleteshippingdest',
                {id: _dest_id},
                function(response){
                    var response_obj = $.parseJSON(response);
                    if(response_obj.status === 'success') {
                        _this.parents('.dest-item').remove();
                    } else {
                        show_message('failure', tips, 'ERROR');
                    }
                });
        }
    });

};
