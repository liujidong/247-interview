#!/bin/bash

sed -i 's#SELINUX=enforcing#SELINUX=disabled#' /etc/selinux/config

puppet apply deamon.pp 
puppet apply pecl_install_http.pp
puppet apply zend.pp
puppet apply sphinx.pp
puppet apply aws.pp
puppet apply redis.pp

mkdir -p /var/data
cd /var/
chmod 777 data

unalias cp
cp -f /var/www/html/pincommerce/quickstart/application/configs/puppet/templates/php.ini /etc/php.ini
cp -f /var/www/html/pincommerce/quickstart/application/configs/puppet/templates/.htaccess /var/www/html/pincommerce/quickstart/public/
alias cp='cp -i'

cd /var/www/html/pincommerce
php create_sphinx_conf.php
./push_sphinx_conf
indexer --all
service searchd restart
chkconfig searchd on  

cd /usr/lib64/php/modules
chmod 755 http.so redis.so

rm -f /etc/localtime
ln -sf /usr/share/zoneinfo/US/Pacific /etc/localtime
