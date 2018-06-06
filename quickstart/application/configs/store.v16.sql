
alter table pictures add column `name` varchar(255) NOT NULL DEFAULT '' after `status`; 

alter table pictures add column `description` text not null default '' after `name`; 

alter table pictures add column `width` int(11) not null default '0' after `size`; 

alter table pictures add column `height` int(11) not null default '0' after `width`; 

alter table pictures add column `order` tinyint not null default '0' after `height`;

update version set version=16;