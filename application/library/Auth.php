<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

class Auth {
    private static $adapter = null;

    private function __construct() {}

    public static function getInstance()
    {
        if ( empty( self::$adapter )) {
            // TODO: Throw an exception
            exit('User has not authenticated yet.');
        }

        return self::$adapter;
    }

    public static function authenticate(Auth_Adapter $adapter)
    {
        self::$adapter = $adapter;
        self::$adapter->authenticate();
    }

    public function getAdapter()
    {
        return self::$adapter;
    }
}

