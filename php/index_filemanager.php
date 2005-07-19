<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../objects/config.php' );
require_once( '../objects/afs.php' );
require_once( '../smarty/smarty.custom.php' );

// Take care of file uploads.

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
                        $errorMsg = "The file '" . $file[0] .
                                "' already exists."
                                . " The upload cannot continue.";
                        $uploadError = true;
                    }
                }

                if ( isset( $file[2] ) &&
                        trim( $file[2] ) == 'not successful' ) {
                    if ( $uploadError == false ) {
                        $errorMsg =
                                "One or more files did not upload sucessfully";
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

$webSelected = false;
$homeSelected = true;

$smarty->assign( 'service_name', $service_name);
$smarty->assign( 'service_url', $service_url);
$smarty->assign( 'secure_service_url', $secure_service_url);
$smarty->assign( 'returnToURI', 'https://' . $_SERVER['HTTP_HOST'] .
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
$smarty->assign( 'trouser_title', 'afs file management');
$smarty->assign( 'javascripts', array("/js/filemanage.js"));
$smarty->assign( 'stylesheets', array("/fileman.css"));
$smarty->display( 'fileman.tpl' );
?>
