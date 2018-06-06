Exec { path => "/bin:/usr/bin:/usr/local/bin" }
class sphinx() {
    exec { "install_sphinx":
        command => "rpm -Uhv http://sphinxsearch.com/files/sphinx-2.0.4-1.rhel6.x86_64.rpm",     
    }    
}

include sphinx