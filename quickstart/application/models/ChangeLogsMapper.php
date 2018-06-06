
<?php

class ChangeLogsMapper {
    
    public static function getLogs($dbobj) {
        $sql = 'select * from change_logs where status='.CREATED;
        $return = array();

        if ($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
}