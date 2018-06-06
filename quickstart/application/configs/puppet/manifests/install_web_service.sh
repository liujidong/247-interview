#!/bin/bash

sed -i 's#SELINUX=enforcing#SELINUX=disabled#' /etc/selinux/config

puppet apply web.pp
puppet apply pecl_install_http.pp
puppet apply redis.pp
puppet apply zend.pp

unalias cp

if[ ! -f /etc/init.d/redis ]; then
    cp -f /var/www/html/pincommerce/build/redis /etc/init.d/redis
fi

service redisd start
chkconfig --add redisd
chkconfig redis on

cp -f /var/www/html/pincommerce/quickstart/application/configs/puppet/templates/php.ini /etc/php.ini
cp -f /var/www/html/pincommerce/quickstart/application/configs/puppet/templates/httpd.conf /etc/httpd/conf/httpd.conf
cp -f /var/www/html/pincommerce/quickstart/application/configs/puppet/templates/.htaccess /var/www/html/pincommerce/quickstart/public/
alias cp='cp -i'

cd /usr/lib64/php/modules
chmod 755 redis.so http.so

service httpd restart
chkconfig httpd on

rm -f /etc/localtime
ln -sf /usr/share/zoneinfo/US/Pacific /etc/localtime
