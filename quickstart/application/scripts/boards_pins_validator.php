<?php
require_once('includes.php');
global $pinterest_config;
$boardslists=  PinterestBoardsMapper::getAllBoardsInfo($account_dbobj);

foreach ($boardslists as $boardslist) {

    //if borads not exist on Pinterest
    $board_url=$pinterest_config->homepage.$boardslist['url'];
    if(!checkRemoteFile($board_url)){
        //create a board object and mark status with DELETE
        $board=new PinterestBoard($account_dbobj);
        $board->findOne("id=${boardslist['id']}");
        $board->setStatus(DELETED);
        $board->save();
        //Mark all pins under this borads with 127
        PinterestBoardsMapper::deletePinsByBoardId($boardslist['id'], $account_dbobj);
    }else{
        // check pins in this url
        $pinslists=  PinterestBoardsMapper::getAllPinsByBoradId($boardslist['id'], $account_dbobj);
        foreach ($pinslists as $pinslist) {
            $pin_url=$pinterest_config->homepage.'pin/'.$pinslist['external_id'];
            if(!checkRemoteFile($pin_url)){
                $pin=new PinterestPin($account_dbobj);
                $pin->findOne("id=${pinslist['id']}");
                $pin->setStatus(DELETED);
                $pin->save();
            }
        }
    }
}
