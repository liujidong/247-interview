
shopinterest.controllers.merchant_products = new function() {
    var utils = shopinterest.common.utils,
        get_upload_dst = utils.get_product_image_upload_dst2,
        uniqid = utils.uniqid,
        substitute = utils.substitute,
        show_message = utils.show_message,
        validate = utils.validate,
        convert = utils.convert,
        store_id = $('#my_store_id').val(),
        popup_tags = new shopinterest.modules.popup_tags(),
        body_dom = $('#page-upload'),
        clickOverThePicturePanel = false,
        customField = body_dom.find('.custom-field');

    // render popup html dom when the page loads
    popup_tags.render(body_dom);


    // validate & make ajax for each element
    $('.product_input_field').change(function() {
        var _this = $(this);
        var value = _this.val();
        var field = get_validate_field(_this);
        var product_id = _this.closest('.operation-section').find('.product_id').val();
        _this.removeClass('invalid');

        var tips = _this.closest('.operation-section').find('.tips');

        if(field === 'commission') {
            var _value = value;
            value = {};
            value.commission = _value;
            value.price = _this.closest('.operation-cell-3').find('.product_price').val();
        }

        if(validate(field, value)) {

            if(field === 'commission') {
                $.post('/api/optinsalesnetwork',function(response){});
                value = value.commission;
            }

            var products = [];
            var product = {};
            product['id'] = product_id;
            product[field] = value;
            products.push(product);

            $.post('/api/createproducts', {products: JSON.stringify(products)}, function(response){
                var type = '';
                var message = '';

                var response_obj = $.parseJSON(response);
                if(response_obj.status === 'success') {
                    type = 'success';
                    message = 'Saved';
                } else {
                    type = 'failure';
                    message = 'Please provide a valid value';
                }
                show_message(type, tips, message);
            });
        } else {
            _this.addClass('invalid');
            show_message('failure', tips, 'Please provide a valid value');
        }

        function get_validate_field(_this) {

            if(_this.hasClass('product_name')) {
                return 'name';
            }
            if(_this.hasClass('product_description')) {
                return 'description';
            }
            if(_this.hasClass('product_quantity')) {
                return 'quantity';
            }
            if(_this.hasClass('product_price')) {
                return 'price';
            }
            if(_this.hasClass('product_commission')) {
                return 'commission';
            }
            if(_this.hasClass('product_shipping')) {
                return 'shipping';
            }
            if(_this.hasClass('product_category')) {
                return 'global_category_id';
            }
            if(_this.hasClass('product_purchase_url')) {
                return 'purchase_url';
            }
        };



//        console.log(_this.attr("class"));
//        console.log(_this.val());
    });

    $('.edit-tags').click(function(e){
        e.preventDefault();
        var _this = $(this);
        var tags_section = _this.closest('.prod-tags').find('.prod-tag');
        var tags = [];
        $.each(tags_section, function(index, tag_span){
            var tag = $(tag_span).html();
            tags.push(tag);
        });
        popup_tags.show(_this,tags);
    });


    // click the product picture
    $('.prod-image').click(function(e){
        var _this = $(this);

        var picture_expand_box = _this.parents('.operation-row').siblings('.prod-image-expand');
        adjust_image_expand(picture_expand_box);
        picture_expand_box.show();
        e.stopPropagation();
    });

    // prod-image sortable
    $(".prod-image-draggable").sortable({
        placeholder: "ui-state-highlight",
        stop: function( event, ui ) {
            var _this = $(this);
            var panel = _this.find('.expand-pic');

            if(panel.length <= 1) {
                return;
            }
            var product_id = _this.closest('.operation-section').find('.product_id').val();
            var products = [];
            var pictures = [];

            $.each(panel, function(i, pic){
                var picture = {};
                picture.id = $(pic).attr('picture_id');
                picture.orderby = i;
                pictures.push(picture);
            });
            var product = {};
            product.id = product_id;
            product.pictures = pictures;
            products.push(product);
            //console.log(products);
            $.post('/api/createproducts', {products: JSON.stringify(products)}, function(response) {});

            adjust_image_cover(_this);
        }
    });
    $(".prod-image-expand").disableSelection();


    //check-all checked
    $('#check-all').change(function(e){
        $('.operation-section .item-header-1 input:checkbox').each(function(){
            this.checked = e.target.checked;
        });
    });

    $('.prod-image-expand').click(function(e){
        clickOverThePicturePanel = true;
    });

    var adjust_image_expand = function(img_panel) {
        var pic_count = img_panel.find('.expand-pic').length;
        img_panel.closest('.operation-section').find('.operation-cell-1 .prod-image .prod-image-number').html(pic_count);
        img_panel.width((pic_count+1) * 96 + 6);
    };

    var adjust_image_cover = function(img_panel) {
        var cover_picture = img_panel.closest('.operation-section').find('.operation-row .operation-cell-1 .prod-image img');
        var first_picture_url = img_panel.find('.expand-pic :first img').attr('src');
        cover_picture.attr('src', first_picture_url);
    };

    var delete_tag = function(product_id, category, target) {
        $.post('/api/deleteproductcategory', {product_id:product_id, category:category}, function(response) {
            var response_obj = $.parseJSON(response);
            if(response_obj.status === 'success') {
                target.remove();
            }
        });
    };

    var delete_picture = function(product_id, picture_id, target) {
        if(confirm("Sure you want to delete this picture?")) {
            $.post('/api/deleteproductpicture', {product_id:product_id, picture_id:picture_id}, function(response) {
                var response_obj = $.parseJSON(response);
                var pic_panle = target.closest('.prod-image-expand');
                if(response_obj.status === 'success') {
                    target.parent().remove();
                    adjust_image_expand(pic_panle);
                    adjust_image_cover(pic_panle);
                }
            });
        }
    };

    var save_product = function(target) {
        var section = target.closest('.operation-section').find('.operation-row');
        var product_name = section.find('.product_name');
        var product_description = section.find('.product_description');
        var product_quantity = section.find('.product_quantity');
        var product_price = section.find('.product_price');
        var product_commission = section.find('.product_commission');
        var product_shipping = section.find('.product_shipping');
        var global_category = section.find('.product_category');
        var tips = target.siblings('.tips');
        var product_id = target.parent().siblings('.product_id').val();
        var products = [];
        var product = {};
        product['id'] = product_id;


        var error = 0;
        if(!validate('name', product_name.val())) {
            error ++;
            product_name.addClass('invalid');
        } else {
            product['name'] = product_name.val();
        }

        if(!validate('description', product_description.val())) {
            error ++;
            product_description.addClass('invalid');
        } else {
            product['description'] = product_description.val();
        }

        if(!validate('quantity', product_quantity.val())) {
            error ++;
            product_quantity.addClass('invalid');
        } else {
            product['quantity'] = product_quantity.val();
        }

        if(!validate('price', product_price.val())) {
            error ++;
            product_price.addClass('invalid');
        } else {
            product['price'] = product_price.val();
        }

        if(!validate('commission', {commission: product_commission.val(), price: product_price.val()})) {
            error ++;
            product_commission.addClass('invalid');
        } else {
            product['commission'] = product_commission.val();
        }

        if(!validate('shipping', product_shipping.val())) {
            error ++;
            product_shipping.addClass('invalid');
        } else {
            product['shipping'] = product_shipping.val();
        }

        if(!validate('global_category_id', global_category.val())) {
            error ++;
            global_category.addClass('invalid');
        } else {
            product['global_category_id'] = global_category.val();
        }

        products.push(product);

        var type = 'failure';
        var message = 'Please provide a valid value';
        if(error === 0) {
            type = 'success';
            message = 'Saved';
            $.post('/api/createproducts', {products: JSON.stringify(products)}, function(response){
                var response_obj = $.parseJSON(response);
                //if(response_obj.status === 'success') {
                //type = 'success';
                //message = 'Saved';
                //}
            });
            if(product['commission'] !== "undefined" && product['commission'] !== '0') {
                $.post('/api/optinsalesnetwork',function(response){});
            }
        }

        show_message(type, tips, message);
    };

    var delete_product = function(product_id, target) {
        if(confirm("Sure you want to delete this product?")) {
            $.post('/api/delete-product', {product_id: product_id}, function(){
                target.closest('.operation-section').fadeOut('slow');
            });
        }
    };

    var handle_click = function(e) {

        //console.log('handle_click');
        //e.preventDefault();
        //e.stopPropagation();
        var target = $(e.target);

        if (!clickOverThePicturePanel){
             $('.prod-image-expand').hide();
        };

        if(target.hasClass('prod-tag')) {
            var product_id = target.closest('.operation-section').find('.product_id').val();
            var category = target.attr('category');
            delete_tag(product_id, category, target);
        }

        if(target.hasClass('icon-delete')) {
            var product_id = target.closest('.operation-section').find('.product_id').val();
            var picture_id = target.parent().attr('picture_id');
            var picture_cnt = target.closest('.prod-image-draggable').find('.expand-pic').length;
            if(picture_cnt === 1) {
                alert('Can\'t delete this picture');
                return false;
            }
            delete_picture(product_id, picture_id, target);
        }

        if(target.hasClass('button-save')) {
            save_product(target);
        }

        if(target.hasClass('button-delete')) {
            var product_id = target.closest('.operation-section').find('.product_id').val();
            delete_product(product_id, target);
        }

        if(target.hasClass('search-input')) {

            // press enter key
            target.keypress(function (event) {
                var search = target.val();
                if(event.keyCode == 13 && target.val() != '') {
                    target.parent().submit();
                }
            });
        }

        clickOverThePicturePanel = false;
    };

    $(document).on('click', function(e) {
        handle_click(e);
    });

//    $(document).on('click',function(e){
//        var thisTarget = $(e.currentTarget);
//
//        if (!clickOverThePicturePanel){
//            $('.prod-image-expand').hide(function() {});
//        }else if(thisTarget.hasClass('prod-tag')){
//            //Remove tags
//            thisTarget.fadeOut();
//            setTimeout(function(){
//                thisTarget.remove();
//            }, 500);
//        }else if(thisTarget.hasClass('custom-title')){
//            //edit custom field title
//            popup_custom_field.show();
//        }else if(thisTarget.hasClass('icon-delete')){
//            //alert the user
//            if(confirm("Sure you want to delete this picture?")){
//                //picturen number
//                var img_panel = thisTarget.closest('.prod-image-expand');
//                var panel_width = caculate_image_expand_width(img_panel);
//
//                var panelParent = thisTarget.parents('.prod-image-expand');
//                //remove the dom
//                thisTarget.parents('.expand-pic').remove();
//
//                //change the parent width
//                panelParent.width(panel_width);
//            }else{
//            }
//        }else if(thisTarget.hasClass('delete-destination')){
//            //Delete a shipping destination
//            var thisRow = thisTarget.parents('.table-row');
//            thisRow.fadeOut();
//            setTimeout(function(){
//                thisRow.remove();
//            },500);
//        }
//
//        clickOverThePicturePanel = false;
//    });

//    //any checkbox changed
//    $('.operation-section .item-header-1 input:checkbox').add('.operation input:checkbox').change(function(){
//        if($('.operation-section .item-header-1 input:checked').length > 0){
//            $('#check-all-operate').fadeIn();
//        }else{
//            $('#check-all-operate').fadeOut();
//        }
//    });

    var upload_image = function(container) {

        var length = container.find('.expand-pic').length;
        if(length >= 5) {
            alert("Sorry, you can't upload more pictures");
            return;
        }

        var product_id = container.closest('.operation-section').find('.product_id').val();
        var picture_order = length;

        filepicker.pick({
                mimetypes: ['image/*']
            },
            function(inkBlob) {
                var products = [];

                var counter = 0;
                var max = 7;

                var salt = uniqid();
                var picture = {};
                picture.converted_pictures = [];
                var product = {};
                product.pictures = [];

                var converted_pictures = [];

                var converted_picture = {};
                converted_picture.type = 45;
                converted_picture.url = convert(inkBlob.url, {width: 45, height: 45, format: 'jpg', quality: 100, fit: 'crop'});
                converted_pictures.push(converted_picture);

                converted_picture = {};
                converted_picture.type = 70;
                converted_picture.url = convert(inkBlob.url, {width: 70, format: 'jpg', quality: 100, fit: 'max'});
                converted_pictures.push(converted_picture);

                converted_picture = {};
                converted_picture.type = 192;
                converted_picture.url = convert(inkBlob.url, {width: 192, format: 'jpg', quality: 100, fit: 'max'});
                converted_pictures.push(converted_picture);

                converted_picture = {};
                converted_picture.type = 236;
                converted_picture.url = convert(inkBlob.url, {width: 236, format: 'jpg', quality: 100, fit: 'max'});
                converted_pictures.push(converted_picture);

                converted_picture = {};
                converted_picture.type = 550;
                converted_picture.url = convert(inkBlob.url, {width: 550, format: 'jpg', quality: 100, fit: 'max'});
                converted_pictures.push(converted_picture);

                converted_picture = {};
                converted_picture.type = 736;
                converted_picture.url = convert(inkBlob.url, {width: 736, format: 'jpg', quality: 100, fit: 'max'});
                converted_pictures.push(converted_picture);
                picture.orderby = picture_order;
                picture.url = inkBlob.url;
                picture.converted_pictures = converted_pictures;
                product.pictures.push(picture);
                product.id = product_id;
                products.push(product);

                $.post('/api/createproducts', {products: JSON.stringify(products)}, function(response) {
                    var response_obj = $.parseJSON(response);
                    if(response_obj.status === 'success') {
                        var picture_id = response_obj.data[0]['pictures'][0].id;
                        var picture_url = response_obj.data[0]['pictures'][0]['converted_pictures'][2]['url'];
                        var template = shopinterest.templates.expand_pic;
                        var html = substitute(template, {picture_id:picture_id, url: picture_url});
                        container.append(html);
                        adjust_image_expand(container.parent());
                    }
                });

            });

    };

    // upload file
    $('.button-upload-more').click(function(){
        var _this = $(this);
        var container = _this.closest('.operation-row').next('.prod-image-expand').find('.prod-image-draggable');
        upload_image(container);
    });

    $('.expand-add-more').click(function(){
        var _this = $(this);
        var container = _this.siblings('.prod-image-draggable');
        upload_image(container);
    });

    // toggle active and inactive status
    $('input:radio').click(function(e) {

        var _this = $(this);
        var status_toggle = _this.closest('.status-toggle');

        if(status_toggle.hasClass('status-inactive') && _this.val() === 'active') {
            $(this).toggleClass('status-inactive');
            $(this).toggleClass('status-active');
            location.href = "/merchant/products?status=active";
        }

        if(status_toggle.hasClass('status-active') && _this.val() === 'inactive') {
            $(this).toggleClass('status-inactive');
            $(this).toggleClass('status-active');
            location.href = "/merchant/products?status=inactive";
        }
    });

    // allow resell checkbox clicked
    $('.resell').click(function(e) {
        var _this = $(this);
        var container = _this.closest('.operation-section');
        var quantity_input_label = container.find('.quantity-input-label');
        var commission_input_label = container.find('.commission-input-label');
        var shipping_calc = container.find('.shipping-calc');
        var resell = 0;
        var product_id = container.find('.product_id').val();
        var purchase_url_container = container.find('.purchase_url_container');

        if(_this.attr('checked')) {
            quantity_input_label.hide();
            commission_input_label.hide();
            shipping_calc.hide();
            purchase_url_container.show();
            resell = 1;
        } else {
            quantity_input_label.show();
            commission_input_label.show();
            shipping_calc.show();
            purchase_url_container.hide();
        }

        $.post('/api/resellproduct', {store_id: store_id, product_id: product_id, resell: resell});

    });


    // custom fields: ajax save/delete
    var update_field = function(item, action){ //action = save|delete
        var fid = item.attr("fid");
        var pid = item.closest('.operation-section').find('.product_id').val();
        var fname = item.find(".cf_name").val();
        var quantity = item.find(".cf_quantity").val();
        var tips = item.closest('.operation-section').find('.tips');
        if(action == "save"){
            if(fname == ''){
                alert("please fill the field name");
                item.find(".cf_name").focus();
                return;
            }
            if(!/\d+/.test(quantity)){
                alert("please enter a correct quantity");
                item.find(".cf_quantity").focus();
                return;
            }
        }
        $.post('/api/' + action + 'customfield',
               {product_id: pid, field_id: fid, field_name: fname, quantity: quantity},
               function(response){
                   var response_obj = $.parseJSON(response);
                   var type = "success";
                   var message = "";
                   if(response_obj.status === 'success') {
                       if(action == "save") {
                           item.attr('fid', response_obj.data.field_id);
                           item.find('input').prop('disabled', true).end()
                               .find('.save-options').hide().end()
                               .find('.edit-options').show();
                           message = "custom field saved";
                       } else if(action == "delete"){
                           item.remove();
                           message = "custom field deleted";
                       }
                   } else {
                       type = "failure";
                       message = "custom field " + action + " error";
                   }
                   show_message(type, tips, message);
               });
    };

    // custom fields: create
    customField.on('click', '.create', function() {
        var html = shopinterest.templates.product_custom_fields;

        $html = $(html);

        // mark as a initialized field
        // should be removed or set to false after click the save button
        $html.attr('fid', '0');

        $(this).parents('.custom-field').find('.table').append($html);
    });

    // custom fields: edit
    customField.on('click', '.edit-custom-item', function() {
        var thisItem = $(this).parents('.custom-item');

        thisItem.find('input:disabled').prop('disabled', false).end()
        .find('.edit-options').hide().end()
        .find('.save-options').show();
    });

    // custom fields: delete
    customField.on('click', '.del-custom-item', function() {
        var thisItem = $(this).parents('.custom-item');
        update_field(thisItem, 'delete');
    });

    // custom fields: cancel
    customField.on('click', '.cancel-custom-item', function() {
        var thisItem = $(this).parents('.custom-item');

        if ( thisItem.attr('fid') === '0' ) {
            thisItem.remove();
        } else {
            thisItem.find('input').prop('disabled', true).end()
            .find('.save-options').hide().end()
            .find('.edit-options').show();
        }
    });

    // custom fields: save
    customField.on('click', '.save-custom-item', function() {
        var thisItem = $(this).parents('.custom-item');
        update_field(thisItem, 'save');
    });

    $(".product-shippingopt input").click(function(e){
        var _this = $(this);
        var pid = _this.attr('product_id');
        var opt = _this.attr('shipping_opt_id');
        var check = _this.is(":checked");
        var tips = _this.closest('.operation-section').find('.tips');
        $.post(
            check ? '/api/saveproductshippingopt' : '/api/deleteproductshippingopt',
            {product_id: pid, shipping_option_id: opt},
            function(response){
                var response_obj = $.parseJSON(response);
                var type, message;
                if(response_obj.status === 'success') {
                    type = "success";
                    message = "shipping operate success";
                } else {
                    type = "failure";
                    message = "shipping operate error";
                }
                show_message(type, tips, message);
            });
    });
};
