shopinterest.controllers.selling_products_shipping = new function() {

    var utils = shopinterest.common.utils,
        substitute = utils.substitute,
        get_post_data = utils.get_post_data,
        post = utils.post,
        $container = $('#sellingvenue-products-shipping');

    var bind_auto_save = function(field) {
        var auto_save_field = field.find('.shipping-header .auto-save-field');
        auto_save_field.unbind('change');
        auto_save_field.bind('change', function(){
            var _this = $(this);

            if(_this.val() === "Custom") {
                return;
            }
            var post_data = {};
            post_data.id = field.find('.shipping-id').val();

            var input_opt_name = field.find('.shipping-name').val();            
            if(input_opt_name === 'Custom') {
                post_data.name = field.find('.shipping-name-extra').val();
            } else {
                post_data.name = input_opt_name;
            }

            post('/api/saveshippingopt', {shipping: JSON.stringify(post_data)}, function(response) {

                if(response.status === 'success') {
                    var data = response.data;
                    utils.alertBox({
                        container: field.find('.alert-field'),                    
                        type: 'success',
                        autohide: 'true'
                    });
                } else {
                    utils.alertBox({
                        container: $('.alert-field'),
                        type: 'error',
                        autohide: 'true'                        
                    });
                }
            });
        });

        var destinations = field.find('.shipping-destination');
        $.each(destinations, function(i, item){
            var destination = $(item);
            var auto_save_field_d = destination.find('.auto-save-field');
            auto_save_field_d.unbind('change');
            auto_save_field_d.bind('change', function(){
                var post_data = {'shipping_destinations': []};
                post_data.id = field.find('.shipping-id').val();
                var dest_data = utils.get_post_data(destination);
                post_data.shipping_destinations.push(dest_data);

                post('/api/saveshippingopt', {shipping: JSON.stringify(post_data)}, function(response) {
                    if(response.status === 'success') {
                        var data = response.data,
                            shipping_destinations = data[0].shipping_destinations;
                        utils.alertBox({
                            container: field.find('.alert-field'),                    
                            type: 'success',
                            autohide: 'true'
                        });

                    } else {
                        utils.alertBox({
                            container: field.find('.alert-field'),
                            type: 'error',
                            autohide: 'true'                            
                        });
                    }
                });
            });
         });
    };

    $.each($('.products-shipping'), function(i, item){
        item = $(item);
        bind_auto_save(item);
    });

    // add pattern
    $container.find('.addnew-shipping').click(function() {
        
        var tpl = shopinterest.templates.sellingvenue_shipping_pattern;
        var html = "";
        
        var option_names = ["Custom", "Express", "Priority"];
        var option_names_been_used = [];
        $('.exist-option-name').each(function(i, item){
            option_names_been_used.push($(item).val());
        });

        var available_option_names = $(option_names).not(option_names_been_used).get(); // get diff
        var option_name = available_option_names.pop() || 'Custom';
        
        post('/api/addshippingopt', {option_name: option_name}, function(response){

            var opt_name = response.data[0].name;
            if(opt_name === "Priority") {
                response.data[0].is_priority = true;
            } else if(opt_name === "Express") {
                response.data[0].is_express = true;
            } else {
                response.data[0].is_custom = true;
            }
            html = substitute(tpl, response.data[0]);
            html = $(html);
            if(html.find('.shipping-name').val() === "Custom") {
                html.find('.shipping-name-extra').show();
            }

            html = $(html);
            $('.shipping-name').each(function(i, item){
                var exist_shipping_type = $(item).val();
                if(exist_shipping_type === 'Custom') return;
                html.find(".shipping-name [value='"+exist_shipping_type+"']").remove();
            });
            
            $container.find('.products-shipping-list').append(html);
            var newly_added_elem = $container.find('.products-shipping:last-child');
            bind_auto_save(newly_added_elem);
            $('body').scrollTop(newly_added_elem.offset().top);
        });
    });

    // delete pattern
    $container.on('click', '.delete-shipping', function() {
        var _this = $(this);
        var container = _this.closest('.products-shipping');
        var shipping_id = container.find('.shipping-id').val() || 0;

        if(shipping_id) {
            post(
                '/api/deleteshippingopt',            
                {id: shipping_id},
                function(response_obj){
                    if(response_obj.status === 'success') {
                        container.remove();
                    }
                });

        } else {
            container.remove();
        }
    });

    // if service type custom clicked, show the custom input field
    $container.on('change', '.select-type', function() {
        if ( this.value === 'Custom' ) {
            $(this).next('.custom-type').fadeIn('fast')
            .focus();
        } else {
            $(this).next('.custom-type').hide();
        }
    });

    // toggle internation shipping
    $("#no-inter-shipping").click(function(e){
        var flag = $(this).is(':checked');
        utils.spinner.show();
        $.post('/api/save-selling-settings', {store : {no_international_shipping: flag ? 1 : 0}}, function(response) {
            response = $.parseJSON(response);
            if(response.status != 'success') {
                alert("Error!");
            } else {

            }
            utils.spinner.close();
        });
    });
};
