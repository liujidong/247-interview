
shopinterest.modules.snsearch_item = function() {

    var module_name = 'snsearch_item';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var outnetwork = null;
    var innetwork = null;
    var add_product = null;
    var store_id = 0;
    var product_id = 0;
    var _this = this;

    _this.render = function(tgt,product) {
        var template = shopinterest.templates.snsearch_item;
        var html = substitute(template, {id: id, product: product}); 
        tgt.html(html);
        container = $('#'+id);
        outnetwork = container.find('.outnetwork');
        innetwork = container.find('.innetwork');
        add_product = container.find('.add_product');  
        store_id = add_product.attr('store_id');
        product_id = add_product.attr('product_id');
        add_product = container.find('.add_product');         
        bindUI();
    };
    
    _this.show = function() {
        container.show();
    };
    
    var bindUI = function() {
        add_product.click(function(){
            $.post('/api/add2sn', {store_id: store_id, product_id: product_id}, function(response) {
                var response_obj = $.parseJSON(response);
                if(response_obj.status === 'success') {
                    outnetwork.hide();
                    innetwork.show();
                }
            });            
        });
    };
};


