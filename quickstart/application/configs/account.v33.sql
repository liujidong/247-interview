
alter table `users` add column `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' after `bank_account_number` ;
alter table `users` add column `last_activity` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' after `last_login`;

alter table `stores` add column `no_international_shipping` tinyint not null default '0' after additional_shipping;

update version set version=33;
