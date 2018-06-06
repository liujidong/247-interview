#!/bin/bash
#run this under root admin
#give a  argument $1=web database demon or all
#please put id_rsa install.sh env.conf at the some dir 
#chmod 755 install.sh before run this script
#please pull code into /var/www/html/pincommerce 

WEBROOT="/var/www/html/pincommerce"

if [ $(id -u) != "0" ]; then
    echo "Error: You must be root to run this script, please use root to install lnmp"
    exit 1
fi

if [ $# -ne 1 ]
then 
    echo " one argument expected.please specify  web  daemon or databaseaccount , databasestore , databasejob or all"
    exit 1
fi

clear
echo "========================================================================="
echo "LAMP Setup Script for CentOS VPS  Written by wyixin"
echo "========================================================================="
echo "A tool to auto-compile & install Apache+MySQL+PHP on Linux "
echo "========================================================================="



trans=`tr -d '\r' <env.conf >env2.conf`
source env2.conf


cur_dir=`pwd`

cat > /tmp/mysql_sec_script_account<<EOF
use mysql;
update user set host = '%'where user = 'root' and host='127.0.0.1';
flush privileges;
DROP DATABASE IF EXISTS account;
create database account;
use account;
source  /var/www/html/pincommerce/quickstart/application/configs/account.sql;
EOF
cat > /tmp/mysql_sec_script_store<<EOF
use mysql;
update user set host = '%'where user = 'root' and host='127.0.0.1';
flush privileges;
DROP DATABASE IF EXISTS store;
create database store;
use store;
source  /var/www/html/pincommerce/quickstart/application/configs/store.sql;
EOF
cat > /tmp/mysql_sec_script_job<<EOF
use mysql;
update user set host = '%'where user = 'root' and host='127.0.0.1';
flush privileges;
DROP DATABASE IF EXISTS job;
create database job;
use job;
source  /var/www/html/pincommerce/quickstart/application/configs/job.sql;
EOF

cat > .htaccess<<EOF
SetEnv APPLICATION_ENV production

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [NC,L]
RewriteRule ^.*$ index.php [NC,L]
EOF

#/home/cloud/pincommerce/quickstart/application/configs/account.sql
#/var/www/html/pincommerce/quickstart/application/configs/account.sql

rm_file(){
    rm -f /tmp/mysql_sec_script*
    rm -f /usr/local/ZendFramework-1.11.11.zip
    rm -f ${cur_dir}/env2.conf
    rm -f ${cur_dir}/.htaccess
    echo "--------------file is remove ----------"
}


# check if a package is install , if not then install it 
is_packages_installed(){
    if [ $# -ne 1 ]
    then 
        echo " one argument expected.please give a package name"
        exit 1
    fi
    local package
    package=`yum list installed | grep "$1"`
    if [ -n "$package" ]; then
        echo "$1  [found]"
    else
        echo "Error: $1 not found!!!"
        echo "Now install $1"
        yum install $1
    fi
}

run_service(){
    
    if [ $# -ne 1 ]
    then 
        echo " one argument expected. please give a service name"
        exit 1
    fi
    local rc
    #rc=`ps ax | grep $1 | wc -l`
    #if [ $rc -eq 0 ]; then
    #if ps ax | grep -v grep | grep $1 >/dev/null
    #then
    #   service $1 start
    #    echo "now service $1 is runing...."
    #fi
    #echo "service $1 is already runing...."
    service $1 restart >/dev/null
    chkconfig $1 on
    echo "service $1 is runing...."
}

#timezone and auto sync time 
check_timezone(){
    
    rm -f /etc/localtime
    ln -sf /usr/share/zoneinfo/US/Pacific /etc/localtime
    
    is_packages_installed ntp

    sed -i 's#server 0.pool.ntp.org#ntp0.peakwebhosting.com#' /etc/ntp.conf
    sed -i 's#server 1.pool.ntp.org#ntp1.peakwebhosting.com#' /etc/ntp.conf
    sed -i 's#server 2.pool.ntp.org#ntp2.peakwebhosting.com#' /etc/ntp.conf

    run_service ntpd 
    chkconfig ntpd on
}


sed_files(){
    #sed httpd.conf
    sed -i 's#DocumentRoot "/var/www/html"#DocumentRoot "/var/www/html/pincommerce/quickstart/public"#' /etc/httpd/conf/httpd.conf
    sed -i 's#<Directory "/var/www/html">#<Directory "/var/www/html/pincommerce/quickstart/public">#' /etc/httpd/conf/httpd.conf
    sed -i '320,340s#AllowOverride None#AllowOverride All#' /etc/httpd/conf/httpd.conf
    
    sed -i '$ a\<VirtualHost *:80>' /etc/httpd/conf/httpd.conf
    sed -i '$ a\ServerAdmin xxx@yahoo.com ' /etc/httpd/conf/httpd.conf
    sed -i '$ a\DocumentRoot /var/www/html/pincommerce/quickstart/public ' /etc/httpd/conf/httpd.conf
    sed -i '$ a\ServerName www.shopinterest.co ' /etc/httpd/conf/httpd.conf
    sed -i '$ a\ServerAlias *.shopinterest.com ' /etc/httpd/conf/httpd.conf
    sed -i '$ a\</VirtualHost>' /etc/httpd/conf/httpd.conf
    
    sed -i "1,49s#database.account.host.*#database.account.host ='${DB01_HOST}'#" /var/www/html/pincommerce/quickstart/application/configs/application.ini
    sed -i "1,49s#database.store.host.*#database.store.host ='${DB02_HOST}'#" /var/www/html/pincommerce/quickstart/application/configs/application.ini
    sed -i "1,49s#database.job.host.*#database.job.host ='${DB03_HOST}'#" /var/www/html/pincommerce/quickstart/application/configs/application.ini
    #sed php.ini
    sed -i 's:short_open_tag = Off:short_open_tag=On:' /etc/php.ini

}

#check timezone
check_timezone



install_database_server(){

    for x in mysql mysql-server git
    do
        is_packages_installed "$x"
    done
    
    check_project

    local sql_file_dir
    echo $dbname
    

    case "${dbname}" in
        account.sql)
            sql_file_dir=/tmp/mysql_sec_script_account
            ;;
        store.sql)
            sql_file_dir=/tmp/mysql_sec_script_store
            ;;
        job.sql)
            sql_file_dir=/tmp/mysql_sec_script_job
            ;;
    esac
    
    run_service mysqld
    echo "${sql_file_dir}"
    mysql -u root  < ${sql_file_dir}

    rm_file
    
}

