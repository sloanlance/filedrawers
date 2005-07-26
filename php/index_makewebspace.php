<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../../lib/config.php' );
require_once( '../../lib/libdrawers.php' );
require_once( '../../objects/webspaces.php' );
require_once( '../../objects/afs.php' );
require_once( '../../smarty/smarty.custom.php' );

$uploadError = process_upload($notifyMsg, $errorMsg);

$path = ( isset( $_GET['path'] )) ? $_GET['path'] : '';
$afs  = new Afs( $path );

$webspaces = new Webspaces();
$smarty = new Smarty_Template;

$webSelected = true;
$homeSelected = false;

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

$smarty->assign( 'trouser_title', 'make-webspace');
$smarty->assign( 'javascripts', array("/js/filemanage.js"));
$smarty->assign( 'stylesheets', array("/fileman.css", "/makewebspace.css"));

$afs->make_smarty_assignments($smarty);

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
$smarty->display( 'makewebspace.tpl' );
?>
