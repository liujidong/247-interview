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
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
update version set version=16;
