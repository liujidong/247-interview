<?php

define('PROFILE_SAVED', 201);
define('PINS_SAVED',202);
define('PRODUCT_SAVED',203);
define('STORE_CLOSED',204);

define('PRODUCTS_PARTIAL_SAVED',501);
define('PRODUCT_QUANTITY_ERROR', 502);

define('INVALID_EMAIL', 601);
define('INVALID_PASSWORD', 602);
define('INVALID_PINTEREST_USERNAME', 603);
define('EXISTED_LOGIN', 604);
define('EXISTED_PINTEREST_ACCOUNT', 605);
define('INVALID_PRODUCT_NAME', 606);
define('INVALID_PRODUCT_PRICE', 607);
define('INVALID_SIZE_ERROR', 608);
define('DIR_ERROR', 609);
define('CONTENT_LENGTH_ERROR', 610);
define('FILE_EMPTY_ERROR', 611);
define('FILE_SIZE_ERROR', 612);
define('FILE_EXT_ERROR', 613);
define('FILE_UPLOAD_ERROR', 614);
define('INVALID_STORE_NAME', 615);
define('INVALID_MERCHANT_LOGIN', 616);
define('INVALID_STORE_SUBDOMAIN', 617);
define('PRODUCT_NAME_ERROR', 618);
define('PRODUCT_PRICE_ERROR', 619);
define('PRODUCT_PIC_ERROR', 620);
define('N0_PRODUCT_ERROR', 621);
define('INVALID_ADDRESS', 622);
define('NO_PRODUCT_SELECTED', 623);
define('INITIATE_PAYPAL_FAILURE', 624);
define('INVALID_FEEDBACK_ERROR', 625);
define('CURL_FAILED', 626);
define('UNKNOWN_SENDGRID_STATUS', 627);
define('SENDGRID_SEND_FAILED', 628);
define('INVALID_VERIFICATION_CODE', 629);
define('USERNAME_NOT_EXIST', 630);
define('IMAGE_CONVERT_ERROR',631);
define('INVALID_PAYPAL_ACCOUNT', 632);
define('INVALID_TICKET_SUBJECT', 633);
define('INVALID_TICKET_DESCRIPTION', 634);
define('NO_BOARDS_SELECTED', 635);
define('NO_BOARD_AVAILABLE', 636);
define('NO_INVITATION_CODE', 637);
define('PRODUCT_SHIPPING_ERROR', 638);

define('CSV_FILE_HEADER_ERROR',640);
define('DEAL_STORE_ID_ERROR',641);
define('DEAL_SCOPE_ERROR',642);
define('DEAL_START_TIME_ERROR',643);
define('DEAL_END_TIME_ERROR',644);
define('DEAL_OFFER_TYPE_ERROR',645);
define('DEAL_OFFER_VALUE_ERROR',646);
define('DEAL_USAGE_LIMIT_ERROR',647);
define('INVALID_PRODUCT_NAME_PRICE_PIC',648);
define('FIELDS_EMPTY_ERROR',649);
define('INVALID_WEBSITE',650);
define('INVALID_WEBSITE_URL',651);
define('INVALID_FACEBOOK_SIGNED_REQUESR',652);
define('FACEBOOKAPIEXCEPTION',653);
define('INVALID_FACEBOOK_CALL',654);
// error for coupon system
define('COUPON_STORE_ID_ERROR',660);
define('COUPON_SCOPE_ERROR',661);
define('COUPON_START_TIME_ERROR',662);
define('COUPON_END_TIME_ERROR',663);
define('COUPON_OFFER_TYPE_ERROR',664);
define('COUPON_OFFER_VALUE_ERROR',665);
define('COUPON_USAGE_LIMIT_ERROR',666);
define('COUPON_PRODUCT_ID_ERROR',667);
define('COUPON_CODE_ERROR',668);
define('COUPON_USAGE_RESTRICTION_ERROR',669);
define('COUPON_NOT_EXIST', 700);
define('COUPON_NOT_ACTIVE', 701);
define('COUPON_EXCEED_USAGE', 702);
define('COUPON_PRICE_PERCENTAGE_OFF_INVALID', 703);
define('COUPON_SHIPPING_PERCENTAGE_OFF_INVALID', 704);

