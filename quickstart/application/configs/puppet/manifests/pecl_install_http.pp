#run this file after install php
Exec { path => "/bin:/usr/bin:/usr/local/bin" }
class pecl_install_http() {

    if ! defined(Package["php-pear"]) {
        package { 'php-pear' :
            ensure => installed,   
            name => 'php-pear',
        }        
    }
    exec { "install-pecl-http":
        command => "pecl install pecl_http",
        require => Package["php-pear"],
    }
    exec { "add-php-extension":
        command => "echo extension=http.so >> /etc/php.ini",
        require => Exec["install-pecl-http"],
        notify  => Service["httpd"],        
    }

    if ! defined(Service["httpd"]) {
        service { 'httpd':
            ensure => running,              
            status => restart,
            name => 'httpd',
        }    
    }
}

include pecl_install_http