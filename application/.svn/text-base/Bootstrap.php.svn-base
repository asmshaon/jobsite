<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    protected function _initAutoload() {
// Add autoloader empty namespace
        $autoLoader = Zend_Loader_Autoloader::getInstance();

        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
                        'basePath' => APPLICATION_PATH,
                        'namespace' => '',
                        'resourceTypes' => array(
                                'form' => array(
                                        'path' => 'forms/',
                                        'namespace' => 'Form_',
                                ),
                                'model' => array(
                                        'path' => 'models/',
                                        'namespace' => 'Model_'
                                ),
                        ),
        ));
// Return it so that it can be stored by the bootstrap
        return $autoLoader;
    }

    protected function _initRoutes() {

        $frontController=Zend_Controller_Front::getInstance();
        $router=$frontController->getRouter();

        $router->addRoute(
                'website',
                new Zend_Controller_Router_Route('/',
                array(  'module'=>'w',
                        'controller'=>'home',
                        'action'=>'index'
                )
                )
        );
    }
}