define('PAYMENT_FAILURE', 705);
define('INVALID_EMAIL_SUBJECT', 706);
define('INVALID_EMAIL_CONTENT', 707);
define('EXCEED_MAX_ALLOWED_CUSTOMER_EMAILS', 708);
define('CONTACTS_IMPORT_ERROR', 709);
define('INVALID_RETURN_POLICY',710);
define('INVALID_FIRST_LAST_NAME',711);
define('INVALID_ASSOCIATE_LOGIN', 712);
define('PRODUCT_COMMISSION_ERROR', 713);
define('NEGATIVE_INCOME', 714);
define('INVALID_CONFIRM_PASSWORD', 715);
define('PRODUCT_CANT_FIND_ERROR', 716);
define('INVALID_QUERY',717);
define('INVALID_BIRTHDATE',750);
define('INVALID_GENGER', 751);
define('JOIN_SALESNETWORK_WITHOUT_ACTIVE_STORE', 752);
define('INVALID_LOGIN', 753);
define('INVALID_STATE_TAX_RATE', 754);
define('INVALID_STORE_SHIPPING', 755);
define('INVALID_STORE_ADDITIONAL_SHIPPING', 756);
define('INVALID_PHONE_NUMBER', 757);
define('STATE_TAX_RATE_GREATER_THAN_TWENTY', 758);
define('INVALID_STORE_EXTERNAL_WEBSITE_URL', 759);
define('INVALID_COUNTRY_NAME', 760);
define('EMPTY_PRODUCT_PHOTO', 761);
define('PICTURE_NOT_EXIST', 762);
define('INVALID_USER_CATEGORY', 763);
define('INVALID_STORE_ID', 764);
define('INVALID_PRODUCT_ID', 765);
define('INVALID_DATETIME', 766);
define('INVALID_DATE_RANGE', 767);
define('INVALID_AUCTION', 768);
define('INVALID_BID_PRICE', 769);
define('BID_ERROR', 770);

define('DIFF_CURRENCY_FOR_CART', 772);
define('NC_UNSPPORTED_PAYMETHOD', 773);
define('PAYPAL_VAULT_STORE_CARD_ERROR', 774);
define('PAYPAL_VAULT_PAY_ERROR', 775);
define('NO_SUCH_MYORDER', 776);
define('INVALID_CURRENCY_CODE',777);
define('INVALID_AUTHTOKEN',778);
define('EXISTED_STORE_SUBDOMAIN', 779);
define('INAVALID_FEATURED_PRODUCTS_CREATE_PARAMS', 780);
define('INVALID_PRODUCT_URL', 781);
define('NO_TABLEOBJECT_DEFINED', 782);
define('INAVALID_FEATURED_PRODUCTS_UPDATE_PARAMS', 783);
define('PRODUCT_HAS_BEEN_FEATURED', 784);
define('PRODUCT_IS_INACTIVE', 785);
define('INAVALID_FEATURED_PRODUCTS_DELETE_PARAMS', 786);
define('PRODUCT_HAS_DIFFERENT_CATEGORY', 787);
define('PRODUCT_SOLDOUT', 788);

define('NO_IPN_TRACK_ID', 789);
define('IPN_NOT_FROM_PAYPAL', 790);
define('NOT_SUBSCRIPTION_IPN', 791);
define('IPN_HAS_NO_STORE_ID', 792);
define('IPN_STORE_ID_INVALID', 793);
define('DUPLICATE_SUBSCRIPTION', 794);
define('NON_SUBSCRIBER_MADE_PAYMENT', 795);
define('SUBSCRIBER_ID_NOT_MATCH', 796);
define('SUBSCRIBER_PAYMENT_FAILED', 797);

define('DIFF_DEALER_FOR_CART', 772);

