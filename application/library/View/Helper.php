<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

abstract class View_Helper
{
    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }
}

