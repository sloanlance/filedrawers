<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

abstract class Controller
{
    protected $request;
    protected $view;

    public function setRequestView(Request $request, View $view)
    {
        $this->request = $request;
        $this->view    = $view;
    }
}

