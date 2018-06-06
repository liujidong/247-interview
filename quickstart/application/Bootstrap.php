<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initViewHelpers() {

        $this->bootstrap('layout');

        $layout = $this->getResource('layout');
        $view = $layout->getView();
    }

    protected function _initSiteRoutes() {
        //Don't forget to bootstrap the front controller as the resource may not been created yet...
        $this->bootstrap("frontController");
        $front = $this->getResource("frontController");
        //Read the routes from an ini file and in that ini file use the options with routes prefix...
        //$front->getRouter()->addConfig(new Zend_Config_Ini(APPLICATION_PATH . "/configs/routes.ini"), "routes");

        //Add modules dirs to the controllers for default routes...
        $front->addModuleDirectory(APPLICATION_PATH . '/modules');
    }
}

