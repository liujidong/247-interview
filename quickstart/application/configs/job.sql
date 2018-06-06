

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `type` tinyint not null default '0',
  `data` text not null default '',
  `priority` tinyint not null default '0',
  `hash1` varchar(2047) not null default '',
  `uniqid` varchar(50) not null default '',
  `created` datetime not null DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uniqid` (`uniqid`),
  KEY `hash1` (`hash1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `job_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint not null default '0',
  `type` tinyint not null default '0',
  `max_instances` tinyint not null default '1',
  `sleep` int not null default '0',
  `created` datetime not null DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint not null default '0',
  `class` varchar(50) not null default '',
  `method` varchar(50) not null default '',
  `file` varchar(255) not null default '',
  `var1` varchar(255) not null default '',
  `var2` varchar(255) not null default '',
  `var3` varchar(255) not null default '',
  `process_id` int not null default '0',
  `hostname` varchar(50) not null default '',
  `msg` text not null default '',
  `created` datetime not null DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `version` (
  `version` int(11) not null default '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
insert into `version` values('1');
insert into job_configs(type, max_instances, sleep, created) values(1, 5, 1, now());
insert into job_configs(type, max_instances, sleep, created) values(2, 10, 1, now());
insert into job_configs(type, max_instances, sleep, created) values(3, 10, 1, now());
insert into job_configs(type, max_instances, sleep, created) values(4, 5, 1, now());
insert into job_configs(type, max_instances, sleep, created) values(5, 10, 1, now());
insert into job_configs(type, max_instances, sleep, created) values(6, 5, 1, now());
insert into job_configs(type, max_instances, sleep, created) values(7, 5, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(9, 5, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(10, 5, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(11, 5, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(12, 5, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(13, 10, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(14, 10, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(15, 10, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(16, 10, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(17, 10, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(18, 10, 5, now());
insert into job_configs(type, max_instances, sleep, created) values(19, 10, 5, now());

