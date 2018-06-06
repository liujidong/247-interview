<?php

class ReviewsMapper {
    public static function getReviews($dbobj){
        $sql = "select * from reviews order by created desc limit 10 ";
        
        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
}
?>