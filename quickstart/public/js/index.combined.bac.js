var env = $('body').attr('env');
if(env === '') env = 'staging';

$.cookie.saveObj = function(key, value, expires) {
    if($.isPlainObject(value)) {
        value = JSON.stringify(value);
        $.cookie(key, value, {domain: window.location.hostname, expires: expires});
    }
};

$.cookie.getObj = function(key) {
    return JSON.parse($.cookie(key, {domain: window.location.hostname}));
};

$.cookie.defaults.domain = window.location.hostname;
$.cookie.defaults.path = '/';

// combo css loader
$.getCSS = function() {
    var i=0, size=arguments.length;
    var callback;
    var path = '';
    
    for(;i<size;i++) {
        if((typeof arguments[i])!=='function') {
            if(i==0) {
                path += arguments[i];
            } else {
                path += ';'+ arguments[i];
            }
        } else {
            callback = arguments[i];
        }
        if(path) {
            loadingURL = 'http://'+window.location.host+'/api/'+'combo'+'?f='+path;
            $.get(loadingURL, function(data) {
                $("<style type=\"text/css\">" + data + "</style>").appendTo(document.head);
                callback(data, status);
            });
        }
    }
};

is_object = function(str) {
    var obj;
    try {
        obj = eval(str);
        if(obj !== undefined) {
            return true;
        } else {
            return false;
        }
    } catch(e) {
        return false;
    }
};