set_zend_framework(){
    cd /usr/local || {
        echo "Cannot change to necessary directory. "
        exit 1
    }
    rm -rf zend
    rm -rf ZendFramework*
    wget https://s3.amazonaws.com/shopinterest_public/ZendFramework-1.11.11.zip
    unzip ZendFramework-1.11.11.zip > /dev/null
    mv ZendFramework-1.11.11 zend
    ln -s /usr/local/zend/library/Zend  /var/www/html/pincommerce/quickstart/library/Zend 
}

check_project(){
    if [ -d /var/www/html/pincommerce/ ]; then
        echo "pincommerce code [found]"
    else
        echo "Error: pincommerce code not found!!!"
        exit 1
    fi
}



#daemon server must installed  mysql mysql-client php php-mysql php-xml git before runing this script
install_daemon_server(){
    
    #check package installed
    for x in mysql php php-mysql php-xml git crontabs
    do
        is_packages_installed "$x"
    done
    
    #check project code is already there
    check_project
    
    sed_files
    #set ZendFramework
    set_zend_framework
    
    #set cornjob
    run_service crond
    push_cron
    
    cd ${cur_dir} || {
        echo "Cannot change to necessary directory. "
        exit 1
    }
    cp .htaccess /var/www/html/pincommerce/quickstart/public/.htaccess
    #remove the temp file
    rm_file

    echo "DAEMON SERVER IS SETUP"
    exit 0
}
#---------some ssh link code---------------------
#ssh -i/home/cloud/.ssh/dev.pem 10.8.0.249
#------------end of ssh link code------------

#---------set up git response and pull code from git-------
#git config --global user.email "xxx@gmail.com"
# git config --global user.name "wyixin"
#
#cd /var/www/html/
#mkdir pincommerce
#cd pincommerce
#git init
#git pull git@github.com:liangdev/salesnet.git
#-----------end of git setup


