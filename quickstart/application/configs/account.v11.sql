
alter table `stores` add column `payment_solution` tinyint not null default '0' after `optin_salesnetwork`;

alter table `stores` add column `transaction_fee_waived` tinyint not null default '0' after `payment_solution`;

update stores set payment_solution=1 where optin_salesnetwork=2;

update version set version=11;