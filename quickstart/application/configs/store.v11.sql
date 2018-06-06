alter table order_items add column `commission` double NOT NULL DEFAULT '0' after `shipping`;

update version set version=11;