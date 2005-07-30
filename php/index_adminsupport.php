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

browser_check();

// Don't display the file manager on this page.
$displayfileman = 0;

$uploadError = process_upload($notifyMsg, $errorMsg);

$path = ( isset( $_GET['path'] )) ? $_GET['path'] : '';
$afs  = new Afs( $path );

$supportgroups = new Supportgroups;

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

if (isset($_POST["add_mapping"])) {
    $supportgroups->add_mapping($_POST["new_map_aff"],
                                $_POST["new_map_group"]);
}

if (isset($_POST["delete_mapping"])) {
    foreach($_POST as $key => $garbage) {
        $regex = "/^delete_(\d+)$/";
        if(preg_match( $regex, $key, $matches )) {
            $supportgroups->delete_mapping($matches[1]);
        }
    }
}

$webSelected = false;
$homeSelected = true;

// Use the "adminsupport.css" stylesheet.
$stylesheets[] = "/adminsupport.css";

$smarty->assign( 'service_name', $service_name);
$smarty->assign( 'service_url', $service_url);
$smarty->assign( 'secure_service_url', $secure_service_url);

$smarty->assign( 'homeSelected', $homeSelected );
$smarty->assign( 'webSelected', $webSelected );

$smarty->assign( 'trouser_title', 'admin-support');
$smarty->assign( 'javascripts', $javascripts);
$smarty->assign( 'stylesheets', $stylesheets);

$smarty->assign( 'js_vars', $afs->get_js_declarations());
$smarty->assign( 'path_url', urlencode($afs->path));
$smarty->assign( 'parentPath', urlencode($afs->parentPath()));
$smarty->assign( 'location', $afs->pathDisplay());

$smarty->assign( 'uniqname', $supportgroups->uniqname);
$smarty->assign( 'affiliations', $supportgroups->get_affiliations());
$smarty->assign( 'mappings', $supportgroups->get_mappings());

$smarty->assign( 'js_displayfileman', $displayfileman);

if ($supportgroups->is_admin()) {
    $smarty->display( 'adminsupport.tpl' );
} else {
    $smarty->display( 'adminsupport_noauth.tpl' );
}
?>
