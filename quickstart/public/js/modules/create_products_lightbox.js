
shopinterest.modules.create_products_lightbox = function() {
    
    var module_name = 'create_product_lightbox';
    var utils = shopinterest.common.utils;
    var get_upload_dst = utils.get_product_image_upload_dst2;
    var get_csv_upload_dst = utils.get_csv_upload_dst;
    var uniqid = utils.uniqid;
    var _this = this;
    var container = null;
    var binded = false;
    var convert = utils.convert;
    var spinner = utils.spinner;
    
    _this.render = function() {
        var template = shopinterest.templates.create_products_lightbox;
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
        
        if(binded !== false) {
            return;
        }
        binded = true;
        
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
                        var picture = {};
                        var product = {};
                        product.pictures = [];
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
                            window.location.href = "/selling/products?status=inactive";
                        }
                    });   
                }); 
            };
            
            // etsy import work flow
            if(e.from === 'etsy_import') {

                var csv_dst = get_csv_upload_dst();
                filepicker.pick({
                        extension: '.csv',
                        services:['COMPUTER', 'GOOGLE_DRIVE', 'DROPBOX', 'BOX', 'SKYDRIVE', 'URL','FTP', 'GMAIL']
                    },
                    function(InkBlob) {
                        spinner.show();
                        $.post('/api/importproductsfromcsv', {csv_file_url: InkBlob.url}, function(response) {
                            var response_obj = JSON.parse(response);   
                            if(response_obj.status === 'success') {
                                spinner.close();
                                alert("Please check 'View products' after your email notification is received.");
                            } else {
                                alert("We're sorry, something seems to be wrong on our end, please try it later");
                            }
                        });                
                  }
                );    
            }          
        });
        var pinpicker_lightbox = new shopinterest.modules.pinpicker_lightbox();
        pinpicker_lightbox.render();
        container.bind('pinterest:popup', function(e) {
            e.preventDefault();
            pinpicker_lightbox.show();    
        });
        
        var etsy_import_lightbox = new shopinterest.modules.etsy_import_lightbox();
        etsy_import_lightbox.render();
        container.bind('etsy:popup', function(e) {
            e.preventDefault();
            etsy_import_lightbox.show();     
        });            
        
        var csv_import_lightbox = new shopinterest.modules.csv_import_lightbox();
        csv_import_lightbox.render();
        container.bind('csv:popup', function(e) {
            e.preventDefault();
            csv_import_lightbox.show(); 
        });
    };
};
