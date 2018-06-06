
INSERT INTO `shipping_options` (`status`, `name`, `created`) VALUES (2, 'Standard', now()) ON DUPLICATE KEY UPDATE status = 2;

ALTER TABLE `fields` DROP COLUMN `type`;
ALTER TABLE `fields` ADD COLUMN `quantity` int(11) NOT NULL DEFAULT '0' after `name`;

drop table `field_values`;

update products set commission=3 where commission<3;

DROP TABLE `products_fields`;
ALTER TABLE `fields` ADD COLUMN `product_id` int(11) NOT NULL DEFAULT '0' after `id`;

update version set version=23;
