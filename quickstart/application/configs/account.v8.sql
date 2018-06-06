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

CREATE TABLE `paypal_accounts_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paypal_account_id` int(11) not null default '0',
  `address_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paypal_accounts_addresses` (`paypal_account_id`, `address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table `stores` add column `optin_salesnetwork` tinyint not null default '0' after `external_website`;

alter table `paypal_accounts` add column `payment_account_id` int(11) not null default '0' after `status`;

alter table `search_products` add column `product_commission` double NOT NULL DEFAULT '0' after `product_end_date`;

alter table `search_products` add column `store_optin_salesnetwork` double NOT NULL DEFAULT '0' after `store_tags`;

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

alter table `coupons` add column `free_shipping` tinyint not null default '0' after `offer_description`;

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

alter table `addresses` add column `first_name` varchar(50) NOT NULL DEFAULT '' after `status`;

alter table `addresses` add column `last_name` varchar(50) NOT NULL DEFAULT '' after `first_name`;

alter table `addresses` add column `name` varchar(255) NOT NULL DEFAULT '' after `last_name`;

alter table `service_orders` add column `payment_status` varchar(50) not null default '' after `status`;

alter table `sales` add column `payment_status` varchar(50) not null default '' after `status`;

update version set version=8;