$GLOBALS['errors'] = array(
    PROFILE_SAVED => array('msg' => 'Profile info saved successfully.'),
    PINS_SAVED=>array('msg' => 'Pins info saved successfully.'),
    PRODUCT_SAVED=>array('msg'=>'Your product was successfully added to your store.'),
    STORE_CLOSED => array('msg' => 'Store closed successfully.'), 
    PRODUCTS_PARTIAL_SAVED => array('msg' => 'Only products with a price given were added'), 
    INVALID_EMAIL => array('msg' =>'Please provide a valid email.'),
    INVALID_PASSWORD => array('msg' => 'Please provide a valid password'),
    INVALID_CONFIRM_PASSWORD => array('msg' => 'The two password fields didn\'t match.'),
    INVALID_PINTEREST_USERNAME => array('msg' => 'Please provide a valid pinterest username.'),
    EXISTED_LOGIN => array('msg' => 'This email has already been registered.'),
    EXISTED_PINTEREST_ACCOUNT => array('msg' => 'This pinterest account has already been used.'),
    INVALID_PRODUCT_NAME => array('msg' => 'Please provide a valid name to the product'),
    INVALID_PRODUCT_PRICE => array('msg' => 'Please provide a valid price to the product'),
    INVALID_SIZE_ERROR => array('msg' => 'Increase post_max_size and upload_max_filesize'),
    DIR_ERROR  => array('msg' => 'Upload directory isn\'t writable'),
    CONTENT_LENGTH_ERROR => array('msg' => 'Getting content length is not supported'),
    FILE_EMPTY_ERROR => array('msg' => 'File is empty'),
    FILE_SIZE_ERROR => array('msg' => 'File is too large'),
    FILE_EXT_ERROR => array('msg' => 'Please provide a valid file type'),
    FILE_UPLOAD_ERROR => array('msg'=>"Could not save uploaded file.The upload was cancelled, or server error encountered"),
    INVALID_STORE_NAME => array('msg' => 'Please provide a valid store name with less than 50 characters.'),
    INVALID_MERCHANT_LOGIN => array('msg' => 'Please provide a valid login credential'),
    INVALID_LOGIN => array('msg' => 'Please provide a valid login credential'),    
    INVALID_ASSOCIATE_LOGIN => array('msg' => 'Please provide a valid login credential'),
    INVALID_STORE_SUBDOMAIN => array('msg' => 'The store subdomain is invalid'),
    PRODUCT_NAME_ERROR => array('msg' => 'Please provide a valid product name'),
    PRODUCT_PRICE_ERROR => array('msg' => 'Please provide a valid product price'),
    PRODUCT_PIC_ERROR => array('msg' => 'Please provide a valid product picture'),
    PRODUCT_COMMISSION_ERROR => array('msg' => 'Invalid product commission.'),
    N0_PRODUCT_ERROR => array('msg' => 'Please add products to your store.'),
    INVALID_ADDRESS => array('msg' => 'Please set correct address.'),
    NO_PRODUCT_SELECTED => array('msg' => 'The shopping cart is empty'),
    INITIATE_PAYPAL_FAILURE => array('msg' => 'There seems failures on initiating paypal payment'),
    INVALID_FEEDBACK_ERROR  => array('msg' => 'Review star can not be empty'),
    CURL_FAILED => array('msg' => 'The CURL request failed.'),
    UNKNOWN_SENDGRID_STATUS => array('msg' => 'Sendgrid API returns unknown status.'),
    SENDGRID_SEND_FAILED => array('msg' => 'Sendgrid send API returns errors.'),
    INVALID_VERIFICATION_CODE => array('msg' => 'The verification code is invalid'),
    USERNAME_NOT_EXIST => array('msg' => 'The username doesnt exist'),
    IMAGE_CONVERT_ERROR => array('msg' => 'The upload image can not be converted to JPG'),
    INVALID_PAYPAL_ACCOUNT => array('msg' => 'The paypal account is invalid'),
    INVALID_TICKET_SUBJECT => array('msg' => 'Please fill ticket subject'),
    INVALID_TICKET_DESCRIPTION => array('msg' => 'Please fill ticket description'),
    NO_BOARDS_SELECTED => array('msg' => 'You need at least select one board'),
    NO_BOARD_AVAILABLE => array('msg' => 'It seems there is no board created for this pinterest account'),
    NO_INVITATION_CODE => array('msg' => 'We will send you an invite email soon'),
    PRODUCT_SHIPPING_ERROR =>  array('msg' => 'Please provide correct shipping fee'),
    INVALID_PRODUCT_NAME_PRICE_PIC=>array('msg'=>"Please provide product name, price and picture."),
    FIELDS_EMPTY_ERROR=>array('msg'=>"Required fields empty"),    
    CSV_FILE_HEADER_ERROR => array('msg' => 'Input CSV file has incorrect header' ),
    DEAL_STORE_ID_ERROR => array('msg'=>'Deal Store Id error. We only support store and product deal now.'),
    DEAL_SCOPE_ERROR => array('msg'=>'Deal scrope error. We only support store and product deal now. Please check your input.'),
    DEAL_START_TIME_ERROR => array('msg'=>'Deal start time error.'),
    DEAL_END_TIME_ERROR => array('msg'=>'Deal end time error.'),
    DEAL_OFFER_TYPE_ERROR => array('msg'=>'Please set correct offer type.'),
    DEAL_OFFER_VALUE_ERROR => array('msg'=>'Please set correct offer value.'),
    DEAL_USAGE_LIMIT_ERROR => array('msg'=>'Please set deal usage limit.'),
    // conpon errors
    COUPON_STORE_ID_ERROR => array('msg'=>'Coupon Store Id error. We only support store and product coupon now.'),
    COUPON_PRODUCT_ID_ERROR => array('msg'=>'Coupon Product Id error. We only support store and product coupon now.'),    
    COUPON_SCOPE_ERROR => array('msg'=>'Coupon scrope error. We only support store and product coupon now. Please check your input.'),
    COUPON_START_TIME_ERROR => array('msg'=>'Coupon start time error.'),
    COUPON_END_TIME_ERROR => array('msg'=>'Coupon end time error.'),
    COUPON_OFFER_TYPE_ERROR => array('msg'=>'Please set correct offer type.'),
    COUPON_OFFER_VALUE_ERROR => array('msg'=>'Please set correct offer value.'),
    COUPON_USAGE_LIMIT_ERROR => array('msg'=>'Please set coupon usage limit.'),
    COUPON_USAGE_RESTRICTION_ERROR => array('msg'=>'Please set coupon usage restriction.'),    
    COUPON_CODE_ERROR => array('msg'=>'Coupon code has used before'),
    COUPON_NOT_EXIST => array('msg' => 'This coupon code doesnt exist.'),
    COUPON_NOT_ACTIVE => array('msg' => 'This is an invalid coupon code'),
    COUPON_EXCEED_USAGE => array('msg' => 'Sorry. But this coupon has been sold out. We won\'t charge you.'),   
    COUPON_PRICE_PERCENTAGE_OFF_INVALID => array('msg' => 'The percentage off of the price is not valid.'),    
    COUPON_SHIPPING_PERCENTAGE_OFF_INVALID => array('msg' => 'The percentage off of the shipping is not valid.'),
    PAYMENT_FAILURE => array('msg' => 'Oops! Your payment did not go through.'),
    INVALID_FIRST_LAST_NAME => array('msg' => 'Please provide valid first and last name.'),
    INVALID_EMAIL_SUBJECT => array('msg' => 'Email Subject is invalid.'),
    INVALID_EMAIL_CONTENT => array('msg' => 'Email Content is invalid.'),
    EXCEED_MAX_ALLOWED_CUSTOMER_EMAILS => array('msg' => 'You exceed the max allowed emails sent to customers.'),
    CONTACTS_IMPORT_ERROR => array('msg' => 'There is a problem when importing your contacts.'),
    INVALID_RETURN_POLICY => array('msg' => 'Please set your return policy.'),
    INVALID_QUERY => array('msg' => 'Please check your query.'),
    NEGATIVE_INCOME => array('msg' => 'This transaction brings negative income for the seller.'),
    PRODUCT_CANT_FIND_ERROR =>array('msg' => 'Product cant\'t be found.'),
    INVALID_BIRTHDATE => array('msg' => 'The Birthday is invalid.'),
    INVALID_GENGER => array('msg' => 'The gender is invalid'),
    JOIN_SALESNETWORK_WITHOUT_ACTIVE_STORE => array('msg'=>"Please launch your store firstly."),
    INVALID_WEBSITE => array('msg' =>'Please provide a valid website name.'),
    INVALID_WEBSITE_URL => array('msg' =>'Please provide a valid website url.'),
    INVALID_FACEBOOK_SIGNED_REQUESR => array('msg' => 'This is an invalid facebook signed request.'),
    FACEBOOKAPIEXCEPTION => array('msg' => 'Facebook api error.'),
    INVALID_FACEBOOK_CALL => array('msg' => 'You call an facebook function not defined.'),
    PRODUCT_QUANTITY_ERROR => array('msg' => 'Sorry, but the zero quantity product won\'t show up anywhere.'),
    INVALID_STATE_TAX_RATE => array('msg' => 'State tax rate should be numerical'),
    INVALID_STORE_SHIPPING => array('msg' => 'The shopping price for your items should be numerical'),
    INVALID_STORE_ADDITIONAL_SHIPPING => array('msg' => 'Additional Items Shipping Price should be numerical.'),
    INVALID_PHONE_NUMBER => array('msg' => 'Please provide valid phone number.'),
    STATE_TAX_RATE_GREATER_THAN_TWENTY => array('msg' => 'The state tax rate should be less than 20.'),
    INVALID_STORE_EXTERNAL_WEBSITE_URL => array('msg' => 'Wrong store external website url.'),
    INVALID_COUNTRY_NAME => array('msg' => 'Please select your country.'),
    EMPTY_PRODUCT_PHOTO => array('msg' => 'Product {{id}} has no photo'),
    PICTURE_NOT_EXIST => array('msg' => 'Picture not exist'),
    INVALID_USER_CATEGORY => array('msg' => 'Please select a user category.'),
    INVALID_STORE_ID => array('msg' => 'No such store'),
    INVALID_PRODUCT_ID => array('msg' => 'No sunch product.'),
    INVALID_DATETIME => array('msg' => 'Please provide a valid date time.'),
    INVALID_DATE_RANGE => array('msg' => 'Please provide a valid date range.'),
    INVALID_AUCTION => array('msg' => 'Please provide a valid auction.'),
    INVALID_BID_PRICE => array('msg' => 'Please provide a valid bid price.'),
    BID_ERROR => array('msg' => 'bid error, please try again later.'),
    INVALID_CURRENCY_CODE => array('msg' => 'Please select a valid currecny.'),
    INVALID_AUTHTOKEN => array('msg' => 'Invalid authtoken'),
    DIFF_CURRENCY_FOR_CART => array('msg' => 'You can\'t add products of this currency to your cart.'),
    NC_UNSPPORTED_PAYMETHOD => array('msg' => 'Your pay menthod is unsupported.'),
    PAYPAL_VAULT_STORE_CARD_ERROR => array('msg' => 'Your Credit Card is invalid.'),
    PAYPAL_VAULT_PAY_ERROR => array('msg' => 'An error occured during your payment.'),
    NO_SUCH_MYORDER => array('msg' => 'No such order.'),
    EXISTED_STORE_SUBDOMAIN => array('msg' => 'The store subdomain has been taken.'),
    INAVALID_FEATURED_PRODUCTS_CREATE_PARAMS => array('msg' => 'Create a featured product need pass product_url, featured_score'),
    INVALID_PRODUCT_URL => array('msg' => 'The product URL is invalid'),
    NO_TABLEOBJECT_DEFINED => array('msg' => 'There is no such table object defined'),
    INAVALID_FEATURED_PRODUCTS_UPDATE_PARAMS => array('msg' => 'Update a featured product need pass featured_score and row id'),
    PRODUCT_HAS_BEEN_FEATURED => array('msg' => 'The product has been featured'),
    PRODUCT_IS_INACTIVE => array('msg' => 'The product is inactive'),
    INAVALID_FEATURED_PRODUCTS_DELETE_PARAMS => array('msg' => 'Delete a featured product need pass row id'),
    PRODUCT_HAS_DIFFERENT_CATEGORY => array('msg' => 'The product is in a different category.'),
    PRODUCT_SOLDOUT => array('msg' => 'Some products is soldout in your cart.'),
    NO_IPN_TRACK_ID => array('msg' => 'IPN Track ID is not valid'),
    IPN_NOT_FROM_PAYPAL => array('msg' => 'IPN is not from Paypal.'),
    NOT_SUBSCRIPTION_IPN => array('msg' => 'IPN is not a subscription IPN.'),
    IPN_HAS_NO_STORE_ID => array('msg' => 'IPN contains no store id.'),
    IPN_STORE_ID_INVALID => array('msg' => 'STORE ID is invalid in the IPN'),
    DUPLICATE_SUBSCRIPTION => array('msg' => 'Possible duplicate subscription'),
    NON_SUBSCRIBER_MADE_PAYMENT => array('msg' => 'A non-subscriber made a payment.'),
    SUBSCRIBER_ID_NOT_MATCH => array('msg' => 'The payer has a different subscriber ID.'),
    SUBSCRIBER_PAYMENT_FAILED => array('msg' => 'The subscription payment failed.'),
    DIFF_DEALER_FOR_CART => array('msg' => 'You can\'t add products from this dealer to your cart.'),
);

