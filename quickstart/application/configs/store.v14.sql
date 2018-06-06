

alter table scheduled_jobs add column `status` tinyint not null default '0' after `id`;


update version set version=14;