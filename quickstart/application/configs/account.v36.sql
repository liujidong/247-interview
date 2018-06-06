
alter table `email_templates` add column `header` varchar(50) NOT NULL DEFAULT '' after `type`;
alter table `email_templates` add column `footer` varchar(50) NOT NULL DEFAULT '' after `header`;

update version set version=36;
