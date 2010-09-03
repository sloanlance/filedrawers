<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initAutoload()
    {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => APPLICATION_PATH));
        return $moduleLoader;
    }


    protected function _initConfig()
    {
        $config = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/application.ini',
            APPLICATION_ENVIRONMENT
        );
        Zend_Registry::set('config', $config);
    }


    protected function _initActionHelpers() {
        Zend_Controller_Action_HelperBroker::addPrefix ('Filedrawers_Controller_Action_Helper');
	}


    protected function _initFilesystem()
    {
        $afs = new Model_Afs();
        Zend_Registry::set('filesystem', $afs);

        $afs->setHomeDirHelper(array('Model_UMForceHomeDirectory', 'getHomeDirectory'));
        $afs->addListHelper(array('Model_Mime', 'setIcon'));
        $afs->addListHelper(array('Model_Afs', 'setPermissions'));
    }


    public function _initRoutes()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();

        /*$home = new Zend_Controller_Router_Route_Static(
            '',
            array(
                'controller' => 'index',
                'action' => 'list'
            )
        );


        $list = new Zend_Controller_Router_Route_Regex(
            'list(.*)',
            array(
                'module' => 'webapp',
                'controller' => 'index',
                'action' => 'list'
            ),
            array(
                1 => 'path'
            ),"list%s"
        );


        $finishUpload = new Zend_Controller_Router_Route_Regex(
            'finish/([a-f0-9]*)(.*)',
            array(
                'module' => 'webapp',
                'controller' => 'index',
                'action' => 'finish'
            ),
            array(
                1 => 'finishID',
                2 => 'path'
            ),"finish/%s%s"
        );*/

        $webservice = new Zend_Controller_Router_Route(
            'webservices/:action',
            array(
                'module' => 'webservices',
                'controller' => 'index'
            )
        );


        /*$favorites = new Zend_Controller_Router_Route(
            'webservices/favorites/:action',
            array(
                'module' => 'webservices',
                'controller' => 'favorites'
            )
        );*/

        //$router->addRoute('home', $home);
        //$router->addRoute('list', $list);
        //$router->addRoute('finishUpload', $finishUpload);
        $router->addRoute('webservice', $webservice);
        //$router->addRoute('favorites', $favorites);
    }
}

