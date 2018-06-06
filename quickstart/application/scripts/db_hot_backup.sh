#!/bin/bash
# -*- sh -*-

WEBROOT=`dirname $0`/../../..
OUTPUT_DIR=/tmp/db-backup/`date +%Y%m%d`
mkdir -p $OUTPUT_DIR
DATE=`date +%H%M`

#EXTRA_ARGS="--defaults-file=/home/kdr2/Pool/data/my.cnf"

backup_database(){
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

    CMD="mysqldump $EXTRA_ARGS --databases --add-drop-database -h$DB_HOST -u$DB_USER $DB_PWD $DB_NAME > $OUTPUT_DIR/$DB_NAME-$DATE.bak"
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

backup_store(){
    if [ "X$1" != "X" ]; then
        if [ "X$2" != "X" ]; then
            for N in `seq $1 $2`; do
                backup_database store $N
            done
        else
            backup_database store $1
        fi
    else
        find_max_store_id
        for N in `seq $1 $MAX_STORE_ID`; do
            backup_database store $N
        done
    fi
}


case $1 in
    "")
        backup_database account
        backup_store
        ;;
    account)
        backup_database account
        ;;
    store)
        backup_store $2 $3
        ;;
    *)
        echo "nothing to do!"
        ;;
esac
