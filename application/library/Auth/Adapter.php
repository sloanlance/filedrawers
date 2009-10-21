<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

abstract class Auth_Adapter {
    protected $username;

    public function authenticate() {}
    public function clear() {}

    public function getUsername() {
        return $this->username;
    }
}
