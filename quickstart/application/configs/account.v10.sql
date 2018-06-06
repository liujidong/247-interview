
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `type` tinyint not null default '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `fb_user_id` int(11) not null default '0',
  `twitter_user_id` int(11) not null default '0',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `birth_day` varchar(10) NOT NULL DEFAULT '',
  `birth_month` varchar(10) NOT NULL DEFAULT '',
  `birth_year` varchar(10) NOT NULL DEFAULT '',
  `gender` varchar(10) NOT NULL DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `merchant_id` int(11) not null default '0',
  `associate_id` int(11) not null default '0',
  `shopper_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payment_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `paypal_account_id` int(11) not null default '0',
  `bank_account_id` int(11) not null default '0',
  `credit_card_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_payment_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) not null default '0',
  `payment_account_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_payment` (`user_id`, `payment_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) not null default '0',
  `address_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_addresses` (`user_id`, `address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

update version set version=10;