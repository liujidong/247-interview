#install httpd, manage config file

package {'httpd':
    ensure => installed,
    before => File['httpd.conf']
}

file { 'httpd.conf':
    ensure => file,
    mode   => 600,
    content => template("httpd.pp"),
}

service { 'httpd':
    ensure     => running,
    enable     => true,
    subscribe  => File['httpd.conf'],
}