<?php

// entity status
define('IGNORE_STATUS',-1);
define('CREATED', 0);
define('PENDING', 1);
define('ACTIVATED', 2);
define('BLOCKED', 3);
define('PROCESSING', 4);
define('PROCESSED', 5);
define('COMPLETED', 6);
define('VERIFIED', 7);
define('PAID', 8);
define('SHIPPED', 9);
define('FAILED', 10);
define('USED', 11);
define('PARTIALLY_FAILED', 12);
define('MARK_TODEL', 13); // marked as to be delete

define('DELETED', 127);

// email verified
define('EMAIL_NOT_VERIFIED', 0);
define('EMAIL_VERIFIED', 1);


define('ORDER_TIMEOUT', 600);
define('PRODUCT_CREATE_TIMEOUT', 7200);

// error type
define('INFO', 1);
define('WARN', 2);
define('ERROR', 3);

// order by
define('ASC', 'ASC');
define('DESC', 'DESC');

//
define('DUMMY_PASSWORD', '******');

// job types
define('BOARD_SCRAPER_JOB', 1); // deprecated
define('BOARD_SCRAPER', 2); // deprecated
define('PINTEREST_IMAGE_UPLOADER', 3); // deprecated
define('EMAIL_SENDER', 4);
define('ACCOUNT_SCRAPER', 5);  // deprecated
define('ADD_FEATURED_PRODUCT', 6); // deprecated
define('PIN_STORE_PRODUCTS', 7);
define('PRODUCT_UPLOADER', 9);
define('PRODUCT_DELETE_BOARD', 10);
define('PRODUCT_DELETE_PIN', 11);
define('PRODUCT_CSV_IMPORTER', 12);
define('PRODUCT_SEARCH', 13); // deprecated
define('UPLOAD_PRODUCT_PICTURES', 14);
define('PICTURE_CONVERT', 15);
define('UPLOAD_CONVERTED_PICTURES', 16);
define('CONVERT_PICTURES', 17);
define('UPDATE_SEARCH_PRODUCTS', 18);
define('SYNC_GLOBAL_PRODUCTS', 19);
define('PICTURE_MIGRATION', 20);
define('DELETE_INACTIVE_STORE', 21);

//cookie path
define('COOKIE_PATH', '/tmp/cookies/');

//product description
define('PRODUCT_DESCRIPTION', 'welcome to our shop.' );

// email types
define('MERCHANT_REGISTER', 'merchant_register');
define('MERCHANT_STORE_DELETE_ALERT', 'merchant_store_delete_alert');
define('ASSOCIATE_REGISTER', 'associate_register');
define('USER_REGISTER', 'user_register');
define('USER_REGISTER_WITH_STORE', 'user_register_with_store');
define('SHOPPER_REGISTER', 'shopper_register');
define('MERCHANT_SALE_NOTIFICATION', 'merchant_sale_notification');
define('SHOPPER_FEEDBACK_REQUEST', 'shopper_feedback_request');
define('SHOPPER_PURCHASE_CONFIRMATION', 'shopper_purchase_confirmation');
define('SHOPPER_SHIPPING_NOTIFICATION', 'shopper_shipping_notification');
define('SHOPPER_ORDER_CANCEL_NOTIFICATION', 'shopper_order_cancel_notification');
define('MERCHANT_ORDER_CANCEL_NOTIFICATION', 'merchant_order_cancel_notification');
define('ADMIN_ORDER_CANCEL_NOTIFICATION', 'admin_order_cancel_notification');
define('MERCHANT_TICKET','merchant_ticket');
define('MERCHANT_PRODUCTS_IMPORTED', 'merchant_products_imported');
define('MERCHANT_PRODUCTS_PINNED', 'merchant_products_pinned');
define('NOTIFICATION', 'notification');
define('USER_FORGET_PASSWORD', 'user_forget_password');
define('ASSOCIATE_AFFILATE_CONFIRMATION', 'associate_affilate_confirmation');
define('MERCHANT_PRODUCT_SOLDOUT_NOTIFICATION', 'merchant_product_soldout_notification');
define('MERCHANT_SITE_ANALYTICS_WEEKLY_REPORT', 'merchant_site_analytics_weekly_report');
define('SHOPPER_AUCTION_OUTBID_NOTIFICATION', 'shopper_auction_outbid_notification');
define('SHOPPER_AUCTION_WINBID_NOTIFICATION', 'shopper_auction_winbid_notification');
define('SHOPPER_AUCTION_BID_RECEIVED_NOTIFICATION', 'shopper_auction_bid_received_notification');
define('MERCHANT_AUCTION_BID_RECEIVED_NOTIFICATION', 'merchant_auction_bid_received_notification');
define('MERCHANT_AUCTION_END_NOTIFICATION', 'merchant_auction_end_notification');
define('BIDER_AUCTION_END_NOTIFICATION', 'bider_auction_end_notification');
define('TECH_REPORT_RELEASE_NOTIFICATION', 'tech_report_release_notification');
define('NATIVE_CHECKOUT_RECEIPT', 'native_checkout_receipt');
define('NATIVE_CHECKOUT_MERCHANT_RECEIPT', 'native_checkout_merchant_receipt');
define('WALLET_WITHDRAW_REQUEST', 'wallet_withdraw_request');
define('ALERT_EMAIL', 'alert_email');

