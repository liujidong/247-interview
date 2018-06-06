
alter table `my_coupons` rename `mycoupons`;

-- a user can own multiple carts
-- a cart can be owned by several users (support shared cart)
CREATE TABLE `users_carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) not null default '0',
  `cart_id` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_cart` (`user_id`, `cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- aid applies to all the products in the cart
-- coupon code can be applied to all the products or any product in the cart
-- a cart is not a transaction contract, so we only need to store the id info about the product
CREATE TABLE `carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `aid` varchar(50) NOT NULL DEFAULT '',
  `coupon_code` varchar(50) NOT NULL default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `cart_id` int(11) not null default '0',
  `store_id` int(11) not null default '0',
  `product_id` int(11) not null default '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `custom_fields` text NOT NULL default '',
  `coupon_code` varchar(50) NOT NULL default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `store_product` (`store_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- a cart can be fullfilled by multiple orders (split pay)
-- an order is a contract between the seller and the buyer
-- so we need to store the information of products purchased.
-- an order can have all the items or only partial items in 
-- the cart.
CREATE TABLE `myorders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `cart_id` int(11) not null default '0',
  `aid` varchar(50) NOT NULL DEFAULT '',
  `coupon_code` varchar(50) NOT NULL default '',
  `total` double NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `tax` double NOT NULL DEFAULT '0',
  `shipping` double NOT NULL DEFAULT '0',
  `user_id` int(11) not null default '0',
  `to_name` varchar(50) not null default '',
  `to_first_name` varchar(50) not null default '',
  `to_last_name` varchar(50) not null default '',
  `billing_addr1` varchar(255) not null default '',
  `billing_addr2` varchar(255) not null default '',
  `billing_city` varchar(50) not null default '',
  `billing_state` varchar(50) not null default '',
  `billing_country` varchar(50) not null default '',
  `billing_zip` varchar(50) not null default '',  
  `shipping_addr1` varchar(255) not null default '',
  `shipping_addr2` varchar(255) not null default '',
  `shipping_city` varchar(50) not null default '',
  `shipping_state` varchar(50) not null default '',
  `shipping_country` varchar(50) not null default '',
  `shipping_zip` varchar(50) not null default '',  
  `to_email` varchar(50) not null default '',  
  `currency_code` varchar(50) not null default '',
  `shipping_service_provider` varchar(50) not null default '',
  `tracking_number` varchar(50) not null default '',
  `expected_arrival_date` datetime DEFAULT '0000-00-00 00:00:00',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), 
  key `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- product info affecting to the order
CREATE TABLE `myorder_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_description` text not null default '',
  `product_quantity` int(11) NOT NULL DEFAULT '0',
  `product_price` double NOT NULL DEFAULT '0',
  `product_shipping` double NOT NULL DEFAULT '0',
  `product_commission` double NOT NULL DEFAULT '0',
  `coupon_code` varchar(50) NOT NULL default '',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  key `order_id` (`order_id`),
  key `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

update version set version=28;