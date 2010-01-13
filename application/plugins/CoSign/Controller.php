<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */
require_once "AdapterCosign.php";

class Plugin_CoSign extends Controller_Plugin {
    public function frontAction()
    {
        $cosign = new Auth_AdapterCosign();
        Auth::authenticate($cosign);
    }
}

