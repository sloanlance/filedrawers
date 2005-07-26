<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../lib/config.php' );
require_once( '../lib/libdrawers.php' );
require_once( '../objects/afs.php' );
require_once( '../smarty/smarty.custom.php' );

$uploadError = process_upload($notifyMsg, $errorMsg);

$path = ( isset( $_GET['path'] )) ? $_GET['path'] : '';
$afs  = new Afs( $path );
$smarty = new Smarty_Template;

// Set notification messages
if ( ! empty( $notifyMsg )) {
    $smarty->assign( 'notifyMsg', $notifyMsg );
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

$webSelected = false;
$homeSelected = true;

$smarty->assign( 'service_name', $service_name);
$smarty->assign( 'service_url', $service_url);
$smarty->assign( 'secure_service_url', $secure_service_url);

$smarty->assign( 'homeSelected', $homeSelected );
$smarty->assign( 'webSelected', $webSelected );

$smarty->assign( 'trouser_title', 'afs file management');
$smarty->assign( 'javascripts', array("/js/filemanage.js"));
$smarty->assign( 'stylesheets', array("/fileman.css"));

$afs->make_smarty_assignments($smarty);

$smarty->display( 'fileman.tpl' );
?>
