<?php

class Zend_View_Helper_AddToCart extends Zend_View_Helper_Abstract {

    public function addToCart($quantity=1){
        if($quantity > 0){
        $html = '
<a class="server-widget radius button medium buy">Add To Cart</a>
';
        } else {
            $html = '
<a class="server-widget radius button medium buy" style="pointer-events: none;background: #AAA;">Add To Cart</a>
';
        }
        echo $html;
    }
}