
alter table orders add column `user_id` int(11) not null default '0' after `to_address_id`;

alter table orders add column `to_email` varchar(50) not null default '' after `user_id`;

alter table orders add column `to_first_name` varchar(50) not null default '' after `to_email`;

alter table orders add column `to_last_name` varchar(50) not null default '' after `to_first_name`;


update version set version=13;