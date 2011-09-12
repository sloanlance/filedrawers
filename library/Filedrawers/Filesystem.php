<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

require_once 'Filedrawers/Filesystem/Exception.php';

abstract class Filedrawers_Filesystem {

    public static $pathSeparator = '/';
	protected $ILLEGAL_DIR_CHARS =  "/~ \t\n\r\0\x0B.";

    public function init()
    {
        $this->addListHelper(array('Model_Mime', 'setIcon'));
        return TRUE;
    }

    public function getHomedir()
    {
        $userInfo = posix_getpwnam(Zend_Auth::getInstance()->getIdentity());

        if ( ! empty($userInfo['dir']) && is_dir($userInfo['dir'])) {
            return $userInfo['dir'];
        } else {
            return '/';
        }
    }
	
    abstract function getFileHandle( $path, $mode = 'rb' );

    public static function pathConcat()
    {
        $args = func_get_args();
        $path = '';
        if (substr($args[ 0 ], 0, 1) == self::$pathSeparator) {
            $path .= self::$pathSeparator;
        }

        $pathParts = array();
        foreach($args as $part) {
            $part = trim($part, self::$pathSeparator);
            if ( ! empty($part)) {
                $pathParts[] = trim($part, self::$pathSeparator);
            }
        }

        return $path .= implode(self::$pathSeparator, $pathParts);
    }


    public function getQuota($path)
    {
        return array('total' => NULL, 'user' => NULL);
    }


    public function listFavs()
    {
    }

    public function addFavs()
    {
    }

    public function renameFavs()
    {
    }

    public function deleteFavs()
    {
    }

    protected function getPermissions($path)
    {
        return array(
            'read' => FALSE,
            'write' => FALSE,
            'delete' => FALSE,
            'lock' => FALSE,
            'admin' => FALSE
        );
    }

    protected static function getDefaultPermissions()
    {
        return array(
            'read' => FALSE,
            'write' => FALSE,
            'delete' => FALSE,
            'lock' => FALSE,
            'admin' => FALSE
        );
    }
}
