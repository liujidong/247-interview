<?php

class FieldsMapper {

    public static function deleteProductField($dbobj, $product_id, $field_id) {
        $sql = "delete from products_fields where product_id = $product_id and field_id = $field_id";
        $dbobj->query($sql);
    }

    public static function getAvailableQuantity($dbobj, $product_id) {
        $sql = "select sum(quantity) as cnt from fields where product_id = $product_id and status !=" . DELETED;
        if($res = $dbobj->query($sql, $dbobj)) {
            if($record = $dbobj->fetch_assoc($res)) {
                $r = $record['cnt'];
                if(empty($r))return 0;
                return $r;
            }
        }
        return 0;
    }

}
