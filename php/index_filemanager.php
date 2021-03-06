<?php
/*
 * Copyright (c) 2008 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */
 
require_once( 'version.php' );
require_once( 'config.php' );
require_once( 'libdrawers.php' );
require_once( 'afs.php' );
require_once( 'smarty.custom.php' );

browser_check();

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

$smarty->assign( 'js_vars', $afs->get_js_declarations());

// Set error messages
if ( $uploadError ) {
    $smarty->assign( 'warnUser', rawurlencode( $errorMsg ));
} else if ( !empty( $afs->errorMsg )) {
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

$smarty->assign( 'filedrawers_title', 'afs file management');
$smarty->assign( 'javascripts', $javascripts);
$smarty->assign( 'stylesheets', $stylesheets);

$smarty->assign( 'path_url', urlencode($afs->path));
$smarty->assign( 'parentPath', urlencode( $afs->parPath ));
$smarty->assign( 'location', $afs->pathDisplay());

$smarty->assign( 'js_displayfileman', $displayfileman);

$smarty->assign( 'filedrawers_version', $filedrawers_version);

$smarty->assign( 'type', $afs->type );
$smarty->assign( 'formKey', $afs->formKey );

if ( $afs->type == 'file' ) {
	$smarty->assign( 'js_displayfileman', 0 );
	$smarty->assign( 'mimetype', $afs->mimetype );
	$smarty->display( 'viewfile.tpl' );
} else {
	$smarty->display( 'fileman.tpl' );
}

?>
