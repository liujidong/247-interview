#!/bin/bash

sed -i 's#SELINUX=enforcing#SELINUX=disabled#' /etc/selinux/config

puppet apply db.pp

echo "use mysql;update user set host = '%'where user = 'root' and host='127.0.0.1';flush privileges;" | mysql -uroot

service mysqld restart
chkconfig mysqld on

rm -f /etc/localtime
ln -sf /usr/share/zoneinfo/US/Pacific /etc/localtime

