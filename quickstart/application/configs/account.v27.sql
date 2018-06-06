
CREATE TABLE `my_coupons` (
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
  `created` datetime not null DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

update version set version=27;
