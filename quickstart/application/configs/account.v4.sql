alter table `store_products` add column `category_description` varchar(255) NOT NULL DEFAULT '' after `category`;

update version set version=4;