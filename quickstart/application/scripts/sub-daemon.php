<?php
require_once('includes.php');

while(1) {
    
    if(!Sub::subscribe(UPDATE_PRODUCT_STATUS)) {
        sleep(5);
    }
}