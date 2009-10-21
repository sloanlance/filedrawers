<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

abstract class Controller_Core extends Controller
{
    protected $plugins = array();

    public function registerPlugins()
    {
        $config = Config::getInstance();
        $controllerName = get_class($this);
        $plugins = array();

        if (isset($config->plugins[$controllerName])) {
            $plugins = $config->plugins[$controllerName];
        }

        foreach($plugins as $plugin) {
            require_once APPLICATION_PATH . "/plugins/$plugin/Controller.php";
            $pluginClass = 'Plugin_' . $plugin;
            $this->plugins[] = new $pluginClass();
        }
    }


    public function __call($action, $arguments) {
        foreach($this->plugins as $plugin) {
            $plugin->{$action}();
        }
    }
}

