alter table `products` add column `free_shipping` tinyint not null default '0' after `shipping`;

CREATE TABLE `pinned_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_account_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_board_id` int(11) NOT NULL DEFAULT '0',
  `external_pinterest_pin_id` varchar(50) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_pinterest` (`product_id`, `pinterest_account_id`, `pinterest_board_id`),
  UNIQUE KEY `external_pinterest_pin_id` (`external_pinterest_pin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `scheduled_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint not null default '0',
  `created` datetime not null DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_id` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

update version set version=8;