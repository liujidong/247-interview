<?php

class ShippingOptionsMapper{

    public static function getDestination($dbobj, $shipping_id) {
        $sql = "select * from shipping_destinations where shipping_option_id = $shipping_id";

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getShippingOptions($dbobj){

        $sql = "select id, name from shipping_options where status!=".DELETED;

        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $record['shipping_destinations'] = self::getDestination($dbobj, $record['id']);
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getShippingOptionsWithDests($dbobj){
        $sql = "select
                so.id as so_id, so.name as so_name,
                sd.*
                from shipping_options so
                left join shipping_destinations sd on (so.id = sd.shipping_option_id)";
        $return = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }

    public static function getShippingOptionsForOrder($dbobj, $pids, $dest){
        // Domestic, International
        if($dest != "Domestic" && $dest != "International"){
            $store = Store::findStoreByDBObj($dbobj);
            if($dest == $store['country']){
                $dest = "Domestic";
            } else {
                $dest = "International";
            }
        }

        $dest = $dbobj->escape($dest);
        $pids = implode(',' , array_unique($pids));
        $return = array();

        $sql = "select so.name as so_name,
                sd.*, pso.product_id, sd.id as dest_id
                from shipping_options so
                join shipping_destinations sd on (so.id = sd.shipping_option_id)
                join products_shipping_options pso on (pso.shipping_option_id = so.id)
                where (sd.name = '$dest' or sd.name = '') and pso.product_id in ($pids) and
                so.status != " . DELETED . " and sd.status != " . DELETED . "
                order by sd.name desc";
        $opts = array();
        if($res = $dbobj->query($sql, $dbobj)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $opts[$record['product_id']][$record['so_name']] = $record;
            }
        }

        $common_opts = NULL;
        if(count($opts) > 1){
            $common_opts = call_user_func_array('array_intersect', array_map(function($a){return array_keys($a);}, $opts));
        } else if(count($opts) == 1){
            $common_opts = array_values($opts);
            $common_opts = array_keys($common_opts[0]);
        } else {
            $common_opts = array();
        }
        foreach($common_opts as $opt_name){
            foreach($opts as $pr){
                if(isset($pr[$opt_name])){
                    $return[$opt_name] = $pr[$opt_name];
                    break;
                }
            }
        }

        if(!in_array("Standard", $opts)) { // add standrad options
            $sql = "select so.name as so_name,
                    sd.*, sd.id as dest_id
                    from shipping_options so
                    join shipping_destinations sd on (so.id = sd.shipping_option_id)
                    where so.name = 'Standard' and (sd.name = '$dest' or sd.name = '')
                    and so.status != " . DELETED . " and sd.status != " . DELETED . "
                    order by sd.name desc"; // dest first
            $std_opt = array();
            if($res = $dbobj->query($sql, $dbobj)) {
                while($record = $dbobj->fetch_assoc($res)) {
                    $std_opt[] = $record;
                }
            }
            if(count($std_opt)>0){
                $return['Standard'] = $std_opt[0];
            }
        }

        return $return;
    }
}
