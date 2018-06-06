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

update version set version=21;
