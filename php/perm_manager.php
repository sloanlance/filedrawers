<?php
/*
 * Copyright (c) 2008 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'afs.php' );
require_once( 'favorites.php' );
require_once( 'smarty.custom.php' );

$afs = new Afs( $_GET['target'] );
$perms = array( 'l', 'r', 'w', 'i', 'd', 'k', 'a' );

if ( isset( $_POST['nrm'] ) && $afs->formKey == $_POST['formKey']) {
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

if ( isset( $_POST['neg'] ) && $afs->formKey == $_POST['formKey']) {
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
$smarty->assign( 'formKey', $afs->formKey );

if ( isset( $rights['normal'] )) {
    $smarty->assign( 'normal', $rights['normal'] );
}

if ( isset( $rights['negative'] )) {
    $smarty->assign( 'negative', $rights['negative'] );
}

$smarty->display( 'permissions.tpl' );
?>

