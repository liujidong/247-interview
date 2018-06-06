#run sudo yum --disableexcludes=main install kernel-headers-2.6.32-279.14.1.el6.openlogic.x86_64 first
#then run sudo yum install gcc

class redis($redis_ver = '2.6.10') {

  $redis_tar = "redis-$redis_ver.tar.gz"
  $redis_dl = "http://redis.googlecode.com/files/$redis_tar"

  if defined(Package['curl']) == false {
    package { "curl":
      ensure => "installed"
    }
    notify{"Package curl has been defined":}
  }

  if defined(Package['make']) == false {
    package { "make":
      ensure => "installed"
    }
    notify{"Package make has been defined":}
  }

  if defined(Package['gcc']) == false {
    package { "gcc":
      ensure => "installed"
    }
    notify{"Package gcc has been defined":}
  }

  exec { 'download_redis':
      command       => "curl -o $redis_tar $redis_dl"
    , cwd           => '/usr/local/'
    , creates       => "/usr/local/${redis_tar}"
    , require       => Package['curl']
    , path          => ['/usr/bin/', '/bin/']
  }

  exec { 'extract_redis':
      command       => "tar -xzf $redis_tar"
    , cwd           => "/usr/local/"
    , creates       => "/usr/local/redis-${redis_ver}"
    , require       => Exec['download_redis']
    , path          => ['/usr/bin/', '/bin/']
  }

  exec { 'install_redis':
      command       => 'make MALLOC=libc'
    , cwd           => "/usr/local/redis-${redis_ver}"
    , require       =>  Exec ['extract_redis']
    , timeout       => 0
    , path          => [ '/usr/bin/', '/bin/', '/opt/redis/bin' ]
  }

#bug here: cant use this path
  exec { 'cp_redis':
    , command       => "cp -n /var/www/html/pincommerce/quickstart/application/configs/puppet/templates/redis.so /usr/lib64/php/modules/"
    , cwd           => "/usr/local/redis-${redis_ver}"
    , require       =>  Exec ['install_redis']
    , path          => [ '/usr/bin/', '/bin/', '/opt/redis/bin' ]
  }

  exec { 'chmod 755 redis.so':
    , cwd           => "/usr/lib64/php/modules/"
    , require       =>  Exec ['cp_redis']
    , path          => [ '/usr/bin/', '/bin/', '/opt/redis/bin' ]
  }

  exec { 'echo extension=redis.so >> /etc/php.ini':
    , cwd           => "/usr/local/redis-${redis_ver}"
    , require       =>  Exec ['install_redis']
    , path          => [ '/usr/bin/', '/bin/', '/opt/redis/bin' ]
  }

  exec { 'echo 0 > /selinux/enforce':
    , path          => [ '/usr/bin/', '/bin/', '/opt/redis/bin' ]
  }

  exec { 'cp_redis_cli':
    , command       => "cp -f /usr/local/redis-2.6.10/src/redis-cli /usr/bin/redis"
    , cwd           => "/usr/local/redis-${redis_ver}"
    , require       =>  Exec ['install_redis']
    , path          => [ '/usr/bin/', '/bin/', '/opt/redis/bin' ]
  }
}

include redis