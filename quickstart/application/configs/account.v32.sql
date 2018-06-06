
-- drop shipping fileds of credit_cards
ALTER TABLE credit_cards DROP COLUMN `shipping_first_name`;
ALTER TABLE credit_cards DROP COLUMN `shipping_last_name`;
ALTER TABLE credit_cards DROP COLUMN `shipping_addr1`;
ALTER TABLE credit_cards DROP COLUMN `shipping_addr2`;
ALTER TABLE credit_cards DROP COLUMN `shipping_city`;
ALTER TABLE credit_cards DROP COLUMN `shipping_state`;
ALTER TABLE credit_cards DROP COLUMN `shipping_country`;
ALTER TABLE credit_cards DROP COLUMN `shipping_zip`;
ALTER TABLE credit_cards DROP COLUMN `shipping_phone`;
ALTER TABLE credit_cards DROP COLUMN `shipping_email`;


-- drop billing fileds of myorders
ALTER TABLE myorders DROP COLUMN `to_name`;
ALTER TABLE myorders DROP COLUMN `to_first_name`;
ALTER TABLE myorders DROP COLUMN `to_last_name`;
ALTER TABLE myorders DROP COLUMN `to_email`;
ALTER TABLE myorders DROP COLUMN `billing_first_name`;
ALTER TABLE myorders DROP COLUMN `billing_last_name`;
ALTER TABLE myorders DROP COLUMN `billing_addr1`;
ALTER TABLE myorders DROP COLUMN `billing_addr2`;
ALTER TABLE myorders DROP COLUMN `billing_city`;
ALTER TABLE myorders DROP COLUMN `billing_state`;
ALTER TABLE myorders DROP COLUMN `billing_country`;
ALTER TABLE myorders DROP COLUMN `billing_zip`;
ALTER TABLE myorders DROP COLUMN `billing_phone`;
ALTER TABLE myorders DROP COLUMN `billing_email`;

-- order items
alter table `myorder_items` add column `custom_fields` text NOT NULL DEFAULT '' after `product_commission`;

-- credit_cards
ALTER TABLE credit_cards CHANGE `paypal_id` `paypal_card_id` varchar(50) NOT NULL DEFAULT '';

-- 2014.01.09
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


DROP INDEX `user_id` ON `myorders`;
ALTER TABLE myorders DROP COLUMN `cart_id`;
ALTER TABLE myorders DROP COLUMN `user_id`;
ALTER TABLE myorders DROP COLUMN `payment_account_id`;
ALTER TABLE myorders DROP COLUMN `payment_method`;
ALTER TABLE myorders DROP COLUMN `payment_info`;
ALTER TABLE myorders DROP COLUMN `shipping_first_name`;
ALTER TABLE myorders DROP COLUMN `shipping_last_name`;
ALTER TABLE myorders DROP COLUMN `shipping_addr1`;
ALTER TABLE myorders DROP COLUMN `shipping_addr2`;
ALTER TABLE myorders DROP COLUMN `shipping_city`;
ALTER TABLE myorders DROP COLUMN `shipping_state`;
ALTER TABLE myorders DROP COLUMN `shipping_country`;
ALTER TABLE myorders DROP COLUMN `shipping_zip`;
ALTER TABLE myorders DROP COLUMN `shipping_phone`;
ALTER TABLE myorders DROP COLUMN `shipping_email`;
ALTER TABLE myorders DROP COLUMN `currency_code`;
ALTER TABLE myorders DROP COLUMN `expected_arrival_date`;

alter table `myorders` add column `payment_status` tinyint not null default '0' after `status`;
alter table `myorders` add column `myorder_group_id` int(11) NOT NULL DEFAULT '0' after `payment_status`;
alter table `myorders` add column `store_id` int(11) NOT NULL DEFAULT '0' after `myorder_group_id`;
alter table `myorders` add column `shipping_option_id` int(11) NOT NULL DEFAULT '0' after `shipping`;
alter table `myorders` add column `shipping_destination_id` int(11) NOT NULL DEFAULT '0' after `shipping_option_id`;
alter table `myorders` add column `expected_arrival_date` varchar(50) not null default '' after `tracking_number`;
alter table `myorders` add column `shipping_date` datetime DEFAULT '0000-00-00 00:00:00' after `expected_arrival_date`;
CREATE INDEX `myorder_group_id` ON `myorders` (`myorder_group_id`);
CREATE INDEX `store_id` ON `myorders` (`store_id`);

