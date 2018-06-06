class zend($zend_ver = '1.11.11', $target = '/var/www/html/pincommerce/quickstart/library/Zend') {
  
    $zend_tar = "ZendFramework-$zend_ver.zip"
    $zend_dl = "https://s3.amazonaws.com/shopinterest_public/$zend_tar"

    if defined(Package['curl']) == false {
        package { "curl":
          ensure => "installed",
        }
        notify{"Package curl has been defined":}
    }
    exec { 'download_zend': 
        command => "curl -o $zend_tar $zend_dl",
        cwd => '/usr/local/',
        creates => "/usr/local/${zend_tar}",
        path => ['/usr/bin/', '/bin/'],
        require => Package['curl'],
    }
    exec { 'extract_zend': 
        command => "unzip ${zend_tar}",
        cwd => '/usr/local/',
        creates => "/usr/local/ZendFramework-$zend_ver",
        path => ['/usr/bin/', '/bin/'],
        require => Exec['download_zend'],      
    } 
    exec { 'create_ln': 
        command => "ln -s /usr/local/ZendFramework-$zend_ver/library/Zend $target",
        cwd => '/usr/local/',
        path => ['/usr/bin/', '/bin/'],
        require => Exec['download_zend'],      
    }           
}

include zend