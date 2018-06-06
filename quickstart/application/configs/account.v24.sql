
alter table `stores` add column `excluded_in_search` tinyint not null default '0' after `currency`;

alter table `search_products` add column `excluded_in_search` tinyint not null default '0' after `score`;

update version set version=24;