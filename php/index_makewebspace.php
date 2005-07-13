<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../../objects/webspaces.php' );
require_once( '../../objects/afs.php' );
require_once( '../../smarty/smarty.custom.php' );

$path = ( isset( $_GET['path'] )) ? $_GET['path'] : '';
$afs  = new Afs( $path );

$webspaces = new Webspaces();
$smarty = new Smarty_Template;

$webSelected = true;
$homeSelected = false;

# File manager assignments
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
$smarty->assign( 'stylesheets', array("/fileman.css", "/makewebspace.css"));
$smarty->assign( 'trouser_title', 'make-webspace');

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
