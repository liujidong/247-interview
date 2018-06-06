<?php
class ConvertedPicturesMapper {
    public static function delete_converted_picture($picture_id, $dbobj) {
        $sql = 'delete from converted_pictures where picture_id='.$picture_id;
        $dbobj->query($sql);        
    }
    
    public static function delete_converted_picture_by_id($id, $dbobj) {
        $sql = 'delete from converted_pictures where id='.$id;
        $dbobj->query($sql);        
    }   
    
    public static function get_converted_pictures($picture_id, $dbobj) {
        $sql = 'select * from converted_pictures where picture_id='.$picture_id;
        $return = array();

        if ($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) {
                $return[] = $record;
            }
        }
        return $return;
    }
    
    public static function get_convert_type($picture_id, $dbobj) {
        $sql = "select p.url as picture_url, 
                cp.id as converted_picture_id, cp.type as converted_picture_type, cp.url as converted_picture_url
                from 
                pictures p
                left join converted_pictures cp on (p.id = cp.picture_id)
                where p.id = $picture_id";
        $pic_type = array(ORIGINAL, CONVERTED45, CONVERTED70, CONVERTED192, CONVERTED236, CONVERTED550, CONVERTED736);
        $return = array();
        if($res = $dbobj->query($sql)) {
            while($record = $dbobj->fetch_assoc($res)) { 
                
                if(stored_in_s3_stores($record['picture_url'])) {
                    $return[] = ORIGINAL;
                }
                
                $url = $record['converted_picture_url'];  
                if(stored_in_s3_stores($url)) {
                   $return[] = $record['converted_picture_type'];   
                } else {
                    ConvertedPicturesMapper::delete_converted_picture_by_id($record['converted_picture_id'], $dbobj);
                }
            }
        }
        return array_values(array_diff($pic_type, $return));            
    }

    public static function getProductId($dbobj, $converted_picture_id) {
        $sql = "select p.*
            from products p
            join products_pictures pp on (p.id = pp.product_id)
            join pictures ps on (pp.picture_id = p.id)
            join converted_pictures cp on (cp.picture_id = ps.id)
            where cp.id = $converted_picture_id";

        $product_id = 0;
        if($res = $dbobj->query($sql)) {
            
            if($record = $dbobj->fetch_assoc($res)) {
                $product_id = $record['id'];
            }
        }

        return $product_id;
    }

}
