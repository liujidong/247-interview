CREATE TABLE `fb_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `about` text NOT NULL DEFAULT '',
  `bio` varchar(255) NOT NULL DEFAULT '',
  `birthday` varchar(50) NOT NULL DEFAULT '',
  `education` text NOT NULL DEFAULT '', /* json string*/
  `email` varchar(50) not null default '',
  `first_name` varchar(50) not null default '',
  `gender` varchar(50) not null default '',
  `external_id` varchar(50) not null default '',
  `last_name` varchar(50) not null default '',
  `link` varchar(255) not null default '',
  `locale` varchar(50) not null default '',
  `name` varchar(50) not null default '',
  `quotes` text not null default '',
  `timzone` varchar(50) not null default '',
  `updated_time` varchar(50) not null default '',
  `username` varchar(50) not null default '',
  `verified` varchar(50) not null default '',
  `work` text not null default '', /* json string */
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `twitter_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `description` text NOT NULL DEFAULT '',
  `profile_image_url` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `location` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(50) not null default '',
  `screen_name` varchar(50) not null default '',
  `external_id` varchar(50) not null default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `merchants_fb_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) not null default '0',
  `fb_user_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchants_fb_users` (`merchant_id`, `fb_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `merchants_twitter_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) not null default '0',
  `twitter_user_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchants_twitter_users` (`merchant_id`, `twitter_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `merchants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `phone_number` varchar(50) NOT NULL DEFAULT '',
  `email_verified` tinyint not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `subdomain` varchar(50) not null default '',
  `name` varchar(50) not null default '',
  `description` text not null default '',
  `return_policy` text not null default '',
  `host` varchar(50) not null default '',
  `featured` tinyint not null default '0',
  `logo` varchar(255) not null default '',
  `converted_logo` varchar(255) not null default '',
  `tax` double NOT NULL DEFAULT '0',
  `shipping` double NOT NULL DEFAULT '0',
  `additional_shipping` double NOT NULL DEFAULT '0',
  `no_international_shipping` tinyint not null default '0',
  `external_website` varchar(255) NOT NULL DEFAULT '',
  `optin_salesnetwork` tinyint not null default '0',
  `payment_solution` tinyint not null default '0',
  `transaction_fee_waived` tinyint not null default '0',
  `country` varchar(10) NOT NULL DEFAULT '',
  `currency` varchar(10) NOT NULL DEFAULT '',
  `excluded_in_search` tinyint not null default '0',
  `allow_resell` tinyint not null default '0',
  `tags` text NOT NULL default '',
  `subscribed` datetime DEFAULT '0000-00-00 00:00:00',
  `subscr_id` varchar(50) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subdomain` (`subdomain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `merchants_stores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) not null default '0',
  `store_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchants_stores` (`merchant_id`, `store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `paypal_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `payer_id` varchar(50) NOT NULL DEFAULT '',
  `country_code` varchar(50) NOT NULL DEFAULT '',
  `payer_status` varchar(50) not null default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `merchants_paypal_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) not null default '0',
  `paypal_account_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchants_paypal_accounts` (`merchant_id`, `paypal_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `shoppers_paypal_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopper_id` int(11) not null default '0',
  `paypal_account_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shoppers_paypal_accounts` (`shopper_id`, `paypal_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pinterest_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `external_id` varchar(50) NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `about` text NOT NULL default '',
  `twitter_link` varchar(255) NOT NULL DEFAULT '',
  `is_following` tinyint(4) NOT NULL DEFAULT '0',
  `facebook_link` varchar(255) NOT NULL DEFAULT '',
  `image_url` varchar(255) NOT NULL DEFAULT '',
  `location` varchar(50) NOT NULL DEFAULT '',
  `full_name` varchar(50) NOT NULL DEFAULT '',
  `image_large_url` varchar(255) DEFAULT '',
  `followers` int(11) NOT NULL DEFAULT '0',
  `likes` int(11) NOT NULL DEFAULT '0',
  `followings` int(11) NOT NULL DEFAULT '0',
  `pins` int(11) NOT NULL DEFAULT '0',
  `boards` int(11) NOT NULL DEFAULT '0',
  `repins` int(11) NOT NULL DEFAULT '0',
  `pic_upload_time` datetime DEFAULT '0000-00-00 00:00:00',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pinterest_accounts_pinterest_boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinterest_account_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_board_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_board` (`pinterest_account_id`, `pinterest_board_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pinterest_boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `external_id` varchar(50) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `category` varchar(50) NOT NULL DEFAULT '',
  `is_collaborator` tinyint(4) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `is_following` tinyint(4) NOT NULL DEFAULT '0',
  `thumbnails` text NOT NULL DEFAULT '',
  `followers` int(11) NOT NULL DEFAULT '0',
  `pins` int(11) NOT NULL DEFAULT '0',
  `pic_upload_time` datetime DEFAULT '0000-00-00 00:00:00',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pinterest_boards_pinterest_pins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinterest_board_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_pin_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `board_pin` (`pinterest_board_id`, `pinterest_pin_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pinterest_pins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `external_id` varchar(50) NOT NULL DEFAULT '0',
  `domain` varchar(50) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `images_mobile` varchar(255) NOT NULL DEFAULT '',
  `images_closeup` varchar(255) NOT NULL DEFAULT '',
  `images_thumbnail` varchar(255) NOT NULL DEFAULT '',
  `images_board` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT '0000-00-00 00:00:00',
  `is_repin` tinyint NOT NULL DEFAULT '0',
  `is_video` tinyint(4) NOT NULL DEFAULT '0',
  `source` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `comments` int(11) NOT NULL DEFAULT '0',
  `likes` int(11) NOT NULL DEFAULT '0',
  `repins` int(11) NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `pic_upload_time` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `merchants_pinterest_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_account_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchant_pinterest_account` (`merchant_id`, `pinterest_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stores_pinterest_boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_board_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_pinterest_board` (`store_id`, `pinterest_board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stores_pinterest_pins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_pin_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_pinterest_pin` (`store_id`, `pinterest_pin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `data` text not null DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `shoppers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `referred_by` varchar(50) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stores_shoppers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) not null default '0',
  `shopper_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stores_shoppers` (`store_id`, `shopper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `shoppers_fb_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopper_id` int(11) not null default '0',
  `fb_user_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shoppers_fb_users` (`shopper_id`, `fb_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `shoppers_twitter_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopper_id` int(11) not null default '0',
  `twitter_user_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shoppers_twitter_users` (`shopper_id`, `twitter_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `addr1` varchar(255) not null default '',
  `addr2` varchar(255) not null default '',
  `city` varchar(50) not null default '',
  `state` varchar(50) not null default '',
  `country` varchar(50) not null default '',
  `zip` varchar(50) not null default '',
  `paypal_address_status` varchar(50) not null default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `merchants_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) not null default '0',
  `address_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchants_addresses` (`merchant_id`, `address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `paypal_accounts_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paypal_account_id` int(11) not null default '0',
  `address_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paypal_accounts_addresses` (`paypal_account_id`, `address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `tag` varchar(50) not null default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stores_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) not null default '0',
  `tag_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stores_tags` (`store_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `service_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `total` double NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL default '',
  `status` tinyint not null default '0',
  `created` datetime not null DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `waiting_merchants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `pinterest_username` varchar(50) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `store_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `score` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_status` tinyint(4) NOT NULL DEFAULT '0',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_description` text NOT NULL,
  `product_size` varchar(50) NOT NULL DEFAULT '',
  `product_quantity` int(11) NOT NULL DEFAULT '0',
  `product_price` double NOT NULL DEFAULT '0',
  `product_shipping` double NOT NULL DEFAULT '0',
  `product_pinterest_pin_id` int(11) NOT NULL DEFAULT '0',
  `product_ext_ref_id` varchar(50) NOT NULL DEFAULT '',
  `product_ext_ref_url` varchar(255) NOT NULL DEFAULT '',
  `product_brand` varchar(255) NOT NULL DEFAULT '',
  `product_misc` text NOT NULL,
  `product_start_date` datetime DEFAULT '0000-00-00 00:00:00',
  `product_end_date` datetime DEFAULT '0000-00-00 00:00:00',
  `category` varchar(50) NOT NULL DEFAULT '',
  `category_description` varchar(255) NOT NULL DEFAULT '',
  `pic_ids` varchar(255) NOT NULL DEFAULT '',
  `pic_types` varchar(255) NOT NULL DEFAULT '',
  `pic_sources` varchar(255) NOT NULL DEFAULT '',
  `pic_urls` text NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `store_status` tinyint(4) NOT NULL DEFAULT '0',
  `store_subdomain` varchar(50) NOT NULL DEFAULT '',
  `store_name` varchar(50) NOT NULL DEFAULT '',
  `store_host` varchar(50) NOT NULL DEFAULT '',
  `store_featured` tinyint(4) NOT NULL DEFAULT '0',
  `store_logo` varchar(255) NOT NULL DEFAULT '',
  `store_tax` double NOT NULL DEFAULT '0',
  `store_shipping` double NOT NULL DEFAULT '0',
  `store_additional_shipping` double NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_product_id` (`store_id`,`product_id`),
  KEY `product_pinterest_pin_id` (`product_pinterest_pin_id`),
  KEY `product_ext_ref_id` (`product_ext_ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `search_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_status` tinyint(4) NOT NULL DEFAULT '0',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_description` text NOT NULL,
  `product_size` varchar(50) NOT NULL DEFAULT '',
  `product_quantity` int(11) NOT NULL DEFAULT '0',
  `product_price` double NOT NULL DEFAULT '0',
  `product_shipping` double NOT NULL DEFAULT '0',
  `product_pinterest_pin_id` int(11) NOT NULL DEFAULT '0',
  `product_ext_ref_id` varchar(50) NOT NULL DEFAULT '',
  `product_ext_ref_url` varchar(255) NOT NULL DEFAULT '',
  `product_brand` varchar(255) NOT NULL DEFAULT '',
  `product_misc` text NOT NULL,
  `product_start_date` datetime DEFAULT '0000-00-00 00:00:00',
  `product_end_date` datetime DEFAULT '0000-00-00 00:00:00',
  `product_commission` double NOT NULL DEFAULT '0',
  `global_category_id` int(11) NOT NULL DEFAULT '0',
  `category` varchar(50) NOT NULL DEFAULT '',
  `category_description` varchar(255) NOT NULL DEFAULT '',
  `pic_ids` varchar(255) NOT NULL DEFAULT '',
  `pic_types` varchar(255) NOT NULL DEFAULT '',
  `pic_sources` varchar(255) NOT NULL DEFAULT '',
  `pic_urls` text NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `store_status` tinyint(4) NOT NULL DEFAULT '0',
  `store_subdomain` varchar(50) NOT NULL DEFAULT '',
  `store_name` varchar(50) NOT NULL DEFAULT '',
  `store_host` varchar(50) NOT NULL DEFAULT '',
  `store_featured` tinyint(4) NOT NULL DEFAULT '0',
  `store_logo` varchar(255) NOT NULL DEFAULT '',
  `store_tax` double NOT NULL DEFAULT '0',
  `store_shipping` double NOT NULL DEFAULT '0',
  `store_additional_shipping` double NOT NULL DEFAULT '0',
  `store_tags` varchar(50) not null default '',
  `store_optin_salesnetwork` tinyint not null default '0',
  `featured` tinyint(4) NOT NULL DEFAULT '0',
  `score` int(11) NOT NULL DEFAULT '0',
  `excluded_in_search` tinyint not null default '0',
  `page_views` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_product_id` (`store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `global_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_status` tinyint(4) NOT NULL DEFAULT '0',  
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_description` text NOT NULL,
  `product_quantity` int(11) NOT NULL DEFAULT '0',
  `product_price` double NOT NULL DEFAULT '0',
  `product_commission` double NOT NULL DEFAULT '0',
  `product_global_category_id` int(11) NOT NULL DEFAULT '0',
  `product_global_category_path` varchar(1023) NOT NULL DEFAULT '',
  `product_resell` tinyint not null default '0',
  `product_purchase_url` text NOT NULL,
  `product_featured` tinyint not null default '0',
  `product_featured_score` int(11) NOT NULL DEFAULT '0',
  `product_created` datetime DEFAULT '0000-00-00 00:00:00',
  `product_updated` datetime DEFAULT '0000-00-00 00:00:00',
  `product_picture_count` tinyint not null default '0',
  `product_pictures` text NOT NULL,
  `product_tags` text NOT NULL,
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_product_id` (`store_id`,`product_id`),
  FULLTEXT(`product_name`, `product_description`, `product_global_category_path`, `product_tags`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `code` varchar(50) NOT NULL default '',
  `scope` tinyint(4) NOT NULL DEFAULT '0',
  `category` varchar(50) NOT NULL DEFAULT '',
  `price_offer_type` tinyint(4) NOT NULL DEFAULT '0',
  `shipping_offer_type` tinyint(4) NOT NULL DEFAULT '0',
  `offer_details` text NOT NULL default '',
  `offer_description` text NOT NULL default '',
  `free_shipping` tinyint not null default '0',
  `usage_limit` int(11) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `start_time` datetime not null DEFAULT '0000-00-00 00:00:00',
  `end_time` datetime not null DEFAULT '0000-00-00 00:00:00',
  `created` datetime not null DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `category` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `global_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `depth` int(11) NOT NULL DEFAULT '0',
  `path` varchar(1023) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `rank` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_parent` (`name`, `parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `associates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `aid` varchar(50) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `phone_number` varchar(50) NOT NULL DEFAULT '',
  `external_website` varchar(255) NOT NULL DEFAULT '',
  `external_website_name` varchar(255) NOT NULL DEFAULT '',
  `external_website_content` varchar(255) NOT NULL DEFAULT '',
  `external_website_description` text NOT NULL DEFAULT '',
  `external_website_monthly_unique_visitors` int(11) NOT NULL DEFAULT '0',
  `marketing_channel` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `aid` (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `associates_pinterest_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_account_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `associate_pinterest_account` (`associate_id`, `pinterest_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `associates_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL DEFAULT '0',
  `address_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `associate_address` (`associate_id`, `address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `associates_paypal_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) not null default '0',
  `paypal_account_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `associate_paypal_accounts` (`associate_id`, `paypal_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `associates_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `associate_id` int(11) not null default '0',
  `store_id` int(11) not null default '0',
  `product_id` int(11) not null default '0',
  `clicks` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `associate_store_product` (`associate_id`, `store_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `associate_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_price` double NOT NULL DEFAULT '0',
  `product_quantity` int(11) NOT NULL DEFAULT '0',
  `product_commission` double NOT NULL DEFAULT '0',
  `commission_amt` double NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `associate_product` (`associate_id`, `order_id`, `store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `sender` int(11) NOT NULL DEFAULT '0',
  `receiver` int(11) NOT NULL DEFAULT '0',
  `amt` double NOT NULL DEFAULT '0',
  `currency_code` varchar(50) not null default 'USD',
  `contract` text NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payment_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `payment_id` int(11) NOT NULL DEFAULT '0',
  `sender` int(11) NOT NULL DEFAULT '0',
  `receiver` int(11) NOT NULL DEFAULT '0',
  `amt` double NOT NULL DEFAULT '0',
  `currency_code` varchar(50) not null default 'USD',
  `contract` text NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `order_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `payment_item_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_order_payment` (`store_id`, `order_id`, `payment_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sale_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `sale_id` int(11) NOT NULL DEFAULT '0',
  `payment_item_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sale_payment` (`sale_id`, `payment_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `type` tinyint not null default '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `fb_user_id` int(11) not null default '0',
  `twitter_user_id` int(11) not null default '0',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `birth_day` varchar(10) NOT NULL DEFAULT '',
  `birth_month` varchar(10) NOT NULL DEFAULT '',
  `birth_year` varchar(10) NOT NULL DEFAULT '',
  `gender` varchar(10) NOT NULL DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `merchant_id` int(11) not null default '0',
  `associate_id` int(11) not null default '0',
  `aid` varchar(50) NOT NULL DEFAULT '',
  `shopper_id` int(11) not null default '0',
  `addr1` varchar(255) not null default '',
  `addr2` varchar(255) not null default '',
  `city` varchar(50) not null default '',
  `state` varchar(50) not null default '',
  `country` varchar(50) not null default '',
  `zip` varchar(50) not null default '',
  `paypal_email` varchar(50) NOT NULL DEFAULT '',
  `bank_name` varchar(255) NOT NULL DEFAULT '',
  `bank_routing_number` varchar(50) NOT NULL DEFAULT '',
  `bank_account_number` varchar(50) NOT NULL DEFAULT '',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_activity` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payment_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `paypal_account_id` int(11) not null default '0',
  `bank_account_id` int(11) not null default '0',
  `credit_card_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_payment_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) not null default '0',
  `payment_account_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_payment` (`user_id`, `payment_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) not null default '0',
  `address_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_addresses` (`user_id`, `address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `abtests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `num_shards` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `user_id` int(11) not null default '0',
  `store_id` int(11) not null default '0',
  `order_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_order` (`store_id`, `order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pin_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinterest_pin_id` int(11) NOT NULL default '0',
  `status` tinyint not null default '0',
  `external_id` varchar(50) NOT NULL DEFAULT '0',
  `image_45` varchar(255) NOT NULL DEFAULT '',
  `image_70` varchar(255) NOT NULL DEFAULT '',
  `image_192` varchar(255) NOT NULL DEFAULT '',
  `image_236` varchar(255) NOT NULL DEFAULT '',
  `image_550` varchar(255) NOT NULL DEFAULT '',
  `image_736` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_id` (`external_id`),
  UNIQUE KEY `pinterest_pin_id` (`pinterest_pin_id`),
  KEY `image_45` (`image_45`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `continent_code` varchar(10) NOT NULL DEFAULT '',
  `continent_name` varchar(50) NOT NULL DEFAULT '',
  `short_name` varchar(50) NOT NULL DEFAULT '',
  `long_name` varchar(255) NOT NULL DEFAULT '',
  `iso2` varchar(10) NOT NULL DEFAULT '',
  `iso3` varchar(10) NOT NULL DEFAULT '',
  `calling_code` varchar(10) NOT NULL DEFAULT '',
  `cctld` varchar(10) NOT NULL DEFAULT '',
  `currency_code` varchar(10) NOT NULL DEFAULT '',
  `currency_name` varchar(255) NOT NULL DEFAULT '',
  `currency_symbol` varchar(50) not null default '',
  `paypal_currency` tinyint not null default '0',
  `payment_enabled` tinyint not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`iso2`),
  KEY `currency_code` (`currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `search_product_converted_pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search_product_id` int(11) NOT NULL default '0',
  `picture_id` int(11) NOT NULL default '0',
  `picture_status` tinyint not null default '0',
  `picture_order` tinyint not null default '0',
  `picture_url` varchar(255) NOT NULL DEFAULT '',
  `converted_45` varchar(255) NOT NULL DEFAULT '',
  `converted_70` varchar(255) NOT NULL DEFAULT '',
  `converted_192` varchar(255) NOT NULL DEFAULT '',
  `converted_236` varchar(255) NOT NULL DEFAULT '',
  `converted_550` varchar(255) NOT NULL DEFAULT '',
  `converted_736` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `search_product_id_picture_id` (`search_product_id`, `picture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `missing_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `store_id` int(11) not null default '0',
  `product_id` int(11) not null default '0',
  `picture_id` int(11) not null default '0',
  `pinterest_image_url` varchar(255) NOT NULL DEFAULT '',
  `s3_url` varchar(255) NOT NULL DEFAULT '',
  `original_url` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_product_picture` (`store_id`, `product_id`, `picture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `missing_converted_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `store_id` int(11) not null default '0',
  `product_id` int(11) not null default '0',
  `picture_id` int(11) not null default '0',
  `picture_url` varchar(255) NOT NULL DEFAULT '',
  `converted_45` varchar(255) NOT NULL DEFAULT '',
  `converted_70` varchar(255) NOT NULL DEFAULT '',
  `converted_192` varchar(255) NOT NULL DEFAULT '',
  `converted_236` varchar(255) NOT NULL DEFAULT '',
  `converted_550` varchar(255) NOT NULL DEFAULT '',
  `converted_736` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_product_picture` (`store_id`, `product_id`, `picture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `auctions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `bid_times` int(11) NOT NULL DEFAULT '0',
  `initial_bid_price` double NOT NULL DEFAULT '0',
  `current_bid_price` double NOT NULL DEFAULT '0',
  `min_bid_increment` double NOT NULL DEFAULT '0',
  `start_time` datetime DEFAULT '0000-00-00 00:00:00',
  `end_time` datetime DEFAULT '0000-00-00 00:00:00',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_auctions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `auction_id` int(11) NOT NULL DEFAULT '0',
  `bid_price` double NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mycoupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `code` varchar(50) NOT NULL default '',
  `offer_name` varchar(255) NOT NULL default '',
  `offer_description` text NOT NULL default '',
  `scope` tinyint(4) NOT NULL DEFAULT '0',
  `category` varchar(50) NOT NULL DEFAULT '',
  `price_offer_type` tinyint(4) NOT NULL DEFAULT '0',
  `price_off` int(11) NOT NULL DEFAULT '0',
  `free_shipping` tinyint not null default '0',
  `usage_limit` int(11) NOT NULL DEFAULT '0',
  `usage_restriction` int(11) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `start_time` datetime not null DEFAULT '0000-00-00 00:00:00',
  `end_time` datetime not null DEFAULT '0000-00-00 00:00:00',
  `is_sale` tinyint(4) NOT NULL DEFAULT '0',
  `is_deal` tinyint(4) NOT NULL DEFAULT '0',
  `operator` varchar(20) NOT NULL default 'merchant',
  `created` datetime not null DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `credit_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `user_id` int(11) not null default '0',
  `card_number` varchar(20) NOT NULL default '',
  `expire_date` varchar(8) NOT NULL default '',
  `exp_month` varchar(10) NOT NULL DEFAULT '',
  `exp_year` varchar(10) NOT NULL DEFAULT '',
  `paypal_card_id` varchar(50) NOT NULL default '',
  `valid_until` datetime DEFAULT '0000-00-00 00:00:00',
  `verified` tinyint not null default '0',
  `billing_first_name` varchar(50) not null default '',
  `billing_last_name` varchar(50) not null default '',
  `billing_addr1` varchar(255) not null default '',
  `billing_addr2` varchar(255) not null default '',
  `billing_city` varchar(50) not null default '',
  `billing_state` varchar(50) not null default '',
  `billing_country` varchar(50) not null default '',
  `billing_zip` varchar(50) not null default '',
  `billing_phone` varchar(50) not null default '',
  `billing_email` varchar(50) not null default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  key `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- a user can own multiple carts
-- a cart can be owned by several users (support shared cart)
CREATE TABLE `users_carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) not null default '0',
  `cart_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_cart` (`user_id`, `cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- aid applies to all the products in the cart
-- coupon code can be applied to all the products or any product in the cart
-- a cart is not a transaction contract, so we only need to store the id info about the product
CREATE TABLE `carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `session_id` varchar(128) NOT NULL DEFAULT '',
  `aid` varchar(50) NOT NULL DEFAULT '',
  `coupon_code` varchar(50) NOT NULL default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `cart_id` int(11) not null default '0',
  `store_id` int(11) not null default '0',
  `product_id` int(11) not null default '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `dealer` varchar(50) NOT NULL default '',
  `external_id` varchar(50) NOT NULL default '',
  `custom_fields` text NOT NULL default '',
  `coupon_code` varchar(50) NOT NULL default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `store_product` (`store_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- a cart can be fullfilled by multiple orders [split pay]
-- an order is a contract between the seller and the buyer
-- so we need to store the information of products purchased.
-- an order can have all the items or only partial items in
-- the cart.
CREATE TABLE `myorder_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `payment_status` tinyint not null default '0',
  `cart_id` int(11) not null default '0',
  `aid` varchar(50) NOT NULL DEFAULT '',
  `coupon_code` varchar(50) NOT NULL default '',
  `total` double NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `tax` double NOT NULL DEFAULT '0',
  `shipping` double NOT NULL DEFAULT '0',
  `user_id` int(11) not null default '0',
  `payment_account_id` int(11) not null default '0',
  `payment_method` varchar(50) not null default '',
  `payment_info` varchar(255) not null default '',
  `shipping_first_name` varchar(50) not null default '',
  `shipping_last_name` varchar(50) not null default '',
  `shipping_addr1` varchar(255) not null default '',
  `shipping_addr2` varchar(255) not null default '',
  `shipping_city` varchar(50) not null default '',
  `shipping_state` varchar(50) not null default '',
  `shipping_country` varchar(50) not null default '',
  `shipping_zip` varchar(50) not null default '',
  `shipping_phone` varchar(50) not null default '',
  `shipping_email` varchar(50) not null default '',
  `currency_code` varchar(50) not null default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `myorders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `payment_status` tinyint not null default '0',
  `myorder_group_id` int(11) not null default '0',
  `store_id` int(11) not null default '0',
  `aid` varchar(50) NOT NULL DEFAULT '',
  `coupon_code` varchar(50) NOT NULL default '',
  `total` double NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `tax` double NOT NULL DEFAULT '0',
  `shipping` double NOT NULL DEFAULT '0',
  `shipping_option_id` int(11) not null default '0',
  `shipping_destination_id` int(11) not null default '0',
  `shipping_service_provider` varchar(50) not null default '',
  `tracking_number` varchar(50) not null default '',
  `expected_arrival_date` varchar(50) not null default '',
  `shipping_date` datetime DEFAULT '0000-00-00 00:00:00',  
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `myorder_group_id` (`myorder_group_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- product info affecting to the order
CREATE TABLE `myorder_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_description` text not null default '',
  `product_quantity` int(11) NOT NULL DEFAULT '0',
  `product_price` double NOT NULL DEFAULT '0',
  `product_shipping` double NOT NULL DEFAULT '0',
  `product_commission` double NOT NULL DEFAULT '0',
  `custom_fields` text NOT NULL default '',
  `coupon_code` varchar(50) NOT NULL default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `store_product_id` (`product_id`, `store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `resell_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `payment_status` tinyint not null default '0',
  `user_id` int(11) not null default '0',
  `myorder_item_id` int(11) not null default '0',
  `aid` varchar(50) not null default '',
  `commission` double NOT NULL DEFAULT '0',
  `currency` varchar(8) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `user_id` int(11) not null default '0',
  `currency` varchar(8) NOT NULL DEFAULT '0',
  `current_balance` double NOT NULL DEFAULT '0',
  `available_balance` double NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `wallet_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `wallet_id` int(11) not null default '0',
  `ref_id` int(11) not null default '0',
  `type` varchar(50) NOT NULL DEFAULT '',
  `currency` varchar(8) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `commission` double NOT NULL DEFAULT '0',
  `paypal_transaction_fee` double NOT NULL DEFAULT '0',
  `our_transaction_fee` double NOT NULL DEFAULT '0',
  `current_balance` double NOT NULL DEFAULT '0',
  `available_balance` double NOT NULL DEFAULT '0',
  `description` text NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `change_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `object_type` varchar(50) not null default '',
  `object_id` varchar(50) not null default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `annoucements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `content` text NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `subscription_ipns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) not null default '0',
  `txn_type` varchar(20) NOT NULL DEFAULT '',
  `subscr_id` varchar(50) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `mc_currency` varchar(10) NOT NULL DEFAULT '',
  `item_name` varchar(255) NOT NULL DEFAULT '',
  `business` varchar(255) NOT NULL DEFAULT '',
  `verify_sign` varchar(1023) NOT NULL DEFAULT '',
  `payer_status` varchar(20) NOT NULL DEFAULT '',
  `payer_email` varchar(50) NOT NULL DEFAULT '',
  `receiver_email` varchar(50) NOT NULL DEFAULT '',
  `payer_id` varchar(50) NOT NULL DEFAULT '',
  `other` text NOT NULL,
  `test_mode` tinyint not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `subscr_id_txn_type` (`subscr_id`, `txn_type`),
  KEY `payer_email` (`payer_email`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `type` varchar(50) NOT NULL DEFAULT '',
  `header` varchar(50) NOT NULL DEFAULT '',
  `footer` varchar(50) NOT NULL DEFAULT '',
  `category` varchar(50) NOT NULL DEFAULT '',
  `template_engine` varchar(20) NOT NULL DEFAULT '',
  `subject` text NOT NULL,
  `content` text NOT NULL,
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `typename` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `email` varchar(80) NOT NULL DEFAULT '',
  `source` varchar(50) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `tags` text NOT NULL,
  `unsubscribe` bit(64) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `from` int(11) not null default '0',
  `to` int(11) not null default '0',
  `subject` varchar(1023) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `from` (`from`),
  KEY `to` (`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `amazon_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `asin` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `commission` double NOT NULL DEFAULT '0',
  `global_category_id` int(11) NOT NULL DEFAULT '0',
  `purchase_url` text NOT NULL,
  `featured` tinyint not null default '0',
  `featured_score` int(11) NOT NULL DEFAULT '0',
  `picture_count` tinyint not null default '0',
  `pictures` text NOT NULL,
  `tags` text NOT NULL,
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asin` (`asin`),
  FULLTEXT(`name`, `description`, `tags`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `version` (
  `version` int(11) not null default '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into `version` values('38');
