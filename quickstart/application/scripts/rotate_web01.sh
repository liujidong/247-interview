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

for file in /var/log/httpd/pincommerce/error /var/log/httpd/pincommerce/clear_img.log /var/log/httpd/pincommerce/access /tmp/paypal.log
do
        rotate "$file"
done

END_TIME=`date +"%Y-%m-%d %H:%M:%S"`
echo "Rotate code end at $END_TIME"

exit 0