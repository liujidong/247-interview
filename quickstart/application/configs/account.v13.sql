
CREATE TABLE `abtests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `num_shards` int(11) not null default '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

update version set version=13;