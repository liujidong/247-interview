<?php

class Zend_View_Helper_NavCart extends Zend_View_Helper_Abstract {

    public function navCart(){
        $span = "";
        if($this->view->nav_cart_num > 0) {
            $span = '<span class="badge-count"> '. $this->view->nav_cart_num . ' </span>';
        } else {
            $span = '<span class="badge-count" style="display: none"> '. $this->view->nav_cart_num . ' </span>';
        }
        $html = '
<a href="/cart" class="server-widget cart nav-cart-link" title="Cart">
<i class="icon fi-shopping-cart">' . $span . '</i>
</a>';
        echo $html;
    }
}