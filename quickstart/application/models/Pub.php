<?php

class Pub {

    public static function publish($channel, $message) {
        global $redis;
        $ck = CacheKey::q($channel);
        $data['type'] = $channel;
        $data['data'] = $message;
        $data = json_encode($data);

        DAL::addToList($ck, array($data=>1));
    }
}
