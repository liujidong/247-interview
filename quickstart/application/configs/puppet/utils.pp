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

define install_git() {
    package { 'git':
        ensure => installed,
    }
}

#use array to execute multi params
#$packages = ['git', 'mysql'];

define install_package($package) {
    if ! defined(Package["$package"]) {
        package { "$package":
            ensure => installed,
        }  
    }
}

define run_service($service) {
    service { "$service":
        ensure => running,
        enable => true,
    }
}






