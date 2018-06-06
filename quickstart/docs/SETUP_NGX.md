Setup pincommerce with NGINX and php-fpm
=========================================

## Application Setup

* add a new config section in application.ini(called development_dev_nginx, for example)
* setup databases, redis, phpredis extension ...

## VirtualHost Config in NGINX

```
    server {
        listen       80;
        server_name  *.shopin.dev;
        location / {
            root /path/to/pincommerce/quickstart/public;
            if (-f $request_filename) { break;}
            rewrite (.*) /index.php last;
       }

       location ~ (.*)\.php {
            root /path/to/pincommerce/quickstart/public;
            fastcgi_param SCRIPT_FILENAME $document_root/index.php;
            fastcgi_pass  127.0.0.1:9000;
            include       fastcgi_params;
        }
    }

```

## php-fpm Config

```
; fcgi listen address and port
listen = 127.0.0.1:9000

; application env setup
env[APPLICATION_ENV] = development_dev_nginx

```

## Other Setting

* Although we have set the `APPLICATION_ENV` environment variable in `php-fpm.conf`,
  we should set it in `public/.htaccess`, because the job scripts will read that
  environment variable from there.

## Start 

* start nginx
* start php-fpm 
* done
