<?php

class CountriesMapper {

    public static function getAllCountryInfo($dbobj) {
        $sql = "select * from countries order by short_name";
        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }

        }
        return $return;
    }

    public static function getCountryInfo($dbobj, $iso2="US") {
        $sql = "select * from countries where iso2 = '$iso2'";

        if ($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                return $record;
            }
        }
        return array();
    }
    
    public static function getCachedObjectList($params, $dbobj) {
        
        $ck = $params['_cachekey'];
        $dbname = $ck->getDBName();
        
        $sql = "select id from countries order by short_name";
        $return = array();

        if ($res = $dbobj->query($sql)) {
            while ($record = $dbobj->fetch_assoc($res)) {
                $return[] = $dbname.'.country?id='.$record['id'];
            }

        }
        return $return;
    }
}
