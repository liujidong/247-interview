alter table categories add column `description` varchar(255) NOT NULL DEFAULT '' after `category`;

update version set version=7;