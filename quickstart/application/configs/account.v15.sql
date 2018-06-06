

alter table `search_products` add column `featured` tinyint(4) NOT NULL DEFAULT '0' after `store_optin_salesnetwork`;

alter table `search_products` add column `score` int(11) NOT NULL DEFAULT '0' after `featured`;

update version set version=15;