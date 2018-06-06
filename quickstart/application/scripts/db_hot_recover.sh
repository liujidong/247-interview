#!/bin/bash
# -*- sh -*-

WEBROOT=`dirname $0`/../../..
BACKUP_DIR=/tmp/db-backup
DATE=`date +%Y%m%d%H%M`

#EXTRA_ARGS="--defaults-file=/home/kdr2/Pool/data/my.cnf"

READER=pv
which $READER > /dev/null
if [ $? != 0 ]; then
    READER=cat
fi

find_latest_backup_file(){
    #args: db_name
    CMD="find $BACKUP_DIR -name *$1*.bak | sort | tail -n 1"
    echo "PROCESSING CMD: $CMD"
    LATEST_BACKUP_FILE=`sh -c "$CMD"`
}

restore_database(){
    DB_HOST=`php $WEBROOT/quickstart/application/scripts/get_conf.php database.$1.host`
    DB_USER=`php $WEBROOT/quickstart/application/scripts/get_conf.php database.$1.user`
    DB_PWD=`php $WEBROOT/quickstart/application/scripts/get_conf.php database.$1.password`
    DB_NAME=`php $WEBROOT/quickstart/application/scripts/get_conf.php database.$1.name`

    if [ "X$DB_PWD" = "X" ]; then
        BD_PWD=""
    else
        DB_PWD="-p$DB_PWD"
    fi

    if [ "$1" = "store" ]; then
        DB_NAME="$DB_NAME"_$2
    fi

    find_latest_backup_file $DB_NAME

    if [ "X$LATEST_BACKUP_FILE" = "X" ]; then
        echo "no backup files for $DB_NAME"
        return
    fi
    
    # DROP DATABASE IF EXISTS `$DB_NAME`
    CMD="mysql $EXTRA_ARGS -h$DB_HOST -u$DB_USER $DB_PWD -e 'DROP DATABASE IF EXISTS \`$DB_NAME\`'"
    echo "PROCESSING CMD: $CMD"
    sh -c "$CMD"
    # recover db
    CMD="$READER $LATEST_BACKUP_FILE | mysql $EXTRA_ARGS -h$DB_HOST -u$DB_USER $DB_PWD"
    echo "PROCESSING CMD: $CMD"
    sh -c "$CMD"
}

find_max_store_id(){
    DB_HOST=`php $WEBROOT/quickstart/application/scripts/get_conf.php database.account.host`
    DB_USER=`php $WEBROOT/quickstart/application/scripts/get_conf.php database.account.user`
    DB_PWD=`php $WEBROOT/quickstart/application/scripts/get_conf.php database.account.password`
    DB_NAME=`php $WEBROOT/quickstart/application/scripts/get_conf.php database.account.name`

    if [ "X$DB_PWD" = "X" ]; then
        BD_PWD=""
    else
        DB_PWD="-p$DB_PWD"
    fi

    CMD="echo 'select max(id) from stores;' | mysql $EXTRA_ARGS -Bs -h$DB_HOST -u$DB_USER $DB_PWD $DB_NAME"
    echo "PROCESSING CMD: $CMD"
    MAX_STORE_ID=`sh -c "$CMD"`
}

restore_store(){
    if [ "X$1" != "X" ]; then
        if [ "X$2" != "X" ]; then
            find_max_store_id
            for N in `seq $1 $2`; do
                restore_database store $N
            done
        else
            restore_database store $1
        fi
    else
        find_max_store_id
        for N in `seq $1 $MAX_STORE_ID`; do
            restore_database store $N
        done
    fi
}


case $1 in
    "")
        restore_database account
        restore_store
        ;;
    account)
        restore_database account
        ;;
    store)
        restore_store $2 $3
        ;;
    *)
        echo "nothing to do!"
        ;;
esac
