<?php
class MissingConvertedPhotosMapper {
    
    public static function get_missing_converted_photo_ids($dbobj) {
        $sql = 'select id from missing_converted_photos where status=1 order by id';
        $return = array();

        if ($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record['id'];
            }
        }
        return $return;
    }
    
    public static function get_recovered_converted_photo_ids($dbobj) {
        $sql = 'select id from missing_converted_photos where status=4 order by id';
        $return = array();

        if ($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record['id'];
            }
        }
        return $return;
    }
    
}
