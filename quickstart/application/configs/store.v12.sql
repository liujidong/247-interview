alter table order_items add column `coupon_id` int(11) NOT NULL DEFAULT '0' after `commission`;

alter table order_items add column `coupon_price_percentage_off` double NOT NULL DEFAULT '0' after `coupon_id`;

alter table order_items add column `coupon_free_shipping` tinyint not null default '0' after `coupon_price_percentage_off`;

update version set version=12;