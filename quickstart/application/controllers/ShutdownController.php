<?php

class ShutdownController extends StaticController
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();   
    }

    public function indexAction()
    {
    }
}
