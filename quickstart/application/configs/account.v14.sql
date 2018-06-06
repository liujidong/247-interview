
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

update version set version=14;