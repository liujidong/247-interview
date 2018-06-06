
alter table pictures add column `original_url` varchar(255) NOT NULL DEFAULT '' after `url`; 

update pictures set original_url=url;

update version set version=20;