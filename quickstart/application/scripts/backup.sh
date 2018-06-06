#!/bin/bash

if [ $(id -u) != "0" ]; then
    echo "Error: You must be root to run this script"
    exit 1
fi
CURRENT_TIME=`date +"%Y-%m-%d %H:%M:%S"`
echo "Backup code begin at $CURRENT_TIME"

trans=`tr -d '\r' <env.conf >env2.conf`
source env2.conf
cur_dir=`pwd`
SITE="pincommerce"
DBUSER="root"
DBPWD=""
DBHOST="localhost"
OUTPUTDIR=/tmp/backups/files
S3PATH=/db_web_data/db_data
LOG_DIR=/var/log
mkdir -p $OUTPUTDIR
if ! [ -d $OUTPUTDIR ];then
     echo "BACKUP DIR DOESNOT EXITS"
fi
DATE=`date +%Y%m%d%H%M`

#rotate function
#input : file path ex  /var/log/httpd/pincommerce/account_scraper.log 
#        rotatetame :ex 5
function rotate(){

    local date=`date +%Y%m%d%H%M`
    local log_name=`basename ${1}`
    local log_dir=`dirname ${1}`
    cd ${log_dir}
    cp ${log_name} ${log_name}-${date} 
    #local cw=`find ${log_dir} -name "${log_name}-*" | wc -l`
    #echo $cw
    if [ $2 -lt `find ${log_dir} -name "${log_name}-*" | wc -l` ] ; then
        echo '' >${log_name}
    fi    
}
#rotate /var/log/httpd/pincommerce/account_scraper.log 2
php ${cur_dir}/bashcall.php getDBInfo account
exit 0
#Backup DB
for host in $DB01_HOST $DB02_HOST $DB03_HOST 
do
    echo "link to $host"
    DBs=`mysql -uroot -h"$host" -Bse 'show databases'| grep -e store_* -e account -e job`
    for db in $DBs
    do
        echo "dump DB $db"
        mysqldump -uroot -h"$host" $db | gzip > ${OUTPUTDIR}/dbbackup_${db}_${DATE}.bak.gz
        echo ${OUTPUTDIR}/dbbackup_${db}_${DATE}.bak.gz 
    done
done

#Backup Web Log
cd $LOG_DIR || {
    echo "Cannot change to necessary directory ."
    exit 1
}
cp -r httpd ${OUTPUTDIR}/web_log_backup
cd httpd
echo `pwd`

for log in `find ${LOG_DIR}/httpd/*`
do  
    if [ -f "${log}" ]; then 
        echo "Now clean up file ${log}"
        cat /dev/null >${log}
    fi
done

cd $OUTPUTDIR || {
    echo "Cannot change to necessary directory ."
    exit 1
}
gzip -9 -cr web_log_backup > web_log_backup_${DATE}.bak.gz
rm -fr web_log_backup

#
sendfiletos3(){
    #php bashcall.php upload_image $1 $2
    php ${cur_dir}/bashcall.php upload_image $1 $2
}

#find the file which is createed just now,and send to S3 
for file in `find ${OUTPUTDIR}/* -mtime -1`
do  
    file_name=`basename ${file}`
    file_dir=`dirname  ${file}`
    echo "Now up load file $file_name to ${S3PATH} form $file_dir"
    sendfiletos3 ${S3PATH}/${file_name} ${file}
done


#rm the temp file
rm -rf ${cur_dir}/env2.conf

END_TIME=`date +"%Y-%m-%d %H:%M:%S"`
echo "Backup code end at $END_TIME"

exit 0