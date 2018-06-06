
alter table `cart_items` add column `dealer` varchar(50) NOT NULL default '' after `quantity`;
alter table `cart_items` add column `external_id` varchar(50) NOT NULL default '' after `dealer`;

alter table `carts` add column `session_id` varchar(128) NOT NULL DEFAULT '' after `status`;

update version set version=38;
