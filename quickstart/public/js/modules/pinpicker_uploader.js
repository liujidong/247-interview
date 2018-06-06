
shopinterest.modules.pinpicker_uploader = function() {
    
    var module_name = 'pinpicker_uploader';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var added_pins = null;
    var upload_button = null;
    var store_id = $('#pinpicker').attr('store-id');
    var _this = this;
    
    
    _this.render = function(tgt) {
        var template = shopinterest.templates[module_name];
        //console.log(template_listitem);
        var html = substitute(template, {id: id}); 
        tgt.html(html);
        container = $('#'+id);
        added_pins = container.find('.added_pins');
        upload_button = container.find('.btn-upload');
        bindUI();
    };
    
    _this.add_pin = function(pin_id, pin_url, pin_description) {
        var template_listitem = shopinterest.templates.pinpicker_uploader_listitem;
        var html = substitute(template_listitem, {pin_id: pin_id, pin_url: pin_url, pin_description: pin_description});
        added_pins.append(html);
    };
    
    _this.remove_pin = function(pin_id) {
        var selector = substitute('li[pin-id={{pin_id}}]', {pin_id: pin_id});
        console.log(selector);
        added_pins.find(selector).remove();
    };
    
    var bindUI = function() {
        upload_button.click(function(e) {

            container.trigger('pinpicker_upload:start');
            
            var selected_pins = added_pins.find('li');
            var products = [];
            
            selected_pins.each(function(index, pin) {
                pin = $(pin);
                var pin_url_45 = pin.find('.thumbnail-image').attr('src');
                var pin_description = pin.find('.multi-filename').html();
                var pin_urls = utils.get_pinterest_image_urls(pin_url_45);     
                
                var product = {};
                product.pictures = [];
                var picture = {};  
                picture.source = 'pinterest';
                picture.type = 'original';
                picture.url = pin_urls[5];
                product.pictures.push(picture);
                product.description = pin_description;
                products.push(product);
            });
            
            $.post('/api/createproducts', {products: JSON.stringify(products)}, function(response) {
                var response_obj = JSON.parse(response);
                if(response_obj.status === 'success') {
                    container.trigger('pinpicker_upload:finish');
                }
            });               
        }); 
    };
};