#---------------about httpd-----------
#cp /var/www/html/pincommerce/quickstart/application/scripts/httpd.conf /etc/httpd/conf/httpd.conf
#-------------end of httpd-------------
#/var/log/httpd/error_log|access_log 


#-----------wget zend framework--------------------
#wget http://framework.zend.com/releases/ZendFramework-1.11.12/ZendFramework-1.11.12-minimal.tar.gz
#tar zxvf or
#tar -xf ZendFramework-1.11.12-minimal.tar.gz
#cd /usr/local/ZendFramework-1.11.12-minimal/library
#cp -rf  Zend /usr/local/Zend
#ln -s /usr/local/Zend  /var/www/html/pincommerce/quickstart/application/library/Zend
#---------end of setup zend framework



#web server must installed httpd mysql mysql-client php php-mysql php-xml git before runing this script
install_web_server(){
    
    #check project code is already there
    check_project
    
    #check package installed
    for x in httpd mysql php php-mysql php-xml php-gd git
    do
        is_packages_installed "$x"
    done
   
    sed_files

    #set ZendFramework
    set_zend_framework
    
    cd ${cur_dir} || {
        echo "Cannot change to necessary directory. "
        exit 1
    }
    cp .htaccess /var/www/html/pincommerce/quickstart/public/.htaccess
    #start service 
    run_service httpd 
    rm_file

    echo "WEB SERVER IS SETUP"
    exit 0
}

push_cron(){
    mkdir  /var/log/httpd/pincommerce
    cd /var/www/html/pincommerce || {
        echo "Cannot change to necessary directory ."
        exit 1
    }
    if [ -x pushcron_daemon01 ]; then
         ./pushcron_daemon01
    else
        cd /var/www/html/pincommerce/quickstart/application/configs/crontab || {
            echo "Cannot change to necessary directory ."
            exit 1
        }
        cp daemon01 /etc/crontab
    fi
}

install_all(){

    #check_project

    for x in httpd mysql mysql-server php php-mysql php-xml php-gd git crontabs
    do
        is_packages_installed "$x"
    done
    
    sed -i 's#DocumentRoot "/var/www/html"#DocumentRoot "/var/www/html/pincommerce/quickstart/public"#' /etc/httpd/conf/httpd.conf
    sed -i 's#<Directory "/var/www/html">#<Directory "/var/www/html/pincommerce/quickstart/public">#' /etc/httpd/conf/httpd.conf
    sed -i '320,340s#AllowOverride None#AllowOverride All#' /etc/httpd/conf/httpd.conf

    sed -i "1,49s#database.account.host.*#database.account.host ='127.0.0.1'#" /var/www/html/pincommerce/quickstart/application/configs/application.ini
    sed -i "1,49s#database.store.host.*#database.store.host ='127.0.0.1'#" /var/www/html/pincommerce/quickstart/application/configs/application.ini
    sed -i "1,49s#database.job.host.*#database.job.host ='127.0.0.1'#" /var/www/html/pincommerce/quickstart/application/configs/application.ini
    #sed php.ini
    sed -i 's:short_open_tag = Off:short_open_tag=On:' /etc/php.ini
    
    set_zend_framework
    
    run_service mysqld
    #database 
    for sql in /tmp/mysql_sec_script_account /tmp/mysql_sec_script_store /tmp/mysql_sec_script_job
    do
        echo "${sql}"
        mysql -u root  < ${sql} 
    done
    #service restart
    for service in httpd mysqld crond 
    do
        echo "${service}"
        run_service ${service}
    done
    #daemon
    push_cron
    
    cd ${cur_dir} || {
        echo "Cannot change to necessary directory. "
        exit 1
    }
    cp .htaccess /var/www/html/pincommerce/quickstart/public/.htaccess
    rm_file
    echo "SHOPINTEREST IS SETUP"
    exit 0
}
case "${1}" in
    web)
        install_web_server
        ;;
    daemon)
        install_daemon_server
        ;;
    databaseaccount)
        dbname="account.sql"
        install_database_server
        ;;
    databasestore)
        dbname="store.sql"
        install_database_server
        ;;
     databasejob)
        dbname="job.sql"
        install_database_server
        ;;    
    all)
        install_all
        ;;
    *)
        echo "Please specify web demon database or all as the arg"
        exit 1
        ;;
esac
