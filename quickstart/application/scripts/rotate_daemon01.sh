#!/bin/bash
CURRENT_TIME=` date +"%Y-%m-%d %H:%M:%S"`
echo "Rotate code begin at $CURRENT_TIME"

#rotate function
#input : file path ex  /var/log/httpd/pincommerce/account_scraper.log 
function rotate(){
    local date=`date +%Y%m%d`
    local log_name=`basename ${1}`
    local log_dir=`dirname ${1}`
    cd ${log_dir}
    gzip -c ${log_name} >${log_name}-${date}.gz
    cat /dev/null > ${log_name}
}

for file in /tmp/sendgrid.log /var/log/httpd/pincommerce/account_scraper.log /var/log/httpd/pincommerce/email_processor.log /var/log/httpd/pincommerce/generate_board_scraping_jobs.log /var/log/httpd/pincommerce/board_scraper.log /var/log/httpd/pincommerce/image_uploader.log /var/log/httpd/pincommerce/add_featured_product.log /var/log/httpd/pincommerce/import_featured_products.log /var/log/httpd/pincommerce/clear_img.log
do
        rotate "$file"
done

END_TIME=`date +"%Y-%m-%d %H:%M:%S"`
echo "Rotate code end at $END_TIME"

exit 0