
/* merchants */

CREATE TABLE `merchants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* stores */

CREATE TABLE `stores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) not null default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/* merchants_stores */

CREATE TABLE `merchants_stores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) not null default '0',
  `store_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchants_stores` (`merchant_id`, `store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/* paypal_accounts */

CREATE TABLE `paypal_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/* dwolla_accounts */

CREATE TABLE `dwolla_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `access_token` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* stores_paypal_accounts */

CREATE TABLE `stores_paypal_acounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) not null default '0',
  `paypal_account_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stores_paypal_accounts` (`store_id`, `paypal_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* stores_dwolla_accounts */

CREATE TABLE `stores_dwolla_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) not null default '0',
  `dwolla_account_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stores_dwolla_accounts` (`store_id`, `dwolla_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* pinterest_accounts */

CREATE TABLE `pinterest_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` varchar(50) NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `about` text NOT NULL default '',
  `twitter_link` varchar(255) NOT NULL DEFAULT '',
  `is_following` tinyint(4) NOT NULL DEFAULT '0',
  `facebook_link` varchar(255) NOT NULL DEFAULT '',
  `image_url` varchar(255) NOT NULL DEFAULT '',
  `location` varchar(50) NOT NULL DEFAULT '',
  `full_name` varchar(50) NOT NULL DEFAULT '',
  `image_large_url` varchar(255) DEFAULT '',
  `followers` int(11) NOT NULL DEFAULT '0',
  `likes` int(11) NOT NULL DEFAULT '0',
  `followings` int(11) NOT NULL DEFAULT '0',
  `pins` int(11) NOT NULL DEFAULT '0',
  `boards` int(11) NOT NULL DEFAULT '0',
  `repins` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* pinterest_accounts_pinterest_boards */

CREATE TABLE `pinterest_accounts_pinterest_boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinterest_account_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_board_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_board` (`pinterest_account_id`, `pinterest_board_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* pinterest_boards */

CREATE TABLE `pinterest_boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` varchar(50) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `category` varchar(50) NOT NULL DEFAULT '',
  `is_collaborator` tinyint(4) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `is_following` tinyint(4) NOT NULL DEFAULT '0',
  `thumbnails` text NOT NULL DEFAULT '',
  `followers` int(11) NOT NULL DEFAULT '0',
  `pins` int(11) NOT NULL DEFAULT '0',
  `pagination` varchar(255) not null default '',
  `selected` tinyint not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* pinterest_boards_pinterest_pins */

CREATE TABLE `pinterest_boards_pinterest_pins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinterest_board_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_pin_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `board_pin` (`pinterest_board_id`, `pinterest_pin_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* pinterest_pins */

CREATE TABLE `pinterest_pins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` varchar(50) NOT NULL DEFAULT '0',
  `domain` varchar(50) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `images_mobile` varchar(255) NOT NULL DEFAULT '',
  `images_closeup` varchar(255) NOT NULL DEFAULT '',
  `images_thumbnail` varchar(255) NOT NULL DEFAULT '',
  `images_board` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT '0000-00-00 00:00:00',
  `is_repin` tinyint NOT NULL DEFAULT '0',
  `is_video` tinyint(4) NOT NULL DEFAULT '0',
  `source` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `comments` int(11) NOT NULL DEFAULT '0',
  `likes` int(11) NOT NULL DEFAULT '0',
  `repins` int(11) NOT NULL DEFAULT '0',
  `price` float NOT NULL DEFAULT '0.0', 
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* stores_pinterest_accounts */

CREATE TABLE `stores_pinterest_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `pinterest_account_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* products */

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `size` varchar(50) NOT NULL DEFAULT '',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `price` float NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* categories */

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categorie` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorie` (`categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/* products_categories */

CREATE TABLE `products_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `categorie_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categorie` (`product_id`, `categorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* stores_products */

CREATE TABLE `stores_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_product` (`store_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* pictures */

CREATE TABLE `pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT '',
  `size` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `source` varchar(50) not null default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* pinterest_pins_pictures */

CREATE TABLE `pinterest_pins_pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinterest_pin_id` int(11) NOT NULL DEFAULT '0',
  `picture_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `picture` (`picture_id`),
  Key `pin` (`pinterest_pin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* products_pictures */

CREATE TABLE `products_pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `picture_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_picture` (`product_id`, `picture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* customers */

CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

stores_customers (id, store_id, customer_id, created, updated)

/* stores_customers */

CREATE TABLE `stores_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_customer` (`store_id`, `customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/* comments */

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL DEFAULT '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* carts */

CREATE TABLE `carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL DEFAULT '',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  unique key `session_product` (`session_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;