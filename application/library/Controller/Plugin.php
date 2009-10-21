<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

abstract class Controller_Plugin extends Controller
{
    public function __call($name, $arguments) {
        return false;
    }
}

