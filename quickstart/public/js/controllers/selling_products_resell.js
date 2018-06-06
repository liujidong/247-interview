shopinterest.controllers.selling_products_resell = new function() {

    var utils = shopinterest.common.utils,
        substitute = utils.substitute,
        spinner = utils.spinner,
        post = utils.post,
        uniqid = utils.uniqid,
        convert = utils.convert,
        imageItemTpl = '<li class="image-item" picture-id={{picture_id}} product-id={{product_id}}>\n' +
            '<img src="{{url}}">\n' +
            '<a href="javascript:;" class="delete-image">&times;</a>\n' +
            '</li>';

    var create_products_lightbox = new shopinterest.modules.create_resell_products_lightbox();
    create_products_lightbox.render();
    
    $('.addnew-product').click(function(){
        create_products_lightbox.show();
    });

    var create_product = function(item) {
        item.on('invalid', function(e) {

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
                    _this.find('.product-id').val(response.data[0].id);
                }

                spinner.close();
            });
        });
    };
    
    var delete_product = function(item) {
        var product_id = item.find('.product-id').val();
        if(!product_id) {
            item.remove();            
        } else if(confirm("Sure you want to delete this product?")) {
            post('/api/delete-product', {product_id: product_id}, function(response){
                if(response.status === 'success') {
                    item.remove();
                } else {
                    utils.alertBox({
                        container: item.find('.alter-field'),
                        type: 'error'
                    });
                }
            });
        }

    };

    var upload_image = function(item) {

        var container = item.find('.images');
        var length = container.find('.image-item').length;
        if(length >= 5) {
            alert("Sorry, you can't upload more pictures");
            return;
        }

        var product_id = item.find('.product-id').val();
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
                if(product_id) {
                    product.id = product_id;
                }
                products.push(product);

                post('/api/createproducts', {products: JSON.stringify(products)}, function(response_obj) {

                    if(response_obj.status === 'success') {
                        var product_id = response_obj.data[0].id;
                        var picture_id = response_obj.data[0]['pictures'][0].id;
                        var picture_url = response_obj.data[0]['pictures'][0]['converted_pictures'][2]['url'];
                        var html = substitute(imageItemTpl, {product_id: product_id, picture_id: picture_id, url: picture_url});
                        item.find('.product-id').val(product_id);
                        item.find('.has_image').val(1);
                        container.find('.add-image').before(html);

                        var newly_added_elem = container.find('.image-item').last();
                        newly_added_elem.find('.delete-image').click(function(){
                            delete_picture(newly_added_elem);
                        });
                    }
                });
            });
    };

    var delete_picture = function(image_item) {
        var picture_cnt = image_item.parent().find('.image-item').length;
        if(picture_cnt === 1) {
            alert('Can\'t delete this picture');
            return;
        }    
        if(confirm("Sure you want to delete this picture?")) {
            var product_id = image_item.attr('product-id');
            var picture_id = image_item.attr('picture-id');
            post('/api/deleteproductpicture', {product_id: product_id, picture_id: picture_id}, function(response_obj) {

                if(response_obj.status === 'success') {
                    image_item.remove();
                }
            });
        }
    };

    // sortable image
    var sort_image = function(item) {
        item.find('.js-sortable').sortable({
            items: '.image-item',
            stop: function(e, ui) {
                var _this = $(this);
                var panel = _this.parent().find('.image-item');
                
                if(panel.length <= 1) {
                    return;
                }
                var product_id = panel.attr('product-id');
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
    };
    
    var item_event_handler = function (item) {
        var delete_btn = item.find('.delete-button');
        delete_btn.unbind('click');
        delete_btn.bind('click', function() {
            delete_product(item);
        });

        var save_btn = item.find('.save-button');
        save_btn.unbind('click');
        save_btn.bind('click', function(){
            create_product(item);
        });

        var add_image_btn = item.find('.add-image');
        add_image_btn.unbind('click');
        add_image_btn.bind('click', function(){
            upload_image(item);
        });

        var delete_image_btn = item.find('.delete-image');
        $.each(delete_image_btn, function(i, btn){
            $(this).unbind('click')
                .bind('click', function(){
                    delete_picture($(this).parent());
                });
        });

        sort_image(item);
    };
    
    $.each($('.product-item'), function(i, item){
        item_event_handler($(item));
    });
};
