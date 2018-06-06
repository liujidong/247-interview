import "utils"

$packages = [mysql, mysql-server]
install_package { $packages: }

$service = [mysqld]
run_service { $service: }
