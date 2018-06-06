
shopinterest.modules.create_resell_products_lightbox = function() {

    var module_name = 'create_resell_product_lightbox';
    var utils = shopinterest.common.utils;
    var get_upload_dst = utils.get_product_image_upload_dst2;
    var get_csv_upload_dst = utils.get_csv_upload_dst;
    var uniqid = utils.uniqid;
    var _this = this;
    var container = null;
    var convert = utils.convert;
    var spinner = utils.spinner;
    
    _this.render = function() {
        var template = shopinterest.templates.create_resell_products_lightbox;
        var html = template; 
        $('body').append(html);
        container = $('#'+ module_name);    
    };
    
    _this.show = function() {    
        container.show();   
        bindUI();    
    };
    
    _this.close = function() {
        container.hide(); 
    };

    var bindUI = function() {
                
        container.bind('create_product_lightbox:close', function(e) {
            e.preventDefault();
            container.hide(); 
        });        
        
        container.bind('filepicker:popup', function(e) {
            //console.log('***********filepicker triggered');
            e.preventDefault();
            
            // create products from pc && social work flow            
            if(e.from === 'social_import') {
                
                filepicker.pickMultiple(function(inkBlobs) {

                    // start to show spinner
                    spinner.show();      
                    
                    var products = [];                 

                    $.each(inkBlobs ,function(index, inkBlob) {
                        var product = {};
                        product.pictures = [];
                        product.resell = 1;

                        var picture = {};
                        picture.url = inkBlob.url;
                        picture.type = 'original';
                        picture.source = 'filepicker';
                        product.pictures.push(picture);
                        products.push(product);
                    });
                    
                    $.post('/api/createproducts', {products: JSON.stringify(products)}, function(response) {
                        var response_obj = JSON.parse(response);
                        if(response_obj.status === 'success') {
                            spinner.close();
                            window.location.reload();
                        }
                    });   
                }); 
            };
        });
    };
};

