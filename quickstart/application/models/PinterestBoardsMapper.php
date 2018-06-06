<?php

class PinterestBoardsMapper {

    public static function getPinsCount($pinterest_board_id, $dbobj) {
        $sql = "select count(pp.id) as cnt from pinterest_boards pb join pinterest_boards_pinterest_pins pbpp 
            on (pb.id=pbpp.pinterest_board_id) join pinterest_pins on (pbpp.pinterest_pin_id=pp.id)";
        $return = 0;
        
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $return = $record['cnt'];
            }
        }
        
        return $return;
        
    }
    
    //get all boards info include board_id board_url $filter=status filter
    public static function getAllBoardsInfo($dbobj){
        $return=array();
        $sql="select id,url from pinterest_boards where url<> '' and status <>". DELETED;
        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
    
    //
    public static function getAllPinsByBoradId($board_id,$dbobj){
        $return=array();
        $sql="select pp.id,pp.external_id from pinterest_pins pp 
            join pinterest_boards_pinterest_pins pbpp on (pbpp.pinterest_pin_id=pp.id)
            join pinterest_boards pb on (pbpp.pinterest_board_id=pb.id)
            where pb.id=$board_id and pp.status <>". DELETED;
        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
    
    public static function deletePinsByBoardId($board_id,$dbobj){
        $sql="update pinterest_pins as pp 
            join pinterest_boards_pinterest_pins as pbpp on (pbpp.pinterest_pin_id=pp.id)
            join pinterest_boards as pb on (pbpp.pinterest_board_id=pb.id)
            set pp.status=".DELETED.
            " where pb.id=$board_id ";
        return $dbobj->query($sql);
    }
    
}


