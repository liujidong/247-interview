alter table `merchants` add column `phone_number` varchar(50) NOT NULL DEFAULT '' after `last_name`;
alter table `stores` add column `external_website` varchar(255) NOT NULL DEFAULT '' after `additional_shipping`;
alter table `stores` add column `description` text not null default '' after `name`;
alter table `stores` add column `return_policy` text not null default '' after `description`;

update version set version=5;