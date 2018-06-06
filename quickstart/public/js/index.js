var env = $('body').attr('env');
if(env === '') env = 'staging';

var site_version = parseInt($('body').attr('siteversion'));

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
            loadingURL = '//'+window.location.host+'/combo/index?f='+path;
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
            'requires':  ['modules-popup_signup', 'modules-nav_cart']
        },
        'controllers-index_index': {
            'path': 'js/controllers/index_index.js',
            'loaded': 0
        },
        'controllers-category_index': {
            'path': 'js/controllers/category_index.js'
	},
        'controllers-pricing_index': {
            'path': 'js/controllers/pricing_index.js'
	},
        'controllers-how_index': {
            'path': 'js/controllers/how_index.js'
	},
        'controllers-how_topics': {
            'path': 'js/controllers/how_topics.js'
	},
        'controllers-ticket_index': {
            'path': 'js/controllers/ticket_index.js'
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
        'controllers-store_info':{
            'path': 'js/controllers/store_info.js',
            'loaded': 0
        },
        'controllers-preview_index':{
            'path': 'js/controllers/preview_index.js',
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
        'controllers-admin_stores':{
            'path': 'js/controllers/admin_stores.js',
            'loaded': 0
        },
        'controllers-admin_coupon':{
            'path': 'js/controllers/admin_coupon.js',
            'requires': ['modules-datatable']
        },
        'controllers-admin_email':{
            'path': 'js/controllers/admin_email.js',
            'requires': []
        },
        'controllers-admin_users':{
            'path': 'js/controllers/admin_users.js',
            'requires': ['modules-datatable']
        },
        'controllers-admin_amazon-products':{
            'path': 'js/controllers/admin_amazon_products.js',
            'requires': ['modules-datatable']
        },
        'controllers-admin_store-detail':{
            'path': 'js/controllers/admin_store_detail.js',
            'requires': []
        },
        'controllers-admin_email-templates':{
            'path': 'js/controllers/admin_email_templates.js',
            'requires': ['modules-datatable']
        },
        'controllers-admin_email-templates-edit':{
            'path': 'js/controllers/admin_email_templates_edit.js',
            'requires': ['modules-datatable']
        },
        'controllers-admin_orders':{
            'path': 'js/controllers/admin_orders.js',
            'requires': ['modules-datatable']
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
        'controllers-admin_auction':{
            'path': 'js/controllers/admin_auction.js',
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
        'controllers-admin_payment-detail':{
            'path': 'js/controllers/admin_payment_detail.js',
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
        'controllers-store_products-item':{
            'path': 'js/controllers/store_products-item.js',
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
            'requires' : ['modules-popup_tags', 'templates-expand_pic', 'templates-shipping', 'templates-product_custom_fields']
        },
        'controllers-merchant_shipping': {
            'path': 'js/controllers/merchant_shipping.js',
            'loaded': 0,
            'requires': ['templates-shipping_options']
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
	    'controllers-iframe_createresellproducts': {
            'path': 'js/controllers/iframe_createresellproducts.js'
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
        'controllers-merchant_analytics': {
            'path': 'js/controllers/merchant_analytics.js',
            'loaded': 0
        },
        'controllers-admin_categorizing': {
            'path': 'js/controllers/admin_categorizing.js',
            'loaded': 0
        },
        'controllers-cart_index': {
            'path': 'js/controllers/cart_index.js',
            'loaded': 0
        },
        'controllers-checkout_index': {
            'path': 'js/controllers/checkout_index.js',
            'loaded': 0
        },
        'controllers-checkout_confirm': {
            'path': 'js/controllers/checkout_confirm.js',
            'loaded': 0,
            'requires': ['modules-popup_receipt']
        },
        'controllers-auction_index': {
            'path': 'js/controllers/auction_index.js',
            'loaded': 0,
            'requires': ['modules-time_countdown']
        },
        'controllers-merchant_coupon': {
            'path': 'js/controllers/merchant_coupon.js'
        },
        'controllers-selling_tools-coupon': {
            'path': 'js/controllers/selling_tools_coupon.js',
            'requires': ['modules-datatable']
        },
        'controllers-selling_close-store': {
            'path': 'js/controllers/selling_close_store.js',
            'loaded': 0
        },
        'controllers-admin_index': {
            'path': 'js/controllers/admin_index.js',
            'requires' : ['modules-datatable']
        },
        'controllers-auction_item': {
            'path': 'js/controllers/auction_item.js',
            'loaded': 0,
            'requires': ['modules-time_countdown']
        },
        'controllers-me_settings': {
            'path': 'js/controllers/me_settings.js',
            'loaded': 0
        },
        'controllers-me_verify': {
            'path': 'js/controllers/me_verify.js'
        },
        'controllers-me_orders_resell-detail': {
            'path': 'js/controllers/me_orders_resell.js',
            'loaded': 0,
            'requires': ['ZeroClipboard.min']
        },
        'controllers-me_wallet-detail': {
            'path': 'js/controllers/me_wallet_detail.js',
            'requires': []
        },
        'controllers-dashboard_index': {
            'path': 'js/controllers/dashboard_index.js',
            'loaded': 0
        },
        'controllers-selling_settings': {
            'path': 'js/controllers/selling_settings.js',
            'loaded': 0
        },
        'controllers-selling_products': {
            'path': 'js/controllers/selling_products.js',
            'loaded': 0
        },
        'controllers-selling_preview': {
            'path': 'js/controllers/selling_preview.js',
            'loaded': 0
        },
        'controllers-selling_products-resell': {
            'path': 'js/controllers/selling_products_resell.js',
            'requires': ['modules-create_resell_products_lightbox']
        },
        'controllers-selling_tools-analytics': {
            'path': 'js/controllers/selling_tools_analytics.js'
        },
        'controllers-selling_products-shipping': {
            'path': 'js/controllers/selling_products_shipping.js',
            'loaded': 0,
            'requires': ['templates-sellingvenue_shipping_pattern']
        },
        'controllers-selling_orders-detail': {
            'path': 'js/controllers/selling_orders_detail.js',
            'loaded': 0
        },
        'controllers-selling_tools-pinstore': {
            'path': 'js/controllers/selling_tools_pinstore.js'
        },
        'controllers-selling_tools-customers': {
            'path': 'js/controllers/selling_tools_customers.js',
            'requires' : ['modules-datatable', 'modules-email_lightbox']
        },
        'controllers-masonry_index': {
            'path': 'js/controllers/masonry_index.js',
            'loaded': 0
        },

        'controllers-me_payment-accounts-paypal': {
            'path': 'js/controllers/me_payment-accounts-paypal.js',
            'loaded': 0
        },
        'controllers-me_payment-accounts-bank': {
            'path': 'js/controllers/me_payment-accounts-bank.js',
            'loaded': 0
        },
        'controllers-me_payment-accounts-creditcard': {
            'path': 'js/controllers/me_payment-accounts-creditcard.js',
            'loaded': 0
        },
        'controllers-selling_terms': {
            'path': 'js/controllers/selling_terms.js',
            'loaded': 0
        },
        'controllers-selling_products': {
            'path': 'js/controllers/selling_products.js',
            'loaded': 0,
            'requires': ['modules-create_products_lightbox']
        },
        'controllers-allstores_index': {
            'path': 'js/controllers/allstores_index.js',
            'loaded': 0
        },
        'controllers-selling_subscription': {
            'path': 'js/controllers/selling_subscription.js',
            'loaded': 0
        },
        'controllers-deals_index': {
            'path': 'js/controllers/deals_index.js',
            'loaded': 0
        },
        'controllers-shopbuilder_index': {
            'path': 'js/controllers/shopbuilder_index.js',
            'loaded': 0
        },

        // UI modules
        'modules-fbfeed_button': {
            'path': 'js/modules/fbfeed_button.js',
            'loaded': 0
        },
        'modules-datatable': {
            'path': 'js/modules/datatable.js',
            'loaded': 0,
            'requires': []
        },
//        'modules-datatable': {
//            'path': 'js/modules/datatable.js',
//            'requires' : ['templates-category_featured_products_row', 'templates-category_featured_products_create', 'templates-category_featured_products_update', 'templates-ad_featured_products_row', 'templates-ad_featured_products_create', 'templates-ad_featured_products_update', 'templates-slider_featured_products_row', 'templates-slider_featured_products_create', 'templates-slider_featured_products_update', 'templates-datatable', 'modules-search_box']
//        },
        'modules-search_box': {
            'path': 'js/modules/search_box.js',
            'requires' : ['templates-search_box']
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
            'requires': ['templates-email_lightbox']
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
            'requires': ['templates-pinpicker_uploader', 'templates-pinpicker_uploader_listitem']
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
            'requires': ['templates-create_products_lightbox', 'modules-csv_import_lightbox', 'modules-pinpicker_lightbox', 'modules-etsy_import_lightbox']
        },
	'modules-create_resell_products_lightbox': {
	    'path': 'js/modules/create_resell_products_lightbox.js',
            'requires': ['templates-create_resell_products_lightbox']
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
        // 'modules-spinner': {
        //     'path': 'js/modules/spinner.js',
        //     'loaded': 0,
        //     'requires': ['templates-spinner']
        // },
        'modules-popup_tags': {
            'path': 'js/modules/popup_tags.js',
            'loaded': 0,
            'requires': ['templates-popup_tags', 'templates-tag']
        },
        'modules-popup_signup': {
            'path': 'js/modules/popup_signup.js',
            'loaded': 0,
            'requires': ['templates-popup_signup', 'libs-fbapi']
        },
        'modules-popup_receipt': {
            'path': 'js/modules/popup_receipt.js',
            'loaded': 0,
            'requires': ['templates-popup_receipt']
        },
        'modules-time_countdown': {
            'path': 'js/modules/time_countdown.js',
            'loaded': 0,
            'requires': ['templates-time_countdown']
        },

        // templates
        'templates-global_category_dropdown': {
            'path': 'js/templates/global_category_dropdown.js'
        },
        'templates-category_creator': {
            'path': 'js/templates/category_creator.js',
            'loaded': 0
        },
        'modules-nav_cart': {
            'path': 'js/modules/nav_cart.js',
            'loaded': 0,
            'requires': ['templates-nav_cart']
        },
        'modules-add_to_cart': {
            'path': 'js/modules/add_to_cart.js',
            'loaded': 0,
            'requires': ['templates-add_to_cart']
        },
        // templates
        'templates-ad_featured_products_row' : {
            'path': 'js/templates/ad_featured_products_row.js'
        },
        'templates-ad_featured_products_update' : {
            'path': 'js/templates/ad_featured_products_update.js'
        },
        'templates-ad_featured_products_create' : {
            'path': 'js/templates/ad_featured_products_create.js'
        },
        'templates-category_featured_products_row' : {
            'path': 'js/templates/category_featured_products_row.js'
        },
        'templates-category_featured_products_update' : {
            'path': 'js/templates/category_featured_products_update.js'
        },
        'templates-category_featured_products_create' : {
            'path': 'js/templates/category_featured_products_create.js'
        },
        'templates-slider_featured_products_row' : {
            'path': 'js/templates/slider_featured_products_row.js'
        },
        'templates-slider_featured_products_update' : {
            'path': 'js/templates/slider_featured_products_update.js'
        },
        'templates-slider_featured_products_create' : {
            'path': 'js/templates/slider_featured_products_create.js'
        },
        'templates-search_box': {
            'path': 'js/templates/search_box.js'
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
        'templates-create_resell_products_lightbox': {
            'path': 'js/templates/create_resell_products_lightbox.js'
	},
        'templates-etsy_import_lightbox': {
            'path': 'js/templates/etsy_import_lightbox.js',
            'loaded': 0
        },
        'templates-csv_import_lightbox': {
            'path': 'js/templates/csv_import_lightbox.js',
            'loaded': 0
        },
        // 'templates-spinner': {
        //     'path': 'js/templates/spinner.js',
        //     'loaded': 0
        // },
        'templates-popup_tags': {
            'path': 'js/templates/popup_tags.js',
            'loaded': 0
        },
        'templates-tag': {
            'path': 'js/templates/tag.js',
            'loaded': 0
        },
        'templates-product_custom_fields': {
            'path': 'js/templates/product_custom_fields.js',
            'loaded': 0
        },
        'templates-popup_signup': {
            'path': 'js/templates/popup_signup.js',
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
        'templates-popup_receipt': {
            'path': 'js/templates/popup_receipt.js',
            'loaded': 0
        },
        'templates-time_countdown': {
            'path': 'js/templates/time_countdown.js',
            'loaded': 0
        },
        'templates-nav_cart': {
            'path': 'js/templates/nav_cart.js',
            'loaded': 0
        },
        'templates-add_to_cart': {
            'path': 'js/templates/add_to_cart.js',
            'loaded': 0
        },
        'templates-shipping': {
            'path': 'js/templates/shipping.js',
            'loaded': 0
        },
        'templates-shipping_options': {
            'path': 'js/templates/shipping_options.js',
            'loaded': 0
        },
        'templates-sellingvenue_shipping_pattern': {
            'path': 'js/templates/sellingvenue_shipping_pattern.js',
            'loaded': 0
        },
        'templates-sellingvenue_shipping_destination': {
            'path': 'js/templates/sellingvenue_shipping_destination.js',
            'loaded': 0
        },
        'templates-sellingvenue_products_resell': {
            'path': 'js/templates/sellingvenue_products_resell.js',
            'loaded': 0
        }
    },
    'combo': '//'+window.location.host+'/combo',
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
            var module_str = 'shopinterest.'+module.replace('-', "['")+"']";
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
            var module_str = 'shopinterest.'+required_modules[j].replace('-', "['")+"']";
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
            $.getScript(loadingURL)
                .done(function(data, status) {
                    callback(_this);
                })
                .fail(function(jqxhr, settings, exception){
                    throw "$.getScript Error: file = " + path + ", exception = " + exception.toString();
                });
        } else {
            callback(_this);
        }

    },
    constants: {
        base_url: '//'+window.location.host,
        base_service_url: '//'+window.location.host+'/api',
        'categories': {
            'signup': 'SIGNUP',
            'shopping': 'SHOPPING',
            'social': 'SOCIAL'
        },
        fb_app_id: window.location.host.indexOf('staging')>0 ? '573737459350983' : '212167575573301',
        's3_base_url': env === 'production'?'http://s3.amazonaws.com/shopinterest_production':'http://s3.amazonaws.com/shopinterest_stage'
    },
    facebook: {
        login_status: '',
        access_token: ''
    }

};

$(document).ready(function() {
    shopinterest.use('common-utils', 'templates-fbapi', 'libs-fbapi', 'libs-twapi', function(shopinterest) {
        shopinterest.use('controllers-base', function(shopinterest) {

        });
    });
});

