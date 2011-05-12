<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

require_once 'Filedrawers/Filesystem/Exception.php';

abstract class Filedrawers_Filesystem {

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

}
