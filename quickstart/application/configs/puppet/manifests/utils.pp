Exec { path => "/bin:/usr/bin:/usr/local/bin" }

define create_user ($user_name, $key) {
    user { "$user_name":
        ensure => present,
        managehome => true,
    }
    ssh_authorized_key { "$user_name":
        ensure => present,
        user => "$user_name",
        type => ssh-rsa,
        key => "$key",
        require => User["$user_name"],
    }  
    exec { "/bin/echo $user_name ALL = NOPASSWD: ALL >>  /etc/sudoers":
        subscribe  => User["$user_name"],
    }
}

define create_git_key($user_name, $key) {
    file { "/home/$user_name/.ssh/id_rsa":
        ensure => file,
        mode => '600',
        content => "$key",
        group => "$user_name",
        owner => "$user_name",
    }
}

#useage:
#$packages = ['git', 'mysql', 'php', 'php-mysql']
#install_package { $packages:
#}
#or
#install_package { 'httpd':
#}

define install_package() {
    if ! defined(Package["${title}"]) {
        package { "${title}":
            ensure => installed,
        }  
    }
}

#useage:
#$service = ['mysqld', httpd]
#run_service { $service:
#}
#or
#run_service { 'mysqld':
#}

define run_service() {
    service { "${title}":
        ensure => running,
        enable => true,
        status => restart,
    }
}

#useage:
#$arr = [1, 2, 3]
#print{$arr: }
define print() {
   notice("The value is: '${name}'")
}


