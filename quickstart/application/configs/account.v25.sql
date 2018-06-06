
alter table `global_categories` add column `rank` int not null default '0' after `name`;

update `global_categories` set `rank` = `id` where `id` > 0;

update version set version=25;
