alter table products modify column `price` double NOT NULL DEFAULT '0';
alter table products modify column `shipping` double NOT NULL DEFAULT '0';

alter table reviews modify column `score` double NOT NULL DEFAULT '0';

alter table orders modify column `total` double NOT NULL DEFAULT '0';
alter table orders modify column `price` double NOT NULL DEFAULT '0';
alter table orders modify column `tax` double NOT NULL DEFAULT '0';
alter table orders modify column `shipping` double NOT NULL DEFAULT '0';

alter table order_items modify column `price` double NOT NULL DEFAULT '0';
alter table order_items modify column `shipping` double NOT NULL DEFAULT '0';

update version set version=4;