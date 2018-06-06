

alter table `payment_items` add column `currency_code` varchar(50) not null default 'USD' after `amt`;

alter table `payments` add column `currency_code` varchar(50) not null default 'USD' after `amt`;

update version set version=29;
