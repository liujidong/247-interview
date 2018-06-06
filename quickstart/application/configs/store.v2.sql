alter table products drop index `pinterest_pin_id`;

update version set version=2;