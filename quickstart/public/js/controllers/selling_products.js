shopinterest.controllers.selling_products = new function() {

    var utils = shopinterest.common.utils,
        post = utils.post,
        spinner = utils.spinner,
        convert = utils.convert,
        uniqid = utils.uniqid,
        substitute = utils.substitute,
        imageItemTpl, customFieldTpl;
    
    imageItemTpl = '<li class="image-item" picture-id={{id}}>\n' +
        '<img src="{{url}}">\n' +
        '<a href="javascript:;" class="delete-image">&times;</a>\n' +
        '</li>';

    customFieldTpl = '<div class="table-row custom-fields-item" cf_id="">\n' +
        '<div class="table-cell field-cell"><input type="text" class="cf_name custom-field"></div>\n' +
        '<div class="table-cell instock-cell"><input type="text" class="cf_quantity custom-field"></div>\n' +
        '<div class="table-cell delete-cell">\n' +
        '<a href="javascript:;" class="delete-custom-field">×</a>\n' +
        '</div>\n' +
        '</div>';

    var create_products_lightbox = new shopinterest.modules.create_products_lightbox();
    create_products_lightbox.render();

    $('.create-products-form').on('invalid', function(e) {
        
    }).on('valid', function(e) {

        var _this = $(this);
        var products = [];
        var product = utils.get_post_data(_this);

        if(product.categories !== '') {
            var input_tags = product.categories.split(',');
            var tags = [];
            var i = 0;
            for(i; i<input_tags.length; i++) {
                var tag = {};
                tag.description = input_tags[i];
                tag.category = input_tags[i];
                tags.push(tag);
            }

            product.categories = tags;
        }

        products.push(product);
        spinner.show();

        post('/api/createproducts', {products: JSON.stringify(products)}, function(response) {

            if(response.status === 'failure') {
                utils.alertBox({
                    container: _this.find('.alert-field'),
                    type: 'error'
                });
            } else {
                utils.alertBox({
                    container: _this.find('.alert-field'),                    
                    type: 'success',
                    autohide: 'true'
                });
            }

            spinner.close();
        });
        
    });

    $('.add-new-products').click(function(){
        create_products_lightbox.show();
    });

    // delete a product item
    $('.product-details').on('click', '.delete-button', function() {
        if(confirm("Sure you want to delete this product?")) {
            var _this = $(this);
            var create_products_form = _this.closest('.create-products-form');
            var product_id = create_products_form.find('.product-id').val();
            post('/api/delete-product', {product_id: product_id}, function(response){
                if(response.status === 'success') {
                    _this.parents('.product-item').remove();
                } else {
                    utils.alertBox({
                        container: create_products_form.find('.alter-field'),
                        type: 'error'
                    });
                }
            });
        }
    });

    var update_field = function(item, action){ //action = save|delete
        var fid = item.attr("cf_id");
        var product_form = item.closest('.create-products-form');
        var pid = product_form.find('.product-id').val();
        var name = item.find(".cf_name").val();
        var quantity = item.find(".cf_quantity").val();

        if(action == "save"){
            if(name == ''){
                item.find(".cf_name").addClass('invalid');
                return;
            } else {
                item.find(".cf_name").removeClass('invalid');
            }

            if(!/^\d+$/.test(quantity)){
                item.find(".cf_quantity").addClass('invalid');
                return;
            } else {
                item.find(".cf_quantity").removeClass('invalid');
            }
        } else {
            if(name === '' && quantity === '') {
                item.remove();
                return;
            }
        }
        post('/api/' + action + 'customfield',
             {product_id: pid, field_id: fid, field_name: name, quantity: quantity},
             function(response_obj){
                 if(response_obj.status === 'success') {
                     item.closest('.create-products-form').find("input[name='quantity']").val(response_obj.data.product_quantity);
                     if(action == "save") {
                         item.attr('cf_id', response_obj.data.field_id);

                         utils.alertBox({
                             container: product_form.find('.alert-field'),                    
                             type: 'success',
                             autohide: 'true'
                         });
                         
                     } else if(action == "delete"){
                         item.remove();
                     }
                 } else {
                     utils.alertBox({
                         container: product_form.find('.alert-field'),
                         type: 'error'
                     });
                 }
             });
    };
    
    // select shipping options
    $('.shipping-option').click(function(){
        var _this = $(this);
        var pid = _this.attr('product_id');
        var opt = _this.attr('shipping_opt_id');
        var check = _this.is(":checked");

        post(
            check ? '/api/saveproductshippingopt' : '/api/deleteproductshippingopt',
            {product_id: pid, shipping_option_id: opt}
        );
    });

    // save & del custom-field
    var custom_field_event_handler = function(field_item) {
        var cst = field_item.find('.custom-field');
        cst.unbind('change');
        cst.bind('change', function(){
            update_field(field_item, 'save');
        });

        var del = field_item.find('.delete-custom-field');
        del.unbind('click');
        del.bind('click', function(){
            update_field(field_item, 'delete');
        });
    };

    $.each($('.custom-fields-item'), function(i, item){
        custom_field_event_handler($(item));
    });

    // add a custom field item
    $('.product-details').on('click', '.add-custom-field', function() {
        var _this = $(this);
        var container = _this.parent().siblings('.custom-fields');console.log(container);
        container.append(customFieldTpl);
        custom_field_event_handler(container.children().last());
    });

    var upload_image = function(container) {

        var length = container.find('.image-item').length;
        if(length >= 5) {
            alert("Sorry, you can't upload more pictures");
            return;
        }

        var product_id = container.closest('.create-products-form').find('.product-id').val();
        var picture_order = length;

        filepicker.pick({
                mimetypes: ['image/*']
            },
            function(inkBlob) {
                var products = [];

                var product = {};
                product.pictures = [];
                var picture = {};
                picture.orderby = picture_order;
                picture.url = inkBlob.url;
                product.pictures.push(picture);
                product.id = product_id;
                products.push(product);

                post('/api/createproducts', {products: JSON.stringify(products)}, function(response_obj) {

                    if(response_obj.status === 'success') {
                        var picture_id = response_obj.data[0]['pictures'][0].id;
                        var picture_url = response_obj.data[0]['pictures'][0].url; // TODO
                        var html = substitute(imageItemTpl, {id: picture_id, url: picture_url});
                        container.find('.image-item').last().after(html);
                    }
                });
            });
    };
    

    $('.add-image').click(function(){
        var _this = $(this);
        var container = _this.parent();
        upload_image(container);
    });

    var delete_picture = function(target) {
        var picture_cnt = target.parent().find('.image-item').length;
        if(picture_cnt === 1) {
            alert('Can\'t delete this picture');
            return false;
        }
        
        if(confirm("Sure you want to delete this picture?")) {
            var product_id = target.closest('.create-products-form').find('.product-id').val();
            var picture_id = target.attr('picture-id');
            post('/api/deleteproductpicture', {product_id: product_id, picture_id: picture_id}, function(response_obj) {

                if(response_obj.status === 'success') {
                    target.remove();
                }
            });
        }
        return false;
    };
    
    // delete a product image
    $('.product-details').on('click', '.delete-image', function() {
        delete_picture($(this).parent());
    });

    // sortable image
    $('.product-details .js-sortable').sortable({
        items: '.image-item',
        stop: function(e, ui) {
            var _this = $(this);
            var panel = _this.parent().find('.image-item');

            if(panel.length <= 1) {
                return;
            }
            var product_id = _this.closest('.create-products-form').find('.product-id').val();
            var products = [];
            var pictures = [];

            $.each(panel, function(i, pic){
                var picture = {};
                picture.id = $(pic).attr('picture-id');
                picture.orderby = i;
                pictures.push(picture);
            });
            var product = {};
            product.id = product_id;
            product.pictures = pictures;
            products.push(product);

            post('/api/createproducts', {products: JSON.stringify(products)}, function(response_obj) {});
        }
    });


    // validate & make ajax for each element
    $('.auto-save-field').change(function() {
        var _this = $(this);

        if(_this.attr('data-invalid') !== '') {
            var field = _this.attr('name');
            var value = _this.val();
            if(field === 'categories' && value !== '') {

                var input_tags = value.split(',');
                var tags = [];
                var i = 0;
                for(i; i<input_tags.length; i++) {
                    var tag = {};
                    tag.description = input_tags[i];
                    tag.category = input_tags[i];
                    tags.push(tag);
                }
                value = tags;
            }

            var form = _this.closest('.create-products-form');
            var product_id = form.find('.product-id').val();

            var products = [];
            var product = {};
            product['id'] = product_id;
            product[field] = value;
            products.push(product);

            post('/api/createproducts', {products: JSON.stringify(products)}, function(response){
                console.log(response);
                if(response.status === 'success') {
                    utils.alertBox({
                        container: form.find('.alert-field'),                    
                        type: 'success',
                        autohide: 'true'
                    });
                    
                } else {
                    utils.alertBox({
                        container: form.find('.alert-field'),
                        type: 'error'
                    });
                }
            });
        }
    });
};
