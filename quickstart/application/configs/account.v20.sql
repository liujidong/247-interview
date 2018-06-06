
alter table `stores` add column `converted_logo` varchar(255) not null default '' after `logo`;

CREATE TABLE `search_product_converted_pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search_product_id` int(11) NOT NULL default '0',
  `picture_id` int(11) NOT NULL default '0',
  `picture_status` tinyint not null default '0',
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

update version set version=20;