
class aws() {
    $aws_dl = "https://raw.github.com/timkay/aws/master/aws"
    
    exec { 'download_aws': 
        command => "curl $aws_dl -o aws", 
        cwd => '/usr/bin', 
        creates => "/usr/bin/aws", 
        path => ['/usr/bin/', '/bin/'],
    }

    exec { 'chmod_aws': 
        command => "chmod 755 /usr/bin/aws", 
        cwd => "/usr/bin",
        require => Exec['download_aws'],
        path => ['/usr/bin/', '/bin/'],
    }

    exec { 'cp_awssecret':
        command => "cp -n /var/www/html/pincommerce/quickstart/application/configs/puppet/templates/.awssecret /root/.awssecret",
        path => [ '/usr/bin/', '/bin/', '/opt/redis/bin' ],
    }  

    exec { 'chmod_awssecret': 
        command => "chmod 600 .awssecret", 
        cwd => "/root/",
        require => Exec['cp_awssecret'],
        path => ['/usr/bin/', '/bin/'],
    }

}
include aws