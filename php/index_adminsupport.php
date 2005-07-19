<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../../objects/config.php' );
require_once( '../../objects/afs.php' );
require_once( '../../objects/affiliations.php' );
require_once( '../../objects/supportgroups.php' );
require_once( '../../smarty/smarty.custom.php' );

$path = ( isset( $_GET['path'] )) ? $_GET['path'] : '';
$afs  = new Afs( $path );

$supportgroups = new Supportgroups;

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

# File manager assignments
$smarty->assign( 'service_name', $service_name);
$smarty->assign( 'secure_service_url', $secure_service_url); 
$smarty->assign( 'returnToURI',
                 'https://' . $_SERVER['HTTP_HOST'] .
                 $_SERVER['PHP_SELF'] .
                 "?path=$afs->path&amp;finishid=$afs->sid" );
$smarty->assign( 'path', $afs->path);
$smarty->assign( 'folderName', basename( $afs->path ));
$smarty->assign( 'folderContents', $afs->folderContents( true, true ));
$smarty->assign( 'homePath', $afs->getBasePath());
$smarty->assign( 'parentPath', $afs->parentPath());
$smarty->assign( 'sid', $afs->sid );
$smarty->assign( 'readonly', $afs->readonly );
$smarty->assign( 'homeSelected', $homeSelected );
$smarty->assign( 'webSelected', $webSelected );
$smarty->assign( 'location', $afs->pathDisplay());
$smarty->assign( 'javascripts', array("/js/filemanage.js",
                                      "/js/allowsupport.js"));
$smarty->assign( 'stylesheets', array("/fileman.css", "/adminsupport.css"));
$smarty->assign( 'trouser_title', 'admin-support');

$smarty->assign( 'uniqname', $supportgroups->uniqname);

$smarty->assign( 'affiliations', $supportgroups->get_affiliations());
$smarty->assign( 'mappings', $supportgroups->get_mappings());

if ($supportgroups->is_admin()) {
    $smarty->display( 'adminsupport.tpl' );
} else {
    $smarty->display( 'adminsupport_noauth.tpl' );
}
?>