// create the map to the modules
var shopinterest = {
    'map': {
        //libs
        'libs-fbapi': {
            'path': 'js/libs/fbapi.js',
            'loaded': 0
        },
        'libs-twapi': {
            'path': 'js/libs/twapi.js',
            'loaded': 0
        },
        // common
        'common-utils': {
            'path': 'js/common/utils.js',
            'loaded': 0
        },
        // controllers
        'controllers-base': {
            'path': 'js/controllers/base.js',
            'loaded': 0,
            'requires': ['modules-spinner']
        },
        'controllers-index_index': {
            'path': 'js/controllers/index_index.js',
            'loaded': 0
        },
        'controllers-merchant_selectpins': {
            'path': 'js/controllers/merchant_selectpins.js',
            'loaded': 0
        },
        'controllers-products_create': {
            'path': 'js/controllers/products_create.js',
            'loaded': 0
        },
        'controllers-products_index': {
            'path': 'js/controllers/products_index.js',
            'loaded': 0
        },
        'controllers-merchant_selectboards': {
            'path': 'js/controllers/merchant_selectboards.js',
            'loaded': 0
        },
        'controllers-merchant_customers': {
            'path': 'js/controllers/merchant_customers.js',
            'loaded': 0
        },
        'controllers-store_index':{
            'path': 'js/controllers/store_index.js',
            'loaded': 0,
            'requires' : ['templates-product_reveal']
        },
        'controllers-preview_index':{
            'path': 'js/controllers/preview_index.js',
            'loaded': 0
        },
        'controllers-products_import':{
            'path': 'js/controllers/products_import.js',
            'loaded': 0
        },
        'controllers-join-now_index':{
            'path': 'js/controllers/join-now_index.js',
            'loaded': 0
        },
        'controllers-start-free_index':{
            'path': 'js/controllers/start-free_index.js',
            'loaded': 0
        },
        'controllers-admin_index':{
            'path': 'js/controllers/admin_index.js',
            'loaded': 0
        },  
        'controllers-admin_futurepay':{
            'path': 'js/controllers/admin_futurepay.js',
            'loaded': 0
        },  
        'controllers-admin_payhistory':{
            'path': 'js/controllers/admin_payhistory.js',
            'loaded': 0
        },        
        'controllers-admin_flashdeal':{
            'path': 'js/controllers/admin_flashdeal.js',
            'loaded': 0
        },
        'controllers-admin_category':{
            'path': 'js/controllers/admin_category.js',
            'loaded': 0
        },
        'controllers-admin_tags':{
            'path': 'js/controllers/admin_tags.js',
            'loaded': 0
        },
        'controllers-flashdealstest_index':{
            'path': 'js/controllers/flashdealstest_index.js',
            'loaded': 0
        },
        'controllers-flashdeals_index':{
            'path': 'js/controllers/flashdeals_index.js',
            'loaded': 0
        },
        'controllers-test_emaillightbox':{
            'path': 'js/controllers/test_emaillightbox.js',
            'loaded': 0
        },    
        'controllers-search_index':{
            'path': 'js/controllers/search_index.js',
            'loaded': 0
        },
        'controllers-associate_login':{
            'path': 'js/controllers/associate_login.js',
            'loaded': 0
        },
        'controllers-associate_search':{
            'path': 'js/controllers/associate_search.js',
            'loaded': 0
        },
        'controllers-associate_products':{
            'path': 'js/controllers/associate_products.js',
            'loaded': 0
        },
        'controllers-associate_profile':{
            'path': 'js/controllers/associate_profile.js',
            'loaded': 0
        },
        'controllers-associate_sales':{
            'path': 'js/controllers/associate_sales.js',
            'loaded': 0
        },
        'controllers-login_index':{
            'path': 'js/controllers/login_index.js',
            'loaded': 0
        },
        'controllers-products_categories':{
            'path': 'js/controllers/products_categories.js',
            'loaded': 0
        },
        'controllers-profile_index':{
            'path': 'js/controllers/profile_index.js',
            'loaded': 0
        },
        'controllers-feedback_index':{
            'path': 'js/controllers/feedback_index.js',
            'loaded': 0
        },
        'controllers-orders_item':{
            'path': 'js/controllers/orders_item.js',
            'loaded': 0
        },    
        'controllers-products_item':{
            'path': 'js/controllers/products_item.js',
            'loaded': 0
        },
        'controllers-account_settings':{
            'path': 'js/controllers/account_settings.js',
            'loaded': 0
        },        
        'controllers-admin_abtests':{
            'path': 'js/controllers/admin_abtests.js',
            'loaded': 0
        }, 
        'controllers-pay_return':{
            'path': 'js/controllers/pay_return.js',
            'loaded': 0
        },         
        'controllers-affiliatestore_index': {
            'path': 'js/controllers/affiliatestore_index.js',
            'loaded': 0
        },
        'controllers-admin_featuredproduct': {
            'path': 'js/controllers/admin_featuredproduct.js',
            'loaded': 0
        },  
        'controllers-merchant_products': {
            'path': 'js/controllers/merchant_products.js',
            'loaded': 0,
            'requires' : ['modules-popup_tags','modules-popup_shipping','modules-popup_custom_field', 'templates-expand_pic']
        },    
        'controllers-iframe_pinpicker': {
            'path': 'js/controllers/iframe_pinpicker.js',
            'loaded': 0,
            'requires': ['modules-pinpicker_uploader', 'modules-pinpicker_selectboards', 'modules-pinpicker_selectpins']
        },   
        'controllers-iframe_createproducts': {
            'path': 'js/controllers/iframe_createproducts.js',
            'loaded': 0
        }, 
        'controllers-iframe_etsyimport': {
            'path': 'js/controllers/iframe_etsyimport.js',
            'loaded': 0
        },      
        'controllers-iframe_csvimport': {
            'path': 'js/controllers/iframe_csvimport.js',
            'loaded': 0
        },  
        'controllers-admin_closeaccount': {
            'path': 'js/controllers/admin_closeaccount.js',
            'loaded': 0
        },                    
        // UI modules
        'modules-category_creator': {
            'path': 'js/modules/category_creator.js',
            'loaded': 0
        },
        'modules-refresh_button': {
            'path': 'js/modules/refresh_button.js',
            'loaded': 0
        },
        'modules-fbfeed_button': {
            'path': 'js/modules/fbfeed_button.js',
            'loaded': 0
        },
        'modules-fbconnect_button': {
            'path': 'js/modules/fbconnect_button.js',
            'loaded': 0
        },
        'modules-tweet_button': {
            'path': 'js/modules/tweet_button.js',
            'loaded': 0
        },
        'modules-fbsend_button': {
            'path': 'js/modules/fbsend_button.js',
            'loaded': 0
        },
        'modules-pinstore_button': {
            'path': 'js/modules/pinstore_button.js',
            'loaded': 0
        },
        'modules-pinstore_lightbox': {
            'path': 'js/modules/pinstore_lightbox.js',
            'loaded': 0
        },
        'modules-pin_button': {
            'path': 'js/modules/pin_button.js',
            'loaded': 0
        },
        'modules-email_lightbox': {
            'path': 'js/modules/email_lightbox.js',
            'loaded': 0
        },
        'modules-signup_lightbox': {
            'path': 'js/modules/signup_lightbox.js',
            'loaded': 0
        },
        'modules-snsearch_item': {
            'path': 'js/modules/snsearch_item.js',
            'loaded': 0
        },
        'modules-password_editor': {
            'path': 'js/modules/password_editor.js',
            'loaded': 0
        },
        'modules-contact_merchant': {
            'path': 'js/modules/contact_merchant.js',
            'loaded': 0
        },  
        'modules-shiptrack_lightbox': {
            'path': 'js/modules/shiptrack_lightbox.js',
            'loaded': 0
        },
        'modules-pinpicker_lightbox': {
            'path': 'js/modules/pinpicker_lightbox.js',
            'loaded': 0,
            'requires': ['templates-pinpicker_lightbox']
        },
        'modules-pinpicker_uploader': {
            'path': 'js/modules/pinpicker_uploader.js',
            'loaded': 0,
            'requires': ['templates-pinpicker_uploader', 'templates-pinpicker_uploader_listitem', 'modules-spinner']
        },
        'modules-pinpicker_selectpins': {
            'path': 'js/modules/pinpicker_selectpins.js',
            'loaded': 0,
            'requires': ['templates-pinpicker_selectpins']
        },
        'modules-pinpicker_selectboards': {
            'path': 'js/modules/pinpicker_selectboards.js',
            'loaded': 0,
            'requires': ['templates-pinpicker_selectboards']
        },
        'modules-create_products_lightbox': {
            'path': 'js/modules/create_products_lightbox.js',
            'loaded': 0,
            'requires': ['templates-create_products_lightbox', 'modules-csv_import_lightbox', 'modules-spinner', 'modules-pinpicker_lightbox', 'modules-etsy_import_lightbox']
        },
        'modules-etsy_import_lightbox': {
            'path': 'js/modules/etsy_import_lightbox.js',
            'loaded': 0,
            'requires' : ['templates-etsy_import_lightbox']
        }, 
        'modules-csv_import_lightbox': {
            'path': 'js/modules/csv_import_lightbox.js',
            'loaded': 0,
            'requires': ['templates-csv_import_lightbox']
        },   
        'modules-spinner': {
            'path': 'js/modules/spinner.js',
            'loaded': 0,
            'requires': ['templates-spinner']
        },    
        'modules-popup_tags': {
            'path': 'js/modules/popup_tags.js',
            'loaded': 0,
            'requires': ['templates-popup_tags', 'templates-tag']
        },
        'modules-popup_shipping': {
            'path': 'js/modules/popup_shipping.js',
            'loaded': 0,
            'requires': ['templates-popup_shipping']
        },
        'modules-popup_custom_field': {
            'path': 'js/modules/popup_custom_field.js',
            'loaded': 0,
            'requires': ['templates-popup_custom_field']
        },                
        // templates
        'templates-category_creator': {
            'path': 'js/templates/category_creator.js',
            'loaded': 0
        },
        'templates-refresh_button': {
            'path': 'js/templates/refresh_button.js',
            'loaded': 0
        },
        'templates-fbfeed_button': {
            'path': 'js/templates/fbfeed_button.js',
            'loaded': 0
        },
        'templates-fbconnect_button': {
            'path': 'js/templates/fbconnect_button.js',
            'loaded': 0
        },
        'templates-tweet_button': {
            'path': 'js/templates/tweet_button.js',
            'loaded': 0
        },
        'templates-fbsend_button': {
            'path': 'js/templates/fbsend_button.js',
            'loaded': 0
        },
        'templates-fbapi': {
            'path': 'js/templates/fbapi.js',
            'loaded': 0
        },
        'templates-pinstore_button': {
            'path': 'js/templates/pinstore_button.js',
            'loaded': 0
        },
        'templates-pinstore_lightbox': {
            'path': 'js/templates/pinstore_lightbox.js',
            'loaded': 0
        },
        'templates-pinstore_lightbox_form': {
            'path': 'js/templates/pinstore_lightbox_form.js',
            'loaded': 0
        },
        'templates-pin_button': {
            'path': 'js/templates/pin_button.js',
            'loaded': 0
        },
        'templates-email_lightbox': {
            'path': 'js/templates/email_lightbox.js',
            'loaded': 0
        },
        'templates-signup_lightbox': {
            'path': 'js/templates/signup_lightbox.js',
            'loaded': 0
        },
        'templates-snsearch_item': {
            'path': 'js/templates/snsearch_item.js',
            'loaded': 0
        },
        'templates-password_editor': {
            'path': 'js/templates/password_editor.js',
            'loaded': 0
        },
        'templates-contact_merchant': {
            'path': 'js/templates/contact_merchant.js',
            'loaded': 0
        },
        'templates-shiptrack_lightbox': {
            'path': 'js/templates/shiptrack_lightbox.js',
            'loaded': 0
        },
        'templates-pinpicker_lightbox': {
            'path': 'js/templates/pinpicker_lightbox.js',
            'loaded': 0
        },
        'templates-pinpicker_uploader': {
            'path': 'js/templates/pinpicker_uploader.js',
            'loaded': 0
        },
        'templates-pinpicker_uploader_listitem': {
            'path': 'js/templates/pinpicker_uploader_listitem.js',
            'loaded': 0
        },
        'templates-pinpicker_selectpins': {
            'path': 'js/templates/pinpicker_selectpins.js',
            'loaded': 0
        },
        'templates-pinpicker_selectboards': {
            'path': 'js/templates/pinpicker_selectboards.js',
            'loaded': 0
        },
        'templates-create_products_lightbox': {
            'path': 'js/templates/create_products_lightbox.js',
            'loaded': 0
        },
        'templates-etsy_import_lightbox': {
            'path': 'js/templates/etsy_import_lightbox.js',
            'loaded': 0
        },
        'templates-csv_import_lightbox': {
            'path': 'js/templates/csv_import_lightbox.js',
            'loaded': 0
        },
        'templates-spinner': {
            'path': 'js/templates/spinner.js',
            'loaded': 0
        },
        'templates-popup_tags': {
            'path': 'js/templates/popup_tags.js',
            'loaded': 0
        },
        'templates-popup_shipping': {
            'path': 'js/templates/popup_shipping.js',
            'loaded': 0
        },
        'templates-tag': {
            'path': 'js/templates/tag.js',
            'loaded': 0
        },
        'templates-popup_custom_field': {
            'path': 'js/templates/popup_custom_field.js',
            'loaded': 0
        },
        'templates-product_reveal': {
            'path': 'js/templates/product_reveal.js',
            'loaded': 0
        },
        'templates-expand_pic': {
            'path': 'js/templates/expand_pic.js',
            'loaded': 0
        },   
        'templates-green_tips': {
            'path': 'js/templates/green_tips.js',
            'loaded': 0
        },  
        'templates-red_tips': {
            'path': 'js/templates/red_tips.js',
            'loaded': 0
        }, 
        'templates-spinner_tips': {
            'path': 'js/templates/spinner_tips.js',
            'loaded': 0
        },                 
    },
    'combo': 'http://'+window.location.host+'/api/'+'combo',
    'common': {
        
    },
    'controllers': {
        
    },
    'modules': {
        
    },
    'templates': {
    },
    'libs': {
        
    },
    'use': function() {
        
        var i=0, j=0, size=arguments.length;
        var path = '';
        var callback = '';
        var loadingURL = '';
        var _this = this;
        var callback;
        var modules = [];
        var requires = [];
        var required_modules = [];
        function getRequiredModules(module) {
            var module_str = 'shopinterest.'+module.replace('-', '.');
            if(is_object(module_str)) {
                return;
            } else if($.inArray(module, _this.map[module].requires) === -1) {
                required_modules.push(module);
            }
            if($.isArray(_this.map[module].requires)) {
                var k = 0;
                var num_requires = _this.map[module].requires.length;
                for(;k<num_requires;k++) {
                    getRequiredModules(_this.map[module].requires[k]);
                }
            }
        }
        for(;i<size;i++) {
            if(!$.isFunction(arguments[i])) {
                getRequiredModules(arguments[i]);
            } else {
                callback = arguments[i];
            }

        }
        var num_requires = required_modules.length;
        for(j=num_requires-1;j>=0;j--) {
            var module_str = 'shopinterest.'+required_modules[j].replace('-', '.');
            if(!is_object(module_str)) {
                if(path === '') {
                    path += _this.map[required_modules[j]].path;
                } else {
                    path += ';' + _this.map[required_modules[j]].path;
                }
                modules.push(_this.map[required_modules[j]]);
            }
        }
        if(path) {
            loadingURL = _this.combo+'?f='+path;
            $.getScript(loadingURL, function(data, status) {
//                var j=0, size2=modules.length;
//                for(;j<size2;j++) {
//                    modules[j].loaded=1;
//                }
                callback(_this);
            });
        } else {
            callback(_this);
        }
        
    },
    constants: {
        base_url: 'http://'+window.location.host,
        base_service_url: 'http://'+window.location.host+'/api',
        facebook_service_url: 'http://'+window.location.host+'/facebook',
        'categories': {
            'signup': 'SIGNUP',
            'shopping': 'SHOPPING',
            'social': 'SOCIAL'
        },
        fb_app_id: '212167575573301',
        's3_base_url': env === 'production'?'http://s3.amazonaws.com/shopinterest_production':'http://s3.amazonaws.com/shopinterest_stage'
    },
    facebook: {
        login_status: '',
        access_token: ''
    }
    
};

