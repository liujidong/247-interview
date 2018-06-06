
alter table orders add column `shipping_service_provider` varchar(50) not null default '' after `currency_code`;

alter table orders add column `tracking_number` varchar(50) not null default '' after `shipping_service_provider`;

alter table orders add column `expected_arrival_date` datetime not null default '0000-00-00 00:00:00' after `tracking_number`;

update version set version=15;