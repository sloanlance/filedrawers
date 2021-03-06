<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'version.php' );
require_once( 'config.php' );
require_once( 'libdrawers.php' );
require_once( 'afs.php' );
require_once( 'affiliations.php' );
require_once( 'supportgroups.php' );
require_once( 'smarty.custom.php' );

browser_check();

// Don't display the file manager on this page.
$displayfileman = 0;

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

// Use the "allowsupport.js" javascript
$javascripts[] = "/js/allowsupport.js";

// Use the "allowsupport.css" stylesheet.
$stylesheets[] = "/css/allowsupport.css";

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

$smarty->assign( 'filedrawers_title', 'allow-support');
$smarty->assign( 'javascripts', $javascripts);
$smarty->assign( 'stylesheets', $stylesheets);

$smarty->assign( 'js_vars', $afs->get_js_declarations());
$smarty->assign( 'path_url', urlencode($afs->path));
$smarty->assign( 'parentPath', urlencode( $afs->parentPath ));
$smarty->assign( 'location', $afs->pathDisplay());

$smarty->assign( 'affiliations', $supportgroups->get_affiliations());
$smarty->assign( 'supportgroups', $supportgroups->get());
$smarty->assign( 'give_support', $give_support);
$smarty->assign( 'remove_support', $remove_support);

$smarty->assign( 'js_displayfileman', $displayfileman);
$smarty->assign( 'filedrawers_version', $filedrawers_version);

$smarty->display( 'allowsupport.tpl' );
?>
