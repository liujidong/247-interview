alter table products add column `ext_ref_id` varchar(50) not null default '' after `pinterest_pin_id`;
alter table products add column `ext_ref_url` varchar(255) not null default '' after `ext_ref_id`;
alter table products add column `brand` varchar(255) not null default '' after `ext_ref_url`;
alter table products add column `misc` text not null default '' after `brand`;
alter table products add index `pinterest_pin_id` (`pinterest_pin_id`);
alter table products add index `ext_ref_id` (`ext_ref_id`);

update version set version=6;