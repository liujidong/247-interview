import "utils"

#install service && ensure start
$packages = [httpd, mysql, php, php-mysql, php-xml, php-gd, php-devel, php-pear, zlib-devel, "libcurl-devel-7.19.7-26.el6_2.4.x86_64"]
install_package { $packages: }

# cant run httpd here
# start redis
# use full direct here
# install redis /usr/local
$service = [httpd]
run_service { $service: }