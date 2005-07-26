<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

// Take care of file uploads.
function process_upload(&$notifyMsg, &$errorMsg)
{

    $uploadError = false;
    $errorMsg    = '';

    if ( isset( $_GET['finishid'] )) {
        $temppath = "/tmp/" . $_GET['finishid'];

        if ( file_exists( $temppath )
                && preg_match( "/[^a-f0-9]/", $_GET['finishid'] ) === 0
                && !is_dir( $temppath )) {
            $result = file( $temppath );
            unlink( $temppath ); // Remove the session file

            // Check for upload errors
            if ( is_array( $result )) {
                foreach( $result as $file ) {
                    $file = explode( ':', $file );
                    if ( isset( $file[2] ) &&
                         trim( $file[2] ) == 'File exists' ) {
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
                            $errorMsg = "One or more files did not " .
                                        "upload sucessfully";
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

    return $uploadError;
}
