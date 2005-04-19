<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../objects/afs.php' );
require_once( '../smarty/smarty.custom.php' );

$uploadError = false;
$errorMsg    = '';

if ( isset( $_GET['finishid'] )) {
    $path = "/tmp/" . $_GET['finishid'];

    if ( file_exists( $path )
      && preg_match( "/[^a-f0-9]/", $_GET['finishid'] ) === 0
      && !is_dir( $path )) {
        $result = file( $path );
        unlink( $path ); // Remove the session file

        // Check for upload errors
        if ( is_array( $result )) {
            foreach( $result as $file ) {
                $file = explode( ':', $file );
                if ( isset( $file[2] ) && trim( $file[2] ) == 'File exists' ) {
                    if ( $uploadError == false ) {
                        $errorMsg = "The file '" . $file[0] . "' already exists."
                          . " The upload cannot continue.";
                        $uploadError = true;
                    }
                }

                if ( isset( $file[2] ) && trim( $file[2] ) == 'not successful' ) {
                    if ( $uploadError == false ) {
                        $errorMsg = "One or more files did not upload sucessfully";
                        $uploadError = true;
                    }
                }
            }

            if ( ! $uploadError ) {
                $notifyMsg = "Successfully received file(s).";
            }
        }
    }
}

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

// Highlight the appropriate service button/tab
if ( strpos( $path, $_SERVER['REMOTE_USER'] . '/Public/html' ) === false ) {
    $webSelected = false;
} else {
    $webSelected = true;
}

$homeSelected = ( $webSelected ) ? false : true;

$smarty->assign( 'returnToURI', 'https://' . $_SERVER['HTTP_HOST']
  . $_SERVER['PHP_SELF'] . "?path=$afs->path&amp;finishid=$afs->sid" );
$smarty->assign( 'path', htmlentities( $afs->path, ENT_QUOTES ));
$smarty->assign( 'folderName', htmlentities( basename( $afs->path ), ENT_QUOTES ));
$smarty->assign( 'folderContents', $afs->folderContents());
$smarty->assign( 'homePath', $afs->getBasePath());
$smarty->assign( 'parentPath', $afs->parentPath());
$smarty->assign( 'sid', $afs->sid );
$smarty->assign( 'newWebSpaceUI', $afs->newWebSpaceUI );
$smarty->assign( 'readonly', $afs->readonly );
$smarty->assign( 'homeSelected', $homeSelected );
$smarty->assign( 'webSelected', $webSelected );
$smarty->assign( 'location', $afs->pathDisplay());
$smarty->display( 'wwwroot/fileman.tpl' );
?>