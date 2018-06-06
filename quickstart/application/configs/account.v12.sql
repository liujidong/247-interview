
alter table `associates` add column `external_website_name` varchar(255) NOT NULL DEFAULT '' after `external_website`;

alter table `associates` add column `external_website_content` varchar(255) NOT NULL DEFAULT '' after `external_website_name`;

alter table `associates` add column `external_website_description` text NOT NULL DEFAULT '' after `external_website_content`;

alter table `associates` add column `external_website_monthly_unique_visitors` int(11) NOT NULL DEFAULT '0' after `external_website_description`;

alter table `associates` add column `marketing_channel` varchar(255) NOT NULL DEFAULT '' after `external_website_monthly_unique_visitors`;

update version set version=12;