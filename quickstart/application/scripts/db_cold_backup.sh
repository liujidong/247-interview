#!/bin/bash
# -*- sh -*-

BACKUP_DIR=/tmp/db-backup
mkdir -p $BACKUP_DIR
DATE=`date +%Y%m%d%H%M`
BACKUP_FILE=$BACKUP_DIR/MYSQL-DATA-$DATE-COLD.tar

if [ "X$MYSQL_DATA_DIR" = "X" ]; then
    MYSQL_DATA_DIR=/mnt/resource/mysql
fi

#stop mysql
stop_mysql(){
    for T in `seq 0 2`; do
        service mysqld stop
        ps aux|grep [m]ysqld > /dev/null
        if [ $? = 0 ]; then
            echo "mysql stop failure, will try again 2 sec later"
            sleep 2
        else
            return
        fi
    done

    ps aux|grep [m]ysqld > /dev/null
    if [ $? = 0 ]; then
        echo "I can *NOT* stop mysqld!"
        exit 1
    fi
}


#tar files
tar_data(){
    tar cf $BACKUP_FILE -C `dirname $MYSQL_DATA_DIR` `basename $MYSQL_DATA_DIR`
}


#recover data
recover_data(){
    CMD="find -L $BACKUP_DIR -name MYSQL-DATA-*.tar | sort | tail -n 1"
    echo "PROCESSING CMD: $CMD"
    LATEST_BACKUP_FILE=`sh -c "$CMD"`
    if [ "X$LATEST_BACKUP_FILE" = "X" ]; then
        echo "no backup file found!"
        exit 1
    fi
    rm -fr $MYSQL_DATA_DIR
    cd `dirname $MYSQL_DATA_DIR`
    tar xf $LATEST_BACKUP_FILE
}


#start mysql
start_mysql(){
    for T in `seq 0 2`; do
        service mysqld start
        ps aux|grep [m]ysqld > /dev/null
        if [ $? != 0 ]; then
            echo "start mysqld failure, will try again 2 sec later"
            sleep 2
        else
            return
        fi
    done

    ps aux|grep [m]ysqld > /dev/null
    if [ $? != 0 ]; then
        echo "I can *NOT* start mysqld!"
    fi
}

case $1 in
    hot-backup)
        BACKUP_FILE=$BACKUP_DIR/MYSQL-DATA-$DATE-HOT.tar
        tar_data
        ;;
    backup)
        stop_mysql
        tar_data
        start_mysql
        ;;
    recover)
        stop_mysql
        recover_data
        start_mysql
        ;;
    backup-recover)
        stop_mysql
        tar_data
        recover_data
        start_mysql
        ;;
    *)
        echo "nothing to do!"
        ;;
esac
