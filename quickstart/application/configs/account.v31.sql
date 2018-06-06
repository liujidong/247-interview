
CREATE TABLE `credit_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `user_id` int(11) not null default '0',
  `card_number` varchar(20) NOT NULL default '',
  `paypal_id` varchar(50) NOT NULL default '',
  `valid_until` datetime DEFAULT '0000-00-00 00:00:00',
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
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  key `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


alter table `myorders` add column `payment_account_id` int(11) NOT NULL DEFAULT '0' after `to_last_name`;
alter table `myorders` add column `payment_method` varchar(50) NOT NULL DEFAULT '' after `payment_account_id`;
alter table `myorders` add column `payment_info` varchar(255) NOT NULL DEFAULT '' after `payment_method`;

alter table `myorders` add column `billing_first_name` varchar(50) NOT NULL DEFAULT '' after `payment_info`;
alter table `myorders` add column `billing_last_name` varchar(50) NOT NULL DEFAULT '' after `billing_first_name`;
alter table `myorders` add column `billing_phone` varchar(50) NOT NULL DEFAULT '' after `billing_zip`;
alter table `myorders` add column `billing_email` varchar(50) NOT NULL DEFAULT '' after `billing_phone`;

alter table `myorders` add column `shipping_first_name` varchar(50) NOT NULL DEFAULT '' after `billing_email`;
alter table `myorders` add column `shipping_last_name` varchar(50) NOT NULL DEFAULT '' after `shipping_first_name`;
alter table `myorders` add column `shipping_phone` varchar(50) NOT NULL DEFAULT '' after `shipping_zip`;
alter table `myorders` add column `shipping_email` varchar(50) NOT NULL DEFAULT '' after `shipping_phone`;

alter table `myorder_items` add column `store_id` int(11) NOT NULL DEFAULT '0' after `order_id`;

alter table `stores` add column `allow_resell` tinyint not null default '0' after `excluded_in_search`;

update version set version=31;