DROP INDEX `product_id` ON `myorder_items`;
CREATE INDEX `store_product_id` ON `myorder_items` (`product_id`, `store_id`);

alter table `credit_cards` add column `expire_date` varchar(8) NOT NULL default '0000' after `card_number`;

alter table `users` add column `aid` varchar(50) NOT NULL default '' after `associate_id`;

update users u set u.aid = (select aid from associates a where a.id = u.associate_id) where u.associate_id !=0;

update users u set u.aid = concat('assoc',u.id) where u.associate_id = 0;

alter table `users` add column `addr1` varchar(255) not null default '' after `shopper_id`;
alter table `users` add column `addr2` varchar(255) not null default '' after `addr1`;
alter table `users` add column `city` varchar(50) not null default '' after `addr2`;
alter table `users` add column `state` varchar(50) not null default '' after `city`;
alter table `users` add column `country` varchar(50) not null default '' after `state`;
alter table `users` add column `zip` varchar(50) not null default '' after `country`;

update
users u join users_addresses ua on (u.id=ua.user_id)
join addresses a on (ua.address_id=a.id)
set
u.addr1 = a.addr1,
u.addr2 = a.addr2,
u.city = a.city,
u.state = a.state,
u.country = a.country,
u.zip = a.zip;

alter table `stores` add column `tags` text NOT NULL default '' after `allow_resell`;

update stores s join
(
select st.store_id as store_id, group_concat(t.tag) as tags
from stores_tags st join tags t on (st.tag_id = t.id)
group by st.store_id
) stt on (s.id=stt.store_id)
set s.tags = stt.tags;

alter table `users` add column `paypal_email` varchar(50) NOT NULL DEFAULT '' after `zip`;
alter table `users` add column `bank_name` varchar(255) NOT NULL DEFAULT '' after `paypal_email`;
alter table `users` add column `bank_routing_number` varchar(50) NOT NULL DEFAULT '' after `bank_name`;
alter table `users` add column `bank_account_number` varchar(50) NOT NULL DEFAULT '' after `bank_routing_number`;

update users u join users_payment_accounts upa on (u.id=upa.user_id)
join payment_accounts pa on (pa.id=upa.payment_account_id)
join paypal_accounts pac on (pac.id=pa.paypal_account_id)
set u.paypal_email = pac.username;

alter table `credit_cards` add column `exp_month` varchar(10) NOT NULL DEFAULT '' after `expire_date`;
alter table `credit_cards` add column `exp_year` varchar(10) NOT NULL DEFAULT '' after `exp_month`;

alter table `credit_cards` drop column `expire_date`;
alter table `credit_cards` drop column `billing_phone`;
alter table `credit_cards` drop column `billing_email`;

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
  `current_balance` double NOT NULL DEFAULT '0',
  `available_balance` double NOT NULL DEFAULT '0',
  `description` text NOT NULL DEFAULT '',
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
insert into annoucements(content, created) values('', now());  

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
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `subscr_id_txn_type` (`subscr_id`, `txn_type`),
  KEY `payer_email` (`payer_email`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table `stores` add column `subscribed` datetime DEFAULT '0000-00-00 00:00:00' after `tags`;
alter table `stores` add column `subscr_id` varchar(50) NOT NULL DEFAULT '' after `subscribed`;

alter table `credit_cards` add column `verified` tinyint not null default '0' after `valid_until`;

alter table `wallet_activities` add column `commission` double NOT NULL DEFAULT '0' after `amount`;
alter table `wallet_activities` add column `paypal_transaction_fee` double NOT NULL DEFAULT '0' after `commission`;
alter table `wallet_activities` add column `our_transaction_fee` double NOT NULL DEFAULT '0' after `paypal_transaction_fee`;

update version set version=32;
