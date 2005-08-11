<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

// Standard javascripts
$javascripts = array("/js/filemanage.js");

// Standard stylesheets
$stylesheets = array("/fileman.css");

// Default value for $displayfileman
$displayfileman = 1;

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

/*
 * Takes care of browser-specific stylesheet includes
 * and redirects.
 */
function browser_check( )
{
    global $stylesheets;
    $platform_comps = array( );
    $msie_major = 0;
    $msie_minor = 0;
    $mac = 0;

    $regex = "/(.*) \((.*)\)/";
    if( preg_match( $regex, $_SERVER['HTTP_USER_AGENT'], $matches )) {
        $browser_str = $matches[1];
        $platform_str = $matches[2];
    }

    $l=strlen( $platform_str );

    $token = "";
    for( $i=0; $i<=$l; $i++ )
    {
        if(( $i == $l ) || (( $c=$platform_str[$i] ) == ";" )) {
            $platform_comps[] = ltrim( rtrim( $token ));
            $token = "";
        } else {
            $token .= $c;
        }
    }

    foreach( $platform_comps as $comp ) {
        $regex = "/MSIE (\d+)\.(\d+)/";
        if( preg_match( $regex, $comp, $matches )) {
            $msie_major = $matches[1];
            $msie_minor = $matches[2];

        }

        if ( preg_match( "/^Mac/i", $comp, $matches)) {
            $mac = 1;
        }
    }

    // We currently don't support MSIE on mac
    if ($msie_major && $mac) {
        header( "Location: /scriptversion.php" );
    }

    // MSIE requires additional stylesheets
    switch ( $msie_major ) {
    case "5":
        $stylesheets[] = "/ie5specific.css";
        break;
    case "6":
        $stylesheets[] = "/ie6specific.css";
        break;
    default:
        break;
    }
}
