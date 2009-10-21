<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

class Auth_AdapterCosign extends Auth_Adapter {
    public function authenticate()
    {
        $this->username = $_SERVER['REMOTE_USER'];
    }
}

