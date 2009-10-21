<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */
require_once APPLICATION_PATH . '/library/View.php';
require_once APPLICATION_PATH . '/library/Request.php';
require_once APPLICATION_PATH . '/library/Router.php';
require_once APPLICATION_PATH . '/library/Controller.php';
require_once APPLICATION_PATH . '/library/Controller/Core.php';
require_once APPLICATION_PATH . '/library/Controller/Plugin.php';
require_once APPLICATION_PATH . '/controllers/FiledrawersController.php';

class Controller_Front
{
    private static $instance;
    private static $request;
    private static $view;

    private function __construct() {}


    public static function getInstance()
    {
        if ( empty( self::$instance )) {
            self::$instance = new self();
            self::$request  = new Request();
            self::$view     = new View();
        }

        return self::$instance;
    }


    public function dispatch()
    {
        $config = Config::getInstance();
        $router = Router::getInstance();

        if (isset($config->router['baseUrl'])) {
            $router->setBaseUrl($config->router['baseUrl']);
        }

        self::registerFrontPlugins('FrontController', 'front');

        $route = $router->getRoute();
        list($controllerName, $actionName) = $route;

        $ctrlClass = $controllerName . 'Controller';

        require_once APPLICATION_PATH . "/controllers/$ctrlClass.php";

        if ( ! class_exists($ctrlClass)) {
            // throw exception: controller does not exist
            exit('controller does not exist');
        }

        $controllerObj = new $ctrlClass();
        $controllerObj->setRequestView(self::$request, self::$view);

        if (method_exists($controllerObj, 'init')) {
            $controllerObj->init();
        }

        $controllerObj->registerPlugins();
        $controllerObj->{$actionName . 'PreAction'}();
        $controllerObj->{$actionName . 'Action'}();
        $controllerObj->{$actionName . 'PostAction'}();

        self::$view->frontRender();
    }


    protected function registerFrontPlugins()
    {
        $config = Config::getInstance();
        $plugins = array();

        if (isset($config->plugins['FrontController'])) {
            $plugins = $config->plugins['FrontController'];
        }

        foreach($plugins as $pluginName) {
            require_once APPLICATION_PATH . "/plugins/$pluginName/Controller.php";
            $pluginClass = 'Plugin_' . $pluginName;
            $plugin = new $pluginClass();
            $plugin->frontAction();
        }
    }
}

