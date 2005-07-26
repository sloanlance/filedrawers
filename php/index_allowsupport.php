<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../../lib/config.php' );
require_once( '../../lib/libdrawers.php' );
require_once( '../../objects/afs.php' );
require_once( '../../objects/affiliations.php' );
require_once( '../../objects/supportgroups.php' );
require_once( '../../smarty/smarty.custom.php' );

$uploadError = process_upload($notifyMsg, $errorMsg);

$path = ( isset( $_GET['path'] )) ? $_GET['path'] : '';
$afs  = new Afs( $path );

$supportgroups = new Supportgroups;

$smarty = new Smarty_Template;

$webSelected = true;
$homeSelected = false;

$give_support = ( isset( $_POST['give_support'] )) ?
                $_POST['give_support'] : '';
$remove_support = ( isset( $_POST['remove_support'] )) ?
                $_POST['remove_support'] : '';

$supportgroups->give_permissions($give_support);
$supportgroups->remove_permissions($remove_support);

// Set notification messages
if ( ! empty( $notifyMsg )) {
    $smarty->assign( 'notifyMsg', $notifyMsg );
} else if ( ! empty( $give_support )) {
    $smarty->assign( 'notifyMsg',
            "Gave support permissions to departmental support group " .
            $give_support );
} else if ( ! empty( $remove_support )) {
    $smarty->assign( 'notifyMsg',
            "Removed support permissions from departmental support group " .
            $remove_support );
} else if ( ! empty( $afs->notifyMsg )) {
    $smarty->assign( 'notifyMsg', $afs->notifyMsg );
}

// Set error messages
if ( $uploadError ) {
    $smarty->assign( 'warnUser', rawurlencode( $errorMsg ));
} else if ( ! empty( $afs->errorMsg )) {
    $smarty->assign( 'warnUser', rawurlencode( $afs->errorMsg ));
} else if ( isset( $_GET['error'] )) {
    $smarty->assign( 'warnUser', 'Unable to upload the selected file(s).' );
}

$smarty->assign( 'service_name', $service_name);
$smarty->assign( 'service_url', $service_url);
$smarty->assign( 'secure_service_url', $secure_service_url);

$smarty->assign( 'homeSelected', $homeSelected );
$smarty->assign( 'webSelected', $webSelected );

$smarty->assign( 'trouser_title', 'allow-support');
$smarty->assign( 'javascripts', array("/js/filemanage.js",
                                      "/js/allowsupport.js"));
$smarty->assign( 'stylesheets', array("/fileman.css", "/allowsupport.css"));

$afs->make_smarty_assignments($smarty);

$smarty->assign( 'affiliations', $supportgroups->get_affiliations());
$smarty->assign( 'supportgroups', $supportgroups->get());
$smarty->assign( 'give_support', $give_support);
$smarty->assign( 'remove_support', $remove_support);

$smarty->display( 'allowsupport.tpl' );
?>
