<?php

class Auction extends BaseModel {

    public function isActive() {
        $status = $this->getStatus();
        $start_time = $this->getStartTime();
        $end_time = $this->getEndTime();
        $now = date("Y-m-d H:i:s");
        return ( $status === ACTIVATED && $start_time <= $now && $now < $end_time);
    }
}