$(document).ready(function() {
    
    /*
     * put the most common used js files here
     * libs, moduels, templates, but not controllers
     */
    
    
    shopinterest.modules.fbfeed_button = function() {

        var module_name = 'fbfeed_button';
        var utils = shopinterest.common.utils;
        var substitute = utils.substitute;
        var id = utils.getModuleId(module_name);
        var container = null;
        var _this = this;
        var fbapi = shopinterest.libs.fbapi;
        var feed = null;
        var success = null;
        var failure = null;
        var button_icon = null;

        _this.render = function(tgt, feed_in, success_in, failure_in) {
            feed = feed_in;
            button_icon = feed.button_icon;
            success = success_in;
            failure = failure_in;
            var template = shopinterest.templates.fbfeed_button;
            var html = substitute(template, {id: id, img_src: button_icon.img_src, width: button_icon.width, height: button_icon.height}); 
            tgt.html(html);
            container = $('#'+id);
            bindUI();
        };

        var bindUI = function() {
            container.on('click', 'img', function(e) {
                e.preventDefault();
                fbapi.showFeedDialog(feed, success, failure);

            });
        };


    };

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.fbfeed_button='<a href=\"\" id=\"{{id}}\"><img width=\"{{width}}\" height=\"{{height}}\" src=\"{{img_src}}\"></a>';

    
    shopinterest.modules.fbsend_button = function() {

        var module_name = 'fbsend_button';
        var utils = shopinterest.common.utils;
        var substitute = utils.substitute;
        var id = utils.getModuleId(module_name);
        var container = null;
        var _this = this;
        var fbapi = shopinterest.libs.fbapi;
        var msg = null;
        var button_icon = '';

        _this.render = function(tgt, msg_in) {
            msg = msg_in;
            button_icon = msg.button_icon;
            var template = shopinterest.templates.fbsend_button;
            var html = substitute(template, {id: id, img_src: button_icon.img_src, width: button_icon.width, height: button_icon.height}); 
            tgt.html(html);
            container = $('#'+id);
            bindUI();
        };

        var bindUI = function() {
            container.on('click', 'img', function(e) {
                e.preventDefault();
                fbapi.showSendDialog(msg);

            });
        };


    };
    
    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.fbsend_button='<a href=\"\" id=\"{{id}}\"><img width=\"{{width}}\" height=\"{{height}}\" src=\"{{img_src}}\"></a>';

    
    shopinterest.modules.tweet_button = function() {

        var module_name = 'tweet_button';
        var utils = shopinterest.common.utils;
        var substitute = utils.substitute;
        var id = utils.getModuleId(module_name);
        var container = null;
        var _this = this;
        var twapi = shopinterest.libs.twapi;
        var tweet = null;
        var success = null;
        var failure = null;
        var button_icon = '';

        _this.render = function(tgt, tweet_in, success_in, failure_in) {
            tweet = tweet_in;
            button_icon = tweet.button_icon;
            var template = shopinterest.templates.tweet_button;
            var html = substitute(template, {id: id, url: tweet.url, via: tweet.via, text: tweet.text, img_src: button_icon.img_src, width: button_icon.width, height: button_icon.height}); 
            tgt.html(html);
            container = $('#'+id);
            bindUI();
        };

        var bindUI = function() {
            container.on('click', 'a', function(e) {
                e.preventDefault();

            });
        };


    };
    
    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.tweet_button='<a href=\"https://twitter.com/intent/tweet?{{#url}}url={{url}}&{{/url}}{{#via}}via={{via}}&{{/via}}{{#text}}text={{text}}{{/text}}\"><img width=\"{{width}}\" height=\"{{height}}\" src=\"{{img_src}}\"></a>';

    
    shopinterest.modules.contact_merchant = function() {

        var module_name = 'contact_merchant';
        var utils = shopinterest.common.utils;
        var substitute = utils.substitute;
        var id = utils.getModuleId(module_name);
        var container = null;
        var _this = this;
        var toemail_box = null;
        var toname_box= null;
        var reply_box= null;
        var subject_box = null;
        var content_box = null;
        var send_button = null;
        var alert_box_success = null;
        var alert_box_error = null;

        _this.render = function(tgt) {
            var template = shopinterest.templates.contact_merchant;
            var html = substitute(template, {id: id});
            tgt.append(html);
            container = $('#'+id);
            toemail_box = container.find('.contact_merchant_toemail');
            toname_box = container.find('.contact_merchant_toname'); 
            reply_box = container.find('.contact_merchant_replyto');         
            subject_box = container.find('.contact_merchant_subject');
            content_box = container.find('.contact_merchant_content');
            send_button = container.find('.contact_merchant_submit');
            alert_box_success = container.find('.alert-box.success');
            alert_box_error = container.find('.alert-box.alert');

            bindUI();
        };

        _this.show = function(email, name) {
            alert_box_error.hide();
            alert_box_success.hide();
            toemail_box.val(email);
            toname_box.val(name);
            $('#'+id).reveal();
        };

        var bindUI = function() {
            send_button.click(function(e) {

                $.post('/api/sendemail', {
                    toemail: toemail_box.val(), 
                    toname: toname_box.val(),
                    subject: subject_box.val(),
                    text: content_box.val(),
                    replyto: reply_box.val()
                }, function(response) {
                    var response_obj = $.parseJSON(response);
                    if(response_obj.status === 'success') {
                        alert_box_success.show();
                        container.slideUp('slow', function() {
                            container.trigger('reveal:close');
                        });
                    } else {
                        alert_box_error.html(response_obj.data.errors[0].msg);
                        alert_box_error.show();
                    }
                });

            });

            container.bind('reveal:close', function(e) {
                reply_box.val('');
                subject_box.val('');
                content_box.val('');
            });
        };
    };

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.contact_merchant='<div id=\"{{id}}\" class=\"reveal-modal contact_merchant\">    <a class=\"close-reveal-modal\">×</a>    <div id=\"compose_email\">        <h1>New conversation with Merchant from Shopinterest</h1>        <h2>Enter the subject and content to send an email.</h2>        <div class=\"alert-box success\">Email Sent!</div>        <div class=\"alert-box alert\">{{error_msg}}</div>        <input type=\"hidden\" class=\"contact_merchant_toemail\">        <input type=\"hidden\" class=\"contact_merchant_toname\">        <input type=\"text\" class=\"contact_merchant_replyto\" placeholder=\"Your Email\">        <input type=\"text\" class=\"contact_merchant_subject\" placeholder=\"Subject\">        <textarea class=\"contact_merchant_content\" placeholder=\"Message\" style=\"height: 170px;\"></textarea>        <input type=\"submit\" class=\"button radius alert medium contact_merchant_submit\" value=\"Send\">    </div></div>';

    
    shopinterest.modules.pinstore_button = function(pinstore_lightbox) {

        var module_name = 'pinstore_button';
        var utils = shopinterest.common.utils;
        var substitute = utils.substitute;
        var id = utils.getModuleId(module_name);
        var container = null;
        var _this = this;

        _this.render = function(tgt, button_image, button_text) {
            var template = shopinterest.templates.pinstore_button;
            var html = substitute(template, {id: id, button_image: button_image, button_text: button_text}); 
            tgt.html(html);
            container = $('#'+id);
            bindUI();
        };

        var bindUI = function() {
            container.click(function(e) {
                e.preventDefault();
                pinstore_lightbox.show($('.tgt_pinstore_lightbox'));
            });
        };


    };

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.pinstore_button='<a href=\"\" id=\"{{id}}\">    {{#button_image}}    <img src=\"http://passets-lt.pinterest.com/images/about/buttons/pinterest-button.png\" width=\"80\" height=\"28\" alt=\"Pin the store products\" />    {{/button_image}}    {{#button_text}}    Pin Your Store    {{/button_text}}</a>';


    
    shopinterest.modules.pinstore_lightbox = function() {

        var module_name = 'pinstore_store';
        var utils = shopinterest.common.utils;
        var substitute = utils.substitute;
        var id = utils.getModuleId(module_name);
        var container = null;
        var _this = this;
        var current_page = '';
        var pinterest_email = '';
        var pinterest_password = '';
        var plogin_url = shopinterest.constants.base_service_url+'/plogin';
        var uploadpins_url = shopinterest.constants.base_service_url+'/uploadpins';
        var pinterest_account = null;
        var pinterest_boards = null;
        var tgt = null;
        var default_boardname = 'My Shopinterest Store';

        _this.render = function(tgt_in, data_in) {
            tgt = tgt_in;
            tgt.html('');
            var template = shopinterest.templates.pinstore_lightbox;
            current_page = data_in.current_page;
            data_in.id = id;
            data_in[current_page] = true;
            var html = substitute(template, data_in); 
            tgt.html(html);
            container = $('#'+id);
            _this.render_form(container, data_in);
        };

        _this.render_form = function(container, data_in) {
            container.html(substitute(shopinterest.templates.pinstore_lightbox_form, data_in));
            bindUI();
        }

        _this.show = function(tgt_in) {
            tgt = tgt_in;
            var data_in = {current_page: 'show_login'};
            data_in.id = id;
            _this.render(tgt, data_in);
            container.reveal();
        }

        var bindUI = function() {

            container.unbind('click');
            $('.close-reveal-modal').click(function(e) {
                container.trigger('reveal:close');
            });
            var data = {};
            if(current_page === 'show_login') {
                container.on('click', '#pinstore_lightbox_submit', function(e) {
                    pinterest_email = $('#pinterest_email').val();
                    pinterest_password = $('#pinterest_password').val();
                    $.post(plogin_url, $.query.set('pinterest_email', pinterest_email).set('pinterest_password', pinterest_password).toString().replace('?', ''), function(response) {

                        var response_obj = $.parseJSON(response);
                        if(response_obj.status === false) {
                            // show error msg
                            data[current_page] = true;
                            data['error_msg'] = response_obj.data.error_msg;
                            _this.render_form(container, data);
                        } else {
                            pinterest_account = response_obj.data.account;
                            pinterest_boards = response_obj.data.boards;
                            // show the create board form
                            current_page = 'show_createboard';
                            data[current_page] = true;
                            data['default_boardname'] = default_boardname;
                            _this.render_form(container, data);
                        }
                    });




                });
            } else if(current_page === 'show_createboard') {
                container.on('click', '#pinstore_lightbox_submit', function(e) {
                    //container.trigger('reveal:close');
                    $.post(uploadpins_url, {pinterest_boardname: $('#boardname').val()}, function(response) {

                        var response_obj = $.parseJSON(response);
                        if(response_obj.status === false) {
                            // show error msg
                            data[current_page] = true;
                            data['error_msg'] = 'Error on creating a new board';
                            _this.render_form(container, data);
                        } else {
                            //container.trigger('reveal:close');
                            // show the confirmation
                            current_page = 'show_confirmation';
                            data[current_page] = true;
                            data['button_text'] = 'Close';
                            _this.render_form(container, data);
                        }
                    });
                });
                $('#select_boards').click(function(e) {
                    e.preventDefault();
                    var data = {};
                    current_page = 'show_selectboards';
                    data[current_page] = true;
                    data['boards'] = pinterest_boards;
                    _this.render_form(container, data);
                });        
            } else if(current_page === 'show_selectboards') {
                container.on('click', '#pinstore_lightbox_submit', function(e) {
                    //container.trigger('reveal:close');
                    $.post(uploadpins_url, {pinterest_board_id: $('#board_options').val()}, function(response) {
                        //console.log(response);
                        var response_obj = $.parseJSON(response);
                        if(response_obj.status === false) {
                            // show error msg
                            data[current_page] = true;
                            data['error_msg'] = 'Upload products to Pinterest error, try again...';
                            _this.render_form(container, data);
                        } else {
                            //container.trigger('reveal:close');
                            // show the confirmation
                            current_page = 'show_confirmation';
                            data[current_page] = true;
                            data['button_text'] = 'Close';
                            _this.render_form(container, data);
                        }

                    });


                });
                $('#create_board').click(function(e) {
                    e.preventDefault();
                    var data = {};
                    current_page = 'show_createboard';
                    data[current_page] = true;
                    data['default_boardname'] = default_boardname;
                    _this.render_form(container, data);
                });
            } else if(current_page === 'show_confirmation') {
                container.on('click', '#pinstore_lightbox_submit', function(e) {
                    container.trigger('reveal:close');
                });


            }    
        };


    };

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.pinstore_lightbox='<div id=\"{{id}}\" class=\"reveal-modal pin-modal\"></div>';
    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.pinstore_lightbox_form='<a class=\"close-reveal-modal\">×</a>{{#error_msg}}    <div class=\"alert-box alert\">{{error_msg}}</div>{{/error_msg}}{{#show_login}}	<h1>Pin All <span class=\"gentium\">Your</span> Products</h1>	<h2>Enter your Pinterest account information below and we will pin all your current products to your Pinterest account for you.</h2>	<p>We do not store your information and will never post without your permission.</p>    <input id=\"pinterest_email\" type=\"text\" placeholder=\"Pinterest Email\">    <input id=\"pinterest_password\" type=\"password\" placeholder=\"Pinterest Password\">{{/show_login}}{{#show_createboard}}	<h1>Name <span class=\"gentium\">your</span> Pinterest board</h2>	<p>Create a new board to pin all your products into.</p>    <input id=\"boardname\" type=\"text\" placeholder=\"{{default_boardname}}\" value=\"{{default_boardname}}\" >        <div><a href=\"\" id=\"select_boards\">Would you like to pin to an existing board? Click here »</a></div>    {{/show_createboard}}{{#show_selectboards}}	<h1>Select a <span class=\"gentium\">Pinterest</span> board</h2>	<p>Select a Pinterest board to pin all your products to.</p>    <select id=\"board_options\">        {{#boards}}            <option value=\"{{id}}\">{{name}}</option>        {{/boards}}    </select>    <div><a href=\"\" id=\"create_board\">Prefer to create a new board? Click Here »</a></div>{{/show_selectboards}}{{#show_confirmation}}    <h1>We are <span class=\"gentium\">pinning</span> your products now.</h1>    <p>This process will take about 15 minutes so please check your Pinterest profile soon.</p>{{/show_confirmation}}<center><br><br><input id=\"pinstore_lightbox_submit\" type=\"submit\" value=\"{{#button_text}}{{button_text}}{{/button_text}}{{^button_text}}Submit{{/button_text}}\" class=\"button radius alert large\"></center>';

    
    shopinterest.modules.etsy_import_lightbox = function() {

        var module_name = 'etsy_import_lightbox';
        var container = null;
        var _this = this;

        _this.render = function() {
            var template = shopinterest.templates.etsy_import_lightbox;
            var html = template;
            $('body').append(html);
            container = $('#'+module_name);         
            bindUI();        
        };

        _this.show = function() {
            container.show();
        };

        var bindUI = function() {
            container.bind('close', function(){
                container.hide();
            });
        };
    };

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.etsy_import_lightbox='<div id=\"etsy_import_lightbox\" style=\"display: none\">    <div id=\"etsy_import_shade\" style=\"position: fixed; top: 0px; bottom: 0px; right: 0px; left: 0px; background-color: rgb(0, 0, 0); opacity: 0.5; z-index: 99;\"></div>    <iframe id=\"etsy_import_iframe\" src=\"/iframe/etsyimport\" style=\"border:0;width: 844px;height: 600px;position: fixed;top: 10px;left: 50%;margin-left: -422px;z-index: 100;\"></iframe></div>';

    
    shopinterest.modules.pinpicker_lightbox = function() {
        var module_name = 'pinpicker-lightbox';
        var utils = shopinterest.common.utils;
        var container = null;
        var _this = this;

        _this.render = function() {
            var template = shopinterest.templates.pinpicker_lightbox;
            var html = template; 
            $('body').append(html);
            container = $('#'+ module_name);      
            bindUI();        
        };

        _this.show = function() {
            container.show();
        };

        var bindUI = function() {
            container.bind('pinpicker:close', function(e) {
                container.hide();
            });

            var spinner = new shopinterest.modules.spinner();
            spinner.render($('body'));
            container.bind('pinpicker_upload:start', function(e) {
                //console.log('pinpicker_upload:start --- base.js');
                // start to show spinner
                spinner.show();                      
            });
            container.bind('pinpicker_upload:finish', function(e) {
                //console.log('pinpicker_upload:finish --- base.js');
                spinner.close();
                location.href = "/merchant/products?status=inactive";                
            });               
        };

    };

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.pinpicker_lightbox='<div id=\"pinpicker-lightbox\" style=\"display: none\"><div id=\"pinpicker-shade\" style=\"position: fixed; top: 0px; bottom: 0px; right: 0px; left: 0px; background-color: rgb(0, 0, 0); opacity: 0.5; z-index: 99;\"></div><iframe id=\"pinpicker-iframe\" src=\"/iframe/pinpicker\" style=\"border:0;width: 845px;height: 544px;position: fixed;top: 10px;left: 50%;margin-left: -422px;z-index: 100;\"></iframe></div>';

    
    shopinterest.modules.pinpicker_selectboards = function(pinpicker_uploader_in) {

        var module_name = 'picker_files';
        var utils = shopinterest.common.utils;
        var substitute = utils.substitute;
        var container = $('#'+module_name);
        var _this = this;
        var pinpicker_uploader = pinpicker_uploader_in;

        var get_boards = function(pinterest_username, callback) {
            $.post('/api/getboards', {pinterest_username: pinterest_username}, function(response) {
                callback($.parseJSON(response));
            });
        }

        _this.render = function(pinterest_username) {
            var template = shopinterest.templates.pinpicker_selectboards;
            // get pins and render them
            get_boards(pinterest_username, function(board_info) {
                var html = substitute(template, board_info);
                container.html(html);
                bindUI();
            });

        };



        var bindUI = function() {
            container.find('li a').click(function(e) {
                e.preventDefault();
                var that = $(this);
                var board_id = that.attr('board-id');
                $('.picker_files_header').hide();
                $('#picker_files').hide();

                // show the pinpicker_selectpins
                var pinpicker_selectpins = new shopinterest.modules.pinpicker_selectpins(pinpicker_uploader);
                pinpicker_selectpins.render(board_id);
                $('#pinlist').show();
                $('#pinlist-spacer').show();
            });



        };


    };

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.pinpicker_selectboards='{{#boards}}<li unselectable=\"on\">    <a name=\"{{name}}\" board-id=\"{{id}}\" board-url=\"{{url}}\" href=\"#/Facebook/tagged/\" path=\"/Facebook/tagged/\" data-disabled=\"\" class=\"directory\">        <div class=\"board-snapshot\">            <span class=\"mainimg\"><img src=\"{{thumbnails.0}}\" width=\"54\" height=\"36.5\"></span>            <ul class=\"extraimg\">                <li><img src=\"{{thumbnails.1}}\" width=\"11.25\" height=\"11.25\"></li>                <li><img src=\"{{thumbnails.2}}\" width=\"11.25\" height=\"11.25\"></li>                <li><img src=\"{{thumbnails.3}}\" width=\"11.25\" height=\"11.25\"></li>                <li><img src=\"{{thumbnails.4}}\" width=\"11.25\" height=\"11.25\"></li>            </ul>        </div>        <div class=\"other-info\"><span class=\"board_name\">{{name}}</span><i class=\"icon-chevron-right icon-white floatright arrow-right\"></i></div>    </a></li>{{/boards}}    ';

    
    shopinterest.modules.pinpicker_selectpins = function(pinpicker_uploader_in) {

        var module_name = 'pinpicker_selectpins';
        var utils = shopinterest.common.utils;
        var substitute = utils.substitute;
        var container = $('#'+module_name);
        var _this = this;
        var board_id = 0;
        var next_page_url = '';
        var template = shopinterest.templates.pinpicker_selectpins;
        var in_process = false;
        var pinpicker_uploader = pinpicker_uploader_in;
        var page_num = 0;

        _this.render = function(board_id_in) {
            board_id = board_id_in;

            // get pins and render them
            get_pins(function(pin_info) {
                var html = substitute(template, pin_info);
                container.html(html);
                bindUI();
            });

        };

        var get_pins = function(callback) {
            $.post('/api/getpins', {board_id: board_id, next_page_url: next_page_url}, function(response) {
                var response_obj = $.parseJSON(response);
                next_page_url = $.trim(response_obj.next_page_url);
                in_process = false;
                page_num++;
                response_obj.page_num = page_num;
                callback(response_obj);
            });
        }

        container.scroll(function(e) {
            var position = parseInt(container.scrollTop());
            if(position > 900) {
                if(next_page_url !== '' && !in_process) {
                    in_process = true;
                    get_pins(function(pin_info) {
                        var html = substitute(template, pin_info);
                        container.append(html);
                        bindUI();
                    });
                }
            }
        });

        var bindUI = function() {

            // hover to a pin
            $(substitute('li[page-num="{{page_num}}"] .pin-container', {page_num: page_num})).hover(function(e) {
                var _this = $(this);
                var overlay_add_pin = _this.find('.overlay-image.add-pin');
                var overlay_pin_added = _this.find('.overlay-image.pin-added');
                var overlay_remove_pin = _this.find('.overlay-image.remove-pin');

                if(!_this.hasClass('selected')) {
                    overlay_add_pin.show();
                } else {
                    overlay_pin_added.hide();
                    overlay_remove_pin.show();
                }


                }, 
                function(e) {

                var _this = $(this);
                var overlay_add_pin = _this.find('.overlay-image.add-pin');
                var overlay_pin_added = _this.find('.overlay-image.pin-added');
                var overlay_remove_pin = _this.find('.overlay-image.remove-pin');

                if(!_this.hasClass('selected')) {
                    overlay_add_pin.hide();
                } else {
                    overlay_remove_pin.hide();
                    overlay_pin_added.show();
                }
            });

            // click a pin
            $(substitute('li[page-num="{{page_num}}"] .pin-container', {page_num: page_num})).click(function() {
                var _this = $(this);
                var overlay_add_pin = _this.find('.overlay-image.add-pin');
                var overlay_pin_added = _this.find('.overlay-image.pin-added');
                var overlay_remove_pin = _this.find('.overlay-image.remove-pin');
                var pin_url = _this.find('.pin45_url').attr('src');
                var pin_description = _this.siblings('h5').html();
                var pin_id = _this.find('.pin45_url').attr('pin-id');

                if(!_this.hasClass('selected')) {
                    overlay_add_pin.hide();
                    overlay_pin_added.show();
                    _this.addClass('selected');
                    // add a photo
                    pinpicker_uploader.add_pin(pin_id, pin_url, pin_description);


                } else {
                    overlay_pin_added.hide();
                    overlay_remove_pin.hide();
                    _this.removeClass('selected');
                    // remove a photo
                    pinpicker_uploader.remove_pin(pin_id);
                }

            });
        }
    };

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.pinpicker_selectpins='{{#pins}}<li unselectable=\"on\" page-num=\"{{page_num}}\">    <a name=\"facebook_photo.jpg\" class=\"thumbnail selectable pin-container\" path=\"/Facebook/2239476041764/2408888636973\" data-disabled=\"\">        <span class=\"overlay-image add-pin\" style=\"display: none\">            <img src=\"/img/plus.png\">        </span>        <span class=\"overlay-image pin-added\" style=\"display: none\">            <img src=\"/img/check.png\">        </span>        <span class=\"overlay-image remove-pin\" style=\"display: none\">            <img src=\"/img/x.png\">        </span>        <img class=\"pin45_url\" src=\"{{image_45}}\" style=\"display: none\" pin-id=\"{{id}}\">        <img class=\"pin192_url\" src=\"{{image_192}}\" alt=\"facebook_photo.jpg\" width=\"130\">    </a>    <h5 style=\"display: none\">{{description}}</h5></li>{{/pins}}';

    
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
        }

        _this.remove_pin = function(pin_id) {
            var selector = substitute('li[pin-id={{pin_id}}]', {pin_id: pin_id});
            console.log(selector);
            added_pins.find(selector).remove();
        }

        var bindUI = function() {
            upload_button.click(function(e) {

                container.trigger('pinpicker_upload:start');

                var selected_pins = added_pins.find('li');
                var products = [];

                selected_pins.each(function(index, pin) {
                    var pin = $(pin);
                    var pin_url_45 = pin.find('.thumbnail-image').attr('src');
                    var pin_description = pin.find('.multi-filename').html();
                    var pin_urls = utils.get_pinterest_image_urls(pin_url_45);     

                    var product = {};
                    product.pictures = [];
                    var picture = {};  
                    picture.converted_pictures = [];
                    var converted_pictures = [];

                    var converted_picture0 = {};
                    converted_picture0.type = 45;
                    converted_picture0.url = pin_urls[0];
                    converted_pictures.push(converted_picture0);

                    var converted_picture1 = {};
                    converted_picture1.type = 70;
                    converted_picture1.url = pin_urls[1];
                    converted_pictures.push(converted_picture1);                

                    var converted_picture2 = {};
                    converted_picture2.type = 192;
                    converted_picture2.url = pin_urls[2];
                    converted_pictures.push(converted_picture2);   

                    var converted_picture3 = {};
                    converted_picture3.type = 236;
                    converted_picture3.url = pin_urls[3];
                    converted_pictures.push(converted_picture3);   

                    var converted_picture4 = {};
                    converted_picture4.type = 550;
                    converted_picture4.url = pin_urls[4];
                    converted_pictures.push(converted_picture4);   

                    var converted_picture5 = {};
                    converted_picture5.type = 736;
                    converted_picture5.url = pin_urls[5];
                    converted_pictures.push(converted_picture5);       

                    picture.converted_pictures = converted_pictures;
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

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.pinpicker_uploader='<div id=\"{{id}}\">    <h3 id=\"helpMessage\">Selected Pins</h3>    <p id=\"subMessage\">Click to select multiple pins.</p>    <ul class=\"multi-file-preview added_pins\">    </ul>    <div id=\"action-group\" class=\"span4\">        <a class=\"btn btn-large btn-primary btn-upload disabled\">Upload</a>    </div></div>';

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.pinpicker_uploader_listitem='<li class=\"selected-preview alert alert-info\" pin-id=\"{{pin_id}}\">    <div class=\"tiny-thumbnail\">        <img class=\"thumbnail-spinner\" src=\"/static/img/spinner.gif\" alt=\"Loading...\" style=\"display: none;\">        <img class=\"thumbnail-image\" src=\"{{pin_url}}\" alt=\" thumbnail\" style=\"\">    </div>    <span class=\"multi-filename ellipsize\">{{pin_description}}</span></li>';
    
    
    shopinterest.modules.csv_import_lightbox = function(){

        var module_name = 'csv_import_lightbox';
        var container = null;
        var _this = this;

        _this.render = function() {
            var template = shopinterest.templates.csv_import_lightbox;
            var html = template;
            $('body').append(html);
            container = $('#'+module_name);
            bindUI();        
        };

        _this.show = function() {
            container.show();
        };

        var bindUI = function() {
            container.bind('close', function(){
                //console.log('close trigger by controller');
                container.hide();
            });
        };    
    };
    
    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.csv_import_lightbox='<div id=\"csv_import_lightbox\" style=\"display: none\">    <div id=\"etsy_import_shade\" style=\"position: fixed; top: 0px; bottom: 0px; right: 0px; left: 0px; background-color: rgb(0, 0, 0); opacity: 0.5; z-index: 99;\"></div>    <iframe id=\"csv_import_iframe\" src=\"/iframe/csvimport\" style=\"border:0;width: 844px;height: 600px;position: fixed;top: 10px;left: 50%;margin-left: -422px;z-index: 100;\"></iframe></div>';
    
    
    shopinterest.modules.spinner = function(){

        var module_name = 'spinner';
        var utils = shopinterest.common.utils;
        var substitute = utils.substitute;
        var id = utils.getModuleId(module_name);
        var show_message = utils.show_message;    
        var container = null;
        var _this = this;

        _this.render = function(tgt) {
            container = $('#'+id);
            var template = shopinterest.templates.spinner;
            var html = substitute(template, {id: id});
            tgt.append(html);
        };

        _this.show = function() {
            $('#'+id).css({'visibility':'', 'display' : 'block'});
        };

        _this.close = function() {
            $('#'+id).css({'visibility':'hidden', 'display' : 'none'});
        };    
    };

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.spinner='<div id=\"{{id}}\" style=\"position: fixed; top: 0px; bottom: 0px; right: 0px; left: 0px; background-color: rgb(0, 0, 0); opacity: 0.6; z-index: 99999;visibility: hidden;display: none;\">    <div id=\"loading\" style=\"text-align: center;margin-top: 100px;\">        <img src=\"/img/waiting.gif\" alt=\"\" style=\"margin-left: -25px;\">    </div></div>';

    
    shopinterest.modules.create_products_lightbox = function() {

        var module_name = 'create_product_lightbox';
        var utils = shopinterest.common.utils;
        var get_upload_dst = utils.get_product_image_upload_dst2;
        var uniqid = utils.uniqid;
        var _this = this;
        var container = null;
        var binded = false;
        var _store_id = 0;
        var convert = utils.convert;

        _this.render = function(store_id) {
            var template = shopinterest.templates.create_products_lightbox;
            var html = template; 
            $('body').append(html);
            container = $('#'+ module_name);    
            _store_id = store_id;
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
                        var spinner = new shopinterest.modules.spinner();
                        spinner.render($('body'));
                        spinner.show();      

                        var products = [];                 

                        $.each(inkBlobs ,function(index, inkBlob) {
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

                            picture.converted_pictures = converted_pictures;    
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
                                location.href = "/merchant/products?status=inactive";
                            }
                        });   

                    }); 

                };

                // etsy import work flow
                if(e.from === 'etsy_import') {
                    filepicker.pick({
                            extension: '.csv',
                            services:['COMPUTER', 'GOOGLE_DRIVE', 'DROPBOX', 'BOX', 'SKYDRIVE', 'URL','FTP', 'GMAIL']
                        },
                        function(InkBlob) {
                            var spinner = new shopinterest.modules.spinner();
                            spinner.render($('body'));
                            spinner.show();
                            $.post('/api/importproductsfromcsv', {csv_file_url: InkBlob.url}, function(response) {
                                var response_obj = JSON.parse(response);   
                                if(response_obj.status === 'success') {
                                    spinner.close();
                                    alert("Please check 'View products' after your email notification is received.");
                                } else {
                                    alter("We're sorry, something seems to be wrong on our end, please try it later");
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

    /* DONT EDIT. THIS IS A AUTO-GENERATED FILE. PLEASE EDIT FILES under quickstart/application/views/mustache INSTEAD*/
    shopinterest.templates.create_products_lightbox='<div id=\"create_product_lightbox\" style=\"display: none;\">    <div id=\"create_product_shade\" style=\"position: fixed; top: 0px; bottom: 0px; right: 0px; left: 0px; background-color: rgb(0, 0, 0); opacity: 0.5; z-index: 99;\"></div>    <iframe id=\"create_product_iframe\" src=\"/iframe/createproducts\" style=\"position: fixed;border:0;width: 345px;height: 375px;top: 10px;left: 50%;margin-left: -172px;z-index: 100;\"></iframe></div>';

    
    
    
    shopinterest.common.utils = {
        get_controller_action: function() {
            var host = window.location.host;
            var subdomains = host.split('.');

            var subdomain_type = 'merchant';
            if(subdomains[0] !== 'shopinterest' && subdomains[0] !== 'home' && subdomains[0] !== 'web01' && subdomains[0] !== 'www' && subdomains[0].indexOf('localhost') != 0 ) {
                subdomain_type = 'store';
            }

            var path = window.location.pathname;
            var parts = path.split('/');
            var controller = 'index', action = 'index';
            if(parts[1]) controller = parts[1];
            if(parts[2]) action = parts[2];
            if(subdomain_type === 'store' && controller == 'index' ) {
                controller = 'store';
                action = 'index';
            }
            return [controller, action];
        },
        getPageControllerName: function() {
            var controller_action = this.get_controller_action();
            var controller = controller_action[0];
            var action = controller_action[1];
            var pageControllerName = 'controllers-'+controller+'_'+action;
            return pageControllerName;
        },
        getPageControllerObjectName: function() {
            var path = window.location.pathname;
            var parts = path.split('/');
            var controller = 'index', action = 'index';
            if(parts[1]) controller = parts[1];
            if(parts[2]) action = parts[2];
            var pageControllerObjectName = controller+'_'+action;
            return pageControllerObjectName;
        },
        getSubdomainType: function() {
            return $('#subdomain_type').val();
        },
        isUser: function() {
            return !($('#user_id').val() === "0");
        },
        isAssociate: function() {
            return !($('#associate_id').val() === "0");
        },
        isMerchant: function() {
            return !($('#merchant_id').val() === "0");
        },
        isAnonymous: function() {
            return $('#user_id').val() === "0";
        },
        getUserId: function() {
            return parseInt($('#user_id').val());
        },
        getMerchantId: function() {
            return parseInt($('#merchant_id').val());
        },
        getAssociateId: function() {
            return parseInt($('#associate_id').val());
        },
        isStoreLaunched: function() {
            return $('#store_status').val() === "2";
        },
        isActiveAssociate: function() { 
            return $('#associate_status').val() === "2";
        },    
        getPathFromURL: function(url) {

        },
        substitute: function(template, dataObj, partials) {
            return Mustache.to_html(template, dataObj, partials);
        },
        setCookie: function(name,value,days) {
            if (days) {
                var date = new Date();
                date.setTime(date.getTime()+(days*24*60*60*1000));
                var expires = "; expires="+date.toGMTString();
            }
            else var expires = "";
            document.cookie = name+"="+value+expires+"; path=/";
        },
        getCookie: function (name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        },
        deleteCookie: function (name) {
            setCookie(name,"",-1);
        },
        getModuleId: function(module_name) {
            return module_name+'_'+(new Date()).getTime()+String(Math.floor((Math.random()*1000)+1));
        },
        bindInfiniteScrolling: function(button) {
            $(window).scroll(function(){
                if($(document).height() - $(document).scrollTop() - $(window).height() < 500){
                    button.click();
                }
            });
        },
        init_commission_validate: function() {
            function is_digit(value){
                return /^[+]?[0-9]+$/.test(value);
            }
            function commission_validate(product_commission, product_price) {
                if(isNaN(product_commission) || isNaN(product_price) || !is_digit(product_commission) || product_commission*product_price/100 < 1 || product_commission > 60 )   return false;  
                return true;
            }
            var commission_field = $('.text_commission');
            if(commission_field.length !== 0) {
                commission_field.unbind('blur');       
                commission_field.blur(function(e) {  
                    elem_obj = $(e.currentTarget);             
                    var product_commission = elem_obj.val();            
                    var product_price = elem_obj.closest('.pinitem').find('.text_price').val();
                    var errorbox = elem_obj.closest('.pinitem').find('.commisson_error');
                    if(errorbox) {
                        errorbox.remove();
                    }
                    if(!commission_validate(product_commission, product_price)) {
                        elem_obj.val(0);
                        elem_obj.focus(function(){
                            elem_obj.css("border", "2px solid #800 !important");
                        });
                        elem_obj.after('<span class="commisson_error" style="color: red;">Invalid product commission.</span>');
                    }
                });
            }
        },
        init_shipping_widget: function() {
            function generate_product_shipping_fee(input_elem, store_shipping, store_additional_shipping) {
                var extra_shipping = isNaN(parseFloat(input_elem.val()))?0:parseFloat(input_elem.val());
                var product_total_shipping = store_shipping + extra_shipping;
                var product_additional_shipping = store_additional_shipping + extra_shipping;
                input_elem.parent().siblings('.calc').find('.product_total_shipping').html('$'+product_total_shipping);
                input_elem.parent().siblings('.calc').find('.product_additional_shipping').html('$'+product_additional_shipping);
            }
            var store_shipping_input = $('#store_shipping');
            var store_additional_shipping_input = $('#store_additional_shipping');
            var product_shipping_input = $('.text_shipping');

            if(store_shipping_input.length !==0 && store_additional_shipping_input !== 0 &&
                product_shipping_input.length !== 0) {
                var store_shipping = parseFloat(store_shipping_input.val());
                var store_additional_shipping = parseFloat(store_additional_shipping_input.val());
                product_shipping_input.each(function(index) {
                    generate_product_shipping_fee($(this), store_shipping, store_additional_shipping);

                    // bind keyup event handler
                    $(this).unbind('keyup');
                    $(this).keyup(function(e) {
                        generate_product_shipping_fee($(this), store_shipping, store_additional_shipping);
                    });

                });
            }
        },
        appendMasonry: function(index, append_data, callback){
            if(!append_data[index]) return;
            var $newElems = $(append_data[index]);
            $newElems.imagesLoaded(function(){
                $('#masonholder').append($newElems).masonry(
                    'appended', $newElems, true 
                );
                // Don't know why have to call these
                $('#masonholder .cart_list01_content').on( "mouseover", function (e) {
                    $(e.currentTarget).find('.hiddenButtons').show();
                });
                $('#masonholder .cart_list01_content').on( "mouseout", function (e) {
                    $(e.currentTarget).find('.hiddenButtons').hide();
                });
                index++;
                $newElems.fadeIn("slow");
                if(index<append_data.length){
                    shopinterest.common.utils.appendMasonry(index, append_data, callback);
                } else {
                    if($.isFunction(callback)) {
                        callback();
                    } else if(callback) {
                        $.each(callback, function(index, call) {
                            if($.isFunction(call)) {
                                call();
                            }
                        });
                    }
                }
            });
        },
        init_masonry: function(callbacks) {
            /* activate mason to re-arrange product boxes */
            var append_data = $('#masonholder .cart_list01_content:gt(4)');
            append_data.remove();

            var origin_data = $('#masonholder .cart_list01_content');
            origin_data.imagesLoaded(function(){
                $('#masonholder').masonry({
                    // options
                    itemSelector : '#masonholder .cart_list01_content',
                    gutterWidth: 12,
                    isFitWidth: true
                });
                /* Fades in products after all content is loaded */
                $('#loading').hide();
                $('#masonholder .cart_list01_content').fadeIn("slow");
                $('#merchantinfo').fadeIn("slow");

                if(callbacks) {
                    $.each(callbacks, function(index, callback) {
                        if($.isFunction(callback)) {
                            callback();
                        }
                    });
                }

                shopinterest.common.utils.appendMasonry(0, append_data, callbacks); 

            });
        },
        show_pagination: function() {
            var pagination = $('#prevnextnav');
            if(pagination.css('display') === 'none') {
                $('#prevnextnav').show();
            }
        },
        toggle_code_section: function() {
            $('.get_code').click(function (e) {
                var get_code_button = $(this);
                var product_item_section = $($(this).closest('.product_item'));
                var code_section = $(product_item_section.children('.thecode'));

                code_section.toggle();
            });
        },
        change_password : function() {
            $('#changepswrd').click(function() {
                $('#myModal').reveal();
                var current_password_elem = $('#current_password');
                var new_password_elem = $('#new_password');
                var confirm_password_elem = $('#confirm_password');
                $('#save_password').unbind('click');
                $('#save_password').click(function(e) {
                    var current_password = $.trim(current_password_elem.val());
                    var new_password = $.trim(new_password_elem.val());
                    var confirm_password = $.trim(confirm_password_elem.val());

                    if(current_password === '' || new_password === '' || confirm_password === '') {
                        $('#save_password_error_box').show();
                    } else {
                        $.post('/api/updatepassword', {
                            current_password: current_password,
                            new_password: new_password,
                            confirm_password: confirm_password
                        }, function(response) {
                            if(response === 'success') {
                                $('#save_password_error_box').hide();
                                current_password_elem.val('');
                                new_password_elem.val('');
                                confirm_password_elem.val('');
                                $('.close-reveal-modal').trigger('click');
                                $('#profile').prepend('<div class="alert-box success row">Your password gets updated successfully.<a href="" class="close">×</a></div>');
                            } else {
                                $('#save_password_error_box').show();
                            }
                        });
                    }
                });

                $('.close-reveal-modal').click(function(e) {
                    $('#save_password_error_box').hide();
                    current_password_elem.val('');
                    new_password_elem.val('');
                    confirm_password_elem.val('');
                });

            });        
        },
        uniqid : function() {
            function S4() {
                return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
            }
            return (S4()+S4()+S4());
        },              
        get_product_image_upload_dst : function(store_id, uniqid, picture_id, image_type) {

            var get_product_image_name = function(uniqid, salt, image_type) {
                return (uniqid+'_'+salt+'_'+image_type+'.jpg');
            };
            var file_name = get_product_image_name(uniqid, picture_id, image_type);
            return ('/store/'+store_id+'/'+file_name);
        },
        get_product_image_upload_dst2 : function(store_id, uniqid, image_type) {

            var get_product_image_name = function(uniqid, image_type) {
                return (uniqid+'_'+image_type+'.jpg');
            };
            var file_name = get_product_image_name(uniqid, image_type);
            return '/stores/'+store_id+'/'+file_name;
        },
        get_pinterest_image_urls : function(url) {
            var pattern = /\d{2,3}x\d{0,2}/;
            var urls = [];
            urls.push(url.replace(pattern, '45x45'));
            urls.push(url.replace(pattern, '70x'));
            urls.push(url.replace(pattern, '192x'));
            urls.push(url.replace(pattern, '236x'));
            urls.push(url.replace(pattern, '550x'));
            urls.push(url.replace(pattern, '736x'));

            return urls;
        },
        show_message : function(type, tip_container, message) {

            if(message === '') {
                return;
            }

            tip_container.children().remove();
            tip_container.show();
            shopinterest.use('templates-green_tips', 'templates-red_tips', 'templates-spinner_tips', function() {
                var template = '';
                if(type === 'success') {
                    template = shopinterest.templates.green_tips;
                } else if(type === 'spinner'){
                    template = shopinterest.templates.spinner_tips;
                } else {
                    template = shopinterest.templates.red_tips;
                }
                var html = shopinterest.common.utils.substitute(template, {message: message});
                tip_container.append(html);
                setTimeout(function() {
                    tip_container.fadeOut("slow", function () {
                        tip_container.hide();
                    });
                }, 2000);
            }); 
        },
        validate : function(field, value) {

            function is_date(data){
                return /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(data);
            }	

            function is_digit(value){
                return /^[+]?[0-9]+$/.test(value);
            }

            function is_integer(value) {
                return  Math.floor(value) == value && $.isNumeric(value);
            }

            function commission_validate(product_commission, product_price) {
                if(isNaN(product_commission) || isNaN(product_price) || !is_digit(product_commission) 
                        || (product_commission!= 0 && product_commission*product_price/100 < 1) || product_commission > 60 )   return false;  
                return true;
            }

            if(field ===  'date') {
                if(is_date(value)) {
                    return false;
                }
                return true;		
            }	

            if(field === 'name' || field === 'description') {
                if($.trim(value) === "") {
                    return false;
                }
                return true;
            }

            if(field === 'quantity') {
                if(value < 0 || !is_integer(value)) {
                    return false;
                }
                return true;
            }    

            if(field === 'commission') {
                var commission = value.commission;
                var price = value.price;
                return commission_validate(commission, price);
            }

            if(field === 'price' || field === 'shipping') {
                if(!$.isNumeric(value)) {
                    return false;
                }
                return true;
            }

        },
        convert : function(url, options) {
            return (url+'/convert?'+$.param(options));
        }
    };

    shopinterest.templates.fbapi='<div id=\"fb-root\"></div>';
    
    shopinterest.libs.fbapi = new function() {

        var fbapi_template = shopinterest.templates.fbapi;
        var _this = this;

        $('body').prepend(fbapi_template);

        window.fbAsyncInit = function() {
            // init the FB JS SDK
            FB.init({
                appId      : shopinterest.constants.fb_app_id, // App ID from the App Dashboard
                channelUrl : shopinterest.constants.base_url+'/'+'channel.php', // Channel File for x-domain communication
                status     : true, // check the login status upon init?
                cookie     : true, // set sessions cookies to allow your server to access the session?
                xfbml      : true  // parse XFBML tags on this page?
            });

            // Additional initialization code such as adding Event Listeners goes here

            FB.getLoginStatus(function(response) {
                FB.getLoginStatus(function(response) {
                    if (response.status === 'connected') {
                        shopinterest.facebook.login_status = 'connected';
                        shopinterest.facebook.access_token = response.authResponse.accessToken;
                    } else if (response.status === 'not_authorized') {
                        shopinterest.facebook.login_status = 'not_authorized';
                    } else {
                        shopinterest.facebook.login_status = 'not_logged_in';
                    }
                });
            });

            FB.Event.subscribe('auth.login', function(response) {
                var access_token = response.authResponse.accessToken;
                window.location.href = shopinterest.constants.base_service_url+'/fbregister?access_token='+access_token;

            });
            FB.Event.subscribe('auth.logout', function(response) {
            });
        };

        // Load the SDK's source Asynchronously
        (function(d){
            var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement('script');
            js.id = id;
            js.async = true;
            js.src = 'http://connect.facebook.net/en_US/all.js';
            ref.parentNode.insertBefore(js, ref);
        }(document));

        // fb feed dialog
        _this.showFeedDialog = function(feed, success, failure) {

            FB.ui({
                method: 'feed',
                name: feed.name,
                caption: feed.caption,
                description: feed.description,
                link: feed.link,
                picture: feed.picture
            }, function(response) {
                    if (response && response.post_id) {
                        if(success) {
                            success();
                        }
                    } else {
                        if(failure) {
                            failure();
                        }
                    }
                }
            );


        };

        // fb send dialog
        _this.showSendDialog = function(msg) {
            FB.ui({
                method: 'send',
                name: msg.name,
                link: msg.link
            });
        };

        // fb connect dialog
        _this.showLoginDialog = function(success, failure) {
            FB.login(function(response) {
                if (response.authResponse) {
                    // connected
                    if(success) {
                        success();
                    }
                } else {
                    // cancelled
                    if(failure) {
                        failure();
                    }
                }
            });
        };
    };

    shopinterest.libs.twapi = new function() {


        // Load the SDK's source Asynchronously
        (function(d){
            var js, id = 'twitter-jssdk', ref = d.getElementsByTagName('script')[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement('script');
            js.id = id;
            js.async = true;
            js.src = 'http://platform.twitter.com/widgets.js';
            ref.parentNode.insertBefore(js, ref);
        }(document));


    };

    
    shopinterest.controllers.base = new function() {

        var pageControllerName = shopinterest.common.utils.getPageControllerName();
        var controller_action = shopinterest.common.utils.get_controller_action();
        var controller = controller_action[0];
        var action = controller_action[1];
        var subdomain_type = shopinterest.common.utils.getSubdomainType();
        var is_user = shopinterest.common.utils.isUser();
        var is_merchant = shopinterest.common.utils.isMerchant();
        var is_associate = shopinterest.common.utils.isAssociate();
        var is_anonymous = shopinterest.common.utils.isAnonymous();
        var user_id = shopinterest.common.utils.getUserId();
        var merchant_id = shopinterest.common.utils.getMerchantId();
        var associate_id = shopinterest.common.utils.getAssociateId();
        var is_store_launched = shopinterest.common.utils.isStoreLaunched();

        var router = function() {
            if(shopinterest.map[pageControllerName]) {

                shopinterest.use(pageControllerName, function(shopinterest) {

                });
            }
        };
        // route to the specific controller/action
        router();

        // event handlers for merchant type pages
        if(subdomain_type === 'merchant') {
            /* search box */ 
            $('.searchform').submit(function(e) {
                var q = $.trim($(this).find('#store_search').val());
                if(q.length !== 0) {
                    return true;
                } else {
                    return false;
                }
            });

            // event handlers for merchants
            if(is_user) {

                // event handlers for merchants who launched the store
                if(is_store_launched) {
                    /* pinstore button*/
                    shopinterest.use('modules-pinstore_button', 'templates-pinstore_button', 
                    'modules-pinstore_lightbox', 'templates-pinstore_lightbox', 'templates-pinstore_lightbox_form',
                    function(shopinterest) {

                        var pinstore_lightbox = new shopinterest.modules.pinstore_lightbox();
                        var pinstore_button = new shopinterest.modules.pinstore_button(pinstore_lightbox);
                        pinstore_button.render($('.tgt_pinstore_button_2'), false, true);

                    });
                }

                // pop up the add products lightbox
                shopinterest.use('modules-create_products_lightbox', function(shopinterest) {
                    var create_products_lightbox = new shopinterest.modules.create_products_lightbox();
                    var store_id = $('#my_store_id').val();
                    create_products_lightbox.render(store_id);            
                    $('.create_products').click(function(e) {
                        e.preventDefault();
                        create_products_lightbox.show();                
                    });
                });
            }
        }

        // event handlers for store type pages
        if(subdomain_type === 'store') {
            if(is_anonymous) {

                shopinterest.use('modules-signup_lightbox', 'templates-signup_lightbox', 'modules-fbconnect_button', 'templates-fbconnect_button', 
                function(shopinterest) {
                    /* signup lightbox */
                    $('.signup').click(function(e) {
                        e.preventDefault();

                        var signup_lightbox = new shopinterest.modules.signup_lightbox();
                        signup_lightbox.render($('.tgt_signup_lightbox'), shopinterest.constants.facebook_service_url+'/fbregister', 'name,email,password');
                    });

                    // facebook connect button
                    var fbconnect_button = new shopinterest.modules.fbconnect_button();
                    var success = function() {
                        if(shopinterest.facebook.login_status === 'connected') {
                            var access_token = shopinterest.facebook.access_token;
                            window.location.href = shopinterest.constants.facebook_service_url+'/fbregister?access_token='+access_token;
                        }
                    };
                    fbconnect_button.render($('.tgt_fbconnect_button'), success);
                });
            }

        }

        // event handlers for signup pages index/index, join-now/index, start-free/index
        if((controller === 'index' || controller === 'join-now') && (action === 'index')) {
            /* show signup form */
            $('.startfree').click(function(e) {
                $(e.currentTarget).hide();
                $('.signupform').slideToggle(500, function() {
                });
            });

            /* hide signup form */
            $('.cancel').click(function(e) {
                $('.signupform').slideToggle(0, function() {
                });
                $('.startfree').show();
            });
        }

        // event handlers for store subdomain type of pages and home page
        if(subdomain_type === 'store' || (controller === 'index' && action === 'index')) {
            /* facebook feed button*/
            shopinterest.use('modules-fbfeed_button', 'templates-fbfeed_button', function(shopinterest) {
                var fbfeed_button = new shopinterest.modules.fbfeed_button();
                fbfeed_button.render($('.tgt_fbfeed_button'), {
                    'name': 'Check out my new ShopInterest store! It\'s so Cool! ',
                    'caption': $('#my_store_name').val(),
                    'description': 'I just created an amazing store in minutes. I\'m sure you will find the perfect gift! Check it out, you\'ll love it.',
                    'link': $('#my_store_url').val(),
                    'picture': $('#my_store_logo').val(),
                    'button_icon': {
                        'img_src': 'http://w.sharethis.com/images/facebook_counter.png',
                        'width': 60,
                        'height': 22
                    }
                });
                /* ga tracking */
//                if(_gaq) {
//                    var categories = shopinterest.constants.categories;
//                    $('.tgt_fbfeed_button').click(function(e) {
//                        _gaq.push(['_trackEvent', categories.social, 'click', 'facebook feed button']);
//                    });
//                }
                /* end ga tracking */
            });

            /* facebook send button*/
            shopinterest.use('modules-fbsend_button', 'templates-fbsend_button', function(shopinterest) {
                var fbsend_button = new shopinterest.modules.fbsend_button();
                fbsend_button.render($('.tgt_fbsend_button'), {
                    'name': 'Check out my new ShopInterest store - Do you like it? Tell me what you think, OK? I\'m sure you will find the perfect gift! Check it out, you\'ll love it.',
                    'link': $('#my_store_url').val(),
                    'button_icon': {
                        'img_src': 'http://w.sharethis.com/images/email_counter.png',
                        'width': 60,
                        'height': 22
                    }
                });
                /* ga tracking */
//                if(_gaq) {
//                    var categories = shopinterest.constants.categories;
//                    $('.tgt_fbsend_button').click(function(e) {
//                        _gaq.push(['_trackEvent', categories.social, 'click', 'facebook send button']);
//                    });
//                }
                /* end ga tracking */
            });

            /* twitter tweet button*/
            shopinterest.use('modules-tweet_button', 'templates-tweet_button', function(shopinterest) {
                var tweet_button = new shopinterest.modules.tweet_button();
                tweet_button.render($('.tgt_tweet_button'), {
                    'url': $('#my_store_url').val(),
                    'via': 'shopinterest',
                    'text': 'I just created an amazing store in minutes. I\'m sure you will find the perfect gift here!',
                    'button_icon': {
                        'img_src': 'http://w.sharethis.com/images/twitter_counter.png',
                        'width': 60,
                        'height': 22
                    }
                });
                /* ga tracking */
//                if(_gaq) {
//                    var categories = shopinterest.constants.categories;
//                    $('.tgt_tweet_button').click(function(e) {
//                        _gaq.push(['_trackEvent', categories.social, 'click', 'twitter tweet button']);
//                    });
//                }
                /* end ga tracking */

            });
        }


    };

    

    
});




