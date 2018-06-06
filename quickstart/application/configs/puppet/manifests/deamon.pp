import "utils"

#install service && ensure start
$packages = [mysql, php, php-mysql, php-xml, php-gd, php-devel, php-pear, zlib-devel, crontabs, "libcurl-devel-7.19.7-26.el6_2.4.x86_64"]
install_package { $packages: }

$service = [crond]
run_service { $service: }