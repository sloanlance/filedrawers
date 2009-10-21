<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

class Plugin_AfsMime extends Controller_Plugin {
    public function frontAction()
    {
        $afs = new Model_Afs();
        Registry::getInstance()->filesystem = $afs;

        $afs->addListHelper(array('Model_Mime', 'setIcon'));
    }
}

