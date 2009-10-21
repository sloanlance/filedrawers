<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */
class Config
{
    private static $instance;
    private static $configData = array();

    private function __construct() {}

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public static function __get($property)
    {
        if (isset(self::$configData[$property])) {
            return self::$configData[$property];
        } else {
            return null;
        }
    }


    public static function __set($name, $value)
    {
        self::$configData[$name] = $value;
    }


    public static function readConfig($configFilePath)
    {
        self::$configData = parse_ini_file($configFilePath, true);
    }
}

