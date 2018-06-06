alter table products add column `commission` double NOT NULL DEFAULT '0' after `end_date`;

update version set version=10;