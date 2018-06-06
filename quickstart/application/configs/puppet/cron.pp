# creat cron file example
file { '/etc/crontab'
    ensure => file,
    content => '0 */1 * * * root /var/www/html/pincommerce/quickstart/application/scripts/clear_img.sh >> /var/log/httpd/pincommerce/clear_img.log
                #1 0 * * * root  /var/www/html/pincommerce/quickstart/application/scripts/rotate_web01.sh >> /var/log/httpd/pincommerce/rotate.log
                #*/1 * * * * root php /var/www/html/pincommerce/quickstart/application/scripts/add_search_product.php >> /var/log/httpd/pincommerce/add_search_product.log',
}