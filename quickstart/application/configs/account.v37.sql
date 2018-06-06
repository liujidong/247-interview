
alter table `mycoupons` add column `is_deal` tinyint(4) not null default '0' after `is_sale`;
alter table `mycoupons` add column `operator` varchar(20) NOT NULL default 'merchant' after `is_deal`;

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

update version set version=37;
