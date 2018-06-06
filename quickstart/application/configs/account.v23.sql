
alter table `search_product_converted_pictures` add column `picture_order` tinyint not null default '0' after `picture_status`;

alter table `search_products` add column `global_category_id` int(11) NOT NULL DEFAULT '0' after `product_commission`;

update version set version=23;