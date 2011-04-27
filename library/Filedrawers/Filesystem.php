<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

require_once 'Filedrawers/Filesystem/Exception.php';

abstract class Filedrawers_Filesystem {
    public function init()
    {
        $this->addListHelper(array('Model_Mime', 'setIcon'));
        return TRUE;
    }
}
