<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

function classLoader( $className )
{
    $classNameArr = explode( '_', $className );
    
    if (is_array($classNameArr) && count($classNameArr) === 2) {
        if ($classNameArr[0] === 'Model') {
            $path = APPLICATION_PATH . '/models/' . $classNameArr[1] . '.php';
        } else {
            list( $package, $fileName ) = $classNameArr;
            $path = APPLICATION_PATH . '/library/' . $package . '/' . $fileName . '.php';
        }
    } else {
        $path = APPLICATION_PATH . '/library/' . $className . '.php';
    }

    require_once $path;
}

spl_autoload_register( 'classLoader' );

