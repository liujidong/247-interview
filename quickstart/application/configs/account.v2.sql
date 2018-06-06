alter table stores modify column `tax` double NOT NULL DEFAULT '0';
alter table stores modify column `shipping` double NOT NULL DEFAULT '0';
alter table stores modify column `additional_shipping` double NOT NULL DEFAULT '0';

alter table pinterest_pins modify column `price` double NOT NULL DEFAULT '0';

alter table service_orders modify column `total` double NOT NULL DEFAULT '0';

update version set version=2;