// time
define('TIME_0', '0000-00-00 00:00:00');
define('TIME_START', '2012-06-23 09:00:00');

// pagination constants
define('DEFAULT_PAGE_SIZE', 20);
define('PIN_NUM_PER_PAGE', 10);
define('PRODUCT_NUM_PER_PAGE', 30);
define('PAYMENT_ITEMS_PER_PAGE', 50);
define('STORE_NUM_PER_PAGE', 50);
define('ACCOUNT_NUM_PER_PAGE', 50);
define('SALESNETWORK_PRODUCT_NUM_PER_PAGE', 10);
define('CREATE_PRODUCT_NUM_PER_PAGE', 10);
define('SHIPPING_OPTIONS_NUM_PER_PAGE', 5);
define('DATATABLE_ITEMS_PER_PAGE', 10);
define('CATEGORY_NUM_PRE_LINE', 8);

// payment account types
define('PAYPAL', 0);
define('CREDIT_CARD', 1);

// credit card types
define('VISA', 0);
define('MASTERCARD', 1);
define('DISCOVER', 2);
define('AMEX', 3);

// payment solution provider
define('PROVIDER_PAYPAL', 0);
define('PROVIDER_SHOPAY', 1);

// wave transaction fee ?
define('NOWAIVE', 0);
define('WAIVED', 1);

// payment solution product
define('ADAPTIVE_PAYMENTS', 0);

// payment solution subproduct
define('CHAINED_PAYMENTS', 0);

// service order source
define('SELLER', 0);
define('BUYER', 1);

// user signup type
define('ANONYMOUS', 1);
define('USER', 2);
define('MERCHANT', 4);
define('ASSOCIATE', 8);
define('SHOPPER', 16);
define('ADMIN', 32);

//image type
define('ORIGINAL', 'original');
define('CONVERTED45', 45);    
define('CONVERTED70', 70);  
define('CONVERTED192', 192);  
define('CONVERTED236', 236);  
define('CONVERTED550', 550);  
define('CONVERTED736', 736);  

// coupon system constants
// coupon offer type
define('PERCENTAGE_OFF', 1);
define('FLAT_VALUE_OFF', 2);
define('BUNDLE', 3);

// coupon scope
define('SITE', 1);
define('STORE', 2);
define('PRODUCT', 3);
define('AMAZON_PRODUCT', 4);

// order pay status
define('ORDER_CANCELED', 1);
define('ORDER_UNPAID', 2);
define('ORDER_PAID', 3);
define('ORDER_SHIPPED', 4);
define('ORDER_COMPLETED', 5);

define('DEFAULT_PRODUCT_IMAGE', '/img/default_product_picture.jpg');
define('DEFAULT_STORE_AVATAR', '/img/merchant_placeholder.jpg');
define('DEFAULT_CONVERTED_STORE_AVATAR', '/img/merchant_placeholder_120_120.jpg');
//define('SHOPINTEREST_LOGO', '/img/shopinterest-logo-new.png');
define('SHOPINTEREST_LOGO', '/img/header-logo.png');

define('CSV_TEMPLATE_FILE_PATH', '/csv/sample.csv');

// limitations
define('LIMITATION_TAG_MAX_LENGTH', 16);

// scheme for http
define('HTTP', 1);
define('HTTPS', 2);
define('BOTH', 3);

// allow resell
define('SELL_ONLY', 0);
define('RESELL_WITHOUT_CHECKOUT', 1);
define('RESELL_WITH_CHECKOUT', 1);

// featured products
define('NOT_FEATURED', 0);
define('SLIDER_FEATURED', 1);
define('CATEGORY_FEATURED', 2);
define('AD_FEATURED', 3);
// pub-sub channel
define('UPDATE_PRODUCT_STATUS', 'update_product_status');

// DAL operator
define('DEL_KV', 1);
define('DEL_KList', 2);
define('DEL_KList_KV', 3);
define('ADD_KList', 4);

// redis session time out
define('REMEMBERME_SESSION_TIME_OUT', 60*60*24*30);
define('REDIS_KEY_TIME_OUT', 60*60*24*7);

// aid
define('DEFAULT_AID', 'assoc37'); // xxx@shopinterest.co

// service status
define('SUCCESS', 0);
define('FAILURE', 1);
