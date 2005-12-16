<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'version.php' );
require_once( 'config.php' );
require_once( 'libdrawers.php' );
require_once( 'webspaces.php' );
require_once( 'afs.php' );
require_once( 'smarty.custom.php' );

browser_check();

$uploadError = process_upload($notifyMsg, $errorMsg);

$path = ( isset( $_GET['path'] )) ? $_GET['path'] : '';
$afs  = new Afs( $path );

$webspaces = new Webspaces();
$smarty = new Smarty_Template;

$webSelected = true;
$homeSelected = false;

// Use the "makewebspace.css" stylesheet.
$stylesheets[] = "/css/makewebspace.css";

// Don't display the file manager on this page.
$displayfileman = 0;

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

$smarty->assign( 'service_name', $service_name);
$smarty->assign( 'service_url', $service_url);
$smarty->assign( 'secure_service_url', $secure_service_url);

$smarty->assign( 'homeSelected', $homeSelected );
$smarty->assign( 'webSelected', $webSelected );

$smarty->assign( 'filedrawers_title', 'make-webspace');
$smarty->assign( 'javascripts', $javascripts);
$smarty->assign( 'stylesheets', $stylesheets);

$smarty->assign( 'js_vars', $afs->get_js_declarations());
$smarty->assign( 'path_url', urlencode($afs->path));
$smarty->assign( 'parentPath', urlencode( $afs->parPath ));
$smarty->assign( 'location', $afs->pathDisplay());

# Makewebspace specific assignments

# Perform any requested webspace preparation.
$prep_results = $webspaces->prepare();

# Create lists of prepared and unprepared webspaces.
$smarty->assign('public_unprepared',
                 $webspaces->get(Webspaces::VISIBILITY_PUBLIC,
                                 Webspaces::STATUS_UNPREPARED));
$smarty->assign('private_unprepared',
                 $webspaces->get(Webspaces::VISIBILITY_PRIVATE,
                                 Webspaces::STATUS_UNPREPARED));
$smarty->assign('public_prepared',
                 $webspaces->get(Webspaces::VISIBILITY_PUBLIC,
                                 Webspaces::STATUS_PREPARED));
$smarty->assign('private_prepared',
                 $webspaces->get(Webspaces::VISIBILITY_PRIVATE,
                                 Webspaces::STATUS_PREPARED));

$smarty->assign( 'prep_results', $prep_results);
$smarty->assign( 'js_displayfileman', $displayfileman);

$smarty->assign( 'filedrawers_version', $filedrawers_version);

$smarty->display( 'makewebspace.tpl' );
?>
