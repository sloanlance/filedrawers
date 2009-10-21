<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */
class Registry
{
    private static $instance;
    private static $data = array();

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
        if (isset(self::$data[$property])) {
            return self::$data[$property];
        } else {
            return null;
        }
    }


    public static function __set($name, $value)
    {
        self::$data[$name] = $value;
    }
}

