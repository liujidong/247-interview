<?php

class EmailTemplate extends BaseModel {
    public static function format($data, $ck){
        switch ($data['status']) {
        case 0:
            $status = "Created";
            break;
        case 1:
            $status = "Pending";
            break;
        case 2:
            $status = "Active";
            break;
        case 127:
            $status = "Deleted";
            break;
        default:
            $status = $data['status'];
        }
        $data['literal_status'] = $status;

        return $data;
    }

}
