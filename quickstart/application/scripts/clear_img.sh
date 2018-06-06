#!/bin/bash
FILES=`find /tmp/*.jpg`
CURRENT_TIME=`date +"%Y-%m-%d %H:%M:%S"`
echo "Current time is $CURRENT_TIME"
CHECK_TIME=`date -d -30Minute +"%T"`
for f in $FILES
do
    #echo "Delete file $f"
    LAST_MODIFY_TIME=`stat $f | grep -i Modify | awk -F . '{print $1}' | awk  ' {print $2,$3}'`
    echo "$LAST_MODIFY_TIME"
    t1=`date -d "$CHECK_TIME" +%s`
    t2=`date -d "$LAST_MODIFY_TIME" +%s`
    if [ $t1 -gt $t2 ]; then
        echo "Delete file $f"
        rm -f $f	
    fi
done