
shopinterest.modules.popup_tags = function() {

    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;    
    var container = null;
    var popup_tags_textarea = null; 
    var exist_tags = null;    
    var _requester = null;
    var _this = this;

    _this.render = function(tgt) {
        var html = shopinterest.templates.popup_tags;
        tgt.append(html);
        container = $('#popup-tags');
        popup_tags_textarea = container.find('#popup-tags-textarea');
        bindUI();
    };
    
    // input tags is a array like ['M17', 'High performance', 'Gaming']
    _this.show = function(requester, tags) {   
        exist_tags = tags;
        _requester = $(requester);
        popup_tags_textarea.val('');
        container.reveal({
            dismissModalClass:'button-cancel-popup'
        });
    };

    var bindUI = function() {
        
        // save button click:package tags text into an array, pase this array to tags section
        container.on('click', '.save-tag-popup', function(e){
            e.preventDefault();
            var text = popup_tags_textarea.val();
            var tags = text.split(',');
            var bad_tags = $.grep(tags, function(e, i){return e.length > 16;});
            if(bad_tags.length > 0) {
                alert("Tag should be shorter than 16 characters.");
                return;
            }
            var html = _package(tags);
            _requester.before(html);
            container.trigger('reveal:close');
        });     
       
    }; 
    
    var _package = function(tags) {
        var prod_tags = '';
        $.each(tags, function(i, tag){
            if($.inArray(tag, exist_tags) === -1) {
                
                // save tag
                var product_id = _requester.closest('.operation-section').find('.product_id').val();
                var products = [];
                var product = {
                    id:product_id
                };
                product.categories = [];
                var category = {
                    category: tag,
                    description: tag
                };
                product.categories.push(category);
                products.push(product);  
                $.post('/api/createproducts', {products: JSON.stringify(products)}, function(response){});      
                
                var template = shopinterest.templates.tag;
                var html = substitute(template, {tag: tag});
                prod_tags += html;
            }
        });
        return prod_tags;
    };
    
    var unpackage = function(tags) {
        // convert array to string
        return (tags + "");     
    };    
};
