<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

class Router
{
    private static $instance;
    private static $baseUrl = '';
    private static $fsPath = null;

    private function __construct() {}


    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function setBaseUrl($baseUrl)
    {
        self::$baseUrl = $baseUrl;
    }


    public function getBaseUrl()
    {
        return self::$baseUrl;
    }


    public function getFSpath()
    {
        return self::$fsPath;
    }


    public function getRoute()
    {
        $controller = 'Filedrawers';
        $action = 'index';

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = trim(str_replace(self::$baseUrl, '', $path), '/');

        $parts = explode('/', $path);

        if ( ! empty($parts[0]) && in_array(strtolower($parts[0]),
                Config::getInstance()->router['secondaryControllers'])) {
            $controller = ucfirst(strtolower($parts[0]));
            $action = (empty($parts[1])) ? $action : strtolower($parts[1]);
        }
        else {
            $action = (empty($parts[0])) ? $action : strtolower($parts[0]);
        }

        self::$fsPath = str_replace($action, '', $path);

        return array($controller, $action);
    }
}

