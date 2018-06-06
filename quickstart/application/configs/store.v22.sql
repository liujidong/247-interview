
alter table `shipping_destinations` add column `name` varchar(50) NOT NULL DEFAULT '' after `shipping_option_id`;

alter table `shipping_destinations` change column `from` `fromdays` tinyint not null default '0';

alter table `shipping_destinations` change column `to` `todays` tinyint not null default '0';


alter table `products` add column `resell` tinyint not null default '0' after `global_category_id`;

alter table `products` add column `purchase_url` text not null default '' after `resell`;

alter table `products` add column `featured` tinyint not null default '0' after `purchase_url`;

alter table `products` add column `featured_score` int(11) NOT NULL DEFAULT '0' after `featured`;

update version set version=22;
