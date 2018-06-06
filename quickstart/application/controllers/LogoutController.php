<?php

class LogoutController extends BaseController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction() {
        Redis_Session::destroy();
        redirect(getUrl());
    }
}

