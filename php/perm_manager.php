<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../objects/afs.php' );
require_once( '../objects/favorites.php' );
require_once( '../smarty/smarty.custom.php' );

$afs = new Afs( $_GET['target'] );
$perms = array( 'l', 'r', 'w', 'i', 'd', 'k', 'a' );

if ( isset( $_POST['nrm'] )) {
	foreach ( $_POST['nrm'] as $group => $rights ) {
		$rights = implode( $rights );
		$rights = ( $rights == ' ' ) ? 'none' : $rights;

		if ( $group == 'nrm_add' ) {
			$afs->changeAcl( $_POST['nrm_add_name'], $rights );
		} else if ( $rights !== 1 ) {
			$afs->changeAcl( $group, $rights );
		}
	}
}

if ( isset( $_POST['neg'] )) {
	foreach ( $_POST['neg'] as $group => $rights ) {
		$rights = implode( $rights );
		$rights = ( $rights == ' ' ) ? 'none' : $rights;

		if ( $group == 'neg_add' ) {
			$afs->changeAcl( $_POST['neg_add_name'], $rights, '', false, true );
		} else if ( $rights !== 1 ) {
			$afs->changeAcl( $group, $rights, '', false, true );
		}
	}
}

$rights    = $afs->readAcl();
$pathParts = pathinfo( $afs->path );

$smarty = new Smarty_Template;
$smarty->assign( 'target', $afs->path );
$smarty->assign( 'folder', $pathParts['basename'] );

if ( isset( $rights['normal'] )) {
    $smarty->assign( 'normal', $rights['normal'] );
}

if ( isset( $rights['negative'] )) {
    $smarty->assign( 'negative', $rights['negative'] );
}

$smarty->display( 'pieces/permissions.tpl' );
?>

