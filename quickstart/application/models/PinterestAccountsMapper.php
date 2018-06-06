<?php

class PinterestAccountsMapper {
    
    public static function getBoards($pinterest_account_id, $dbobj,$fliter=null) {
        
        if(!empty($fliter)){
            $fliter=' and '.$fliter;
        }
     
        $sql = "select pb.*, if(spb.store_id is null, 0, spb.store_id) as store_id  from pinterest_accounts pa join pinterest_accounts_pinterest_boards papb 
            on (pa.id=papb.pinterest_account_id) join pinterest_boards pb on (papb.pinterest_board_id=pb.id) 
            left join stores_pinterest_boards spb on (spb.pinterest_board_id=pb.id)
            where pa.id=$pinterest_account_id $fliter group by pb.id";
        $return = array();
        
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        
        return $return;
        
    }
    
    public static function getBoardsInfo($pinterest_account_id, $dbobj) {
     
        $sql = "select pb.*
            from pinterest_accounts_pinterest_boards papb join pinterest_boards pb on (papb.pinterest_board_id=pb.id)
            where papb.pinterest_account_id=$pinterest_account_id";
        $return = array();
        
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        
        return $return;
    }
    
    
}


