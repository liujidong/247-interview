<?php

class PicturesMapper {
    
    public static function getConvertedPictures($picture_id, $dbobj) {
        
        $sql = "select cp.type, cp.url
            from converted_pictures cp 
            where cp.picture_id = $picture_id";
        
        $return = array(
            'converted_45' => '',
            'converted_70' => '',
            'converted_192' => '',
            'converted_236' => '',
            'converted_550' => '',
            'converted_736' => ''
        );

        if($res = $dbobj->query($sql)) {
            
            while($record = $dbobj->fetch_assoc($res)) {
                
                switch ($record['type']) {
                    case 45:
                        $return['converted_45'] = $record['url'];
                        break;
                    
                    case 70:
                        $return['converted_70'] = $record['url'];
                        break; 
                    
                    case 192:
                        $return['converted_192'] = $record['url'];
                        break;
                    case 236:
                        $return['converted_236'] = $record['url'];
                        break;
                    
                    case 550:
                        $return['converted_550'] = $record['url'];
                        break;
                    
                    case 736:
                        $return['converted_736'] = $record['url'];
                        break;
                    default :                       
                        break;
                }
            }
        }  
        
        return $return;
    }

    public static function getProductId($dbobj, $picture_id) {
        $sql = "select product_id from products_pictures where picture_id = $picture_id";

        $product_id = 0;
        if($res = $dbobj->query($sql)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $product_id = $record['product_id'];
            }
        }
        return $product_id;
    }
}


