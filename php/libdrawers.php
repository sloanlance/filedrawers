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
 * Takes care of browser-specific stylesheet includes and redirects.
 * See docs/HTTP_USER_AGENTS file for partial list of brower IDs reference
 */
function browser_check( )
{
    global $stylesheets;
    $platform_comps = array( );
    $msie_major = 0;
    $msie_minor = 0;
    $mac = 0;
	$platform_str = '';
	$b_id = $_SERVER['HTTP_USER_AGENT'];

	# look into php's get_browser()

	# not suitable for lynx
    if ( preg_match( '/Lynx/i', $b_id )) {
        header( "Location: /scriptversion.php" );
    }

	# Firefox, Mozilla, Netscape, and Camino 
	# all need to be at least "rv:1.7.2" to pass
	if ( preg_match( '/Gecko/', $b_id ) &&
		preg_match( '/ rv:1.7.([^)]*)\)/', $b_id, $Rev )) {
		if ( strlen( $Rev[0] ) && 
			( !strlen( $Rev[1] )  || ( $Rev[1] < 2 ))) {
				header( "Location: /scriptversion.php" );
		}
	}

	# Win Opera 7.5 is broken, but 8 works
    if ( preg_match( '/Opera\/7/', $b_id )) {
        header( "Location: /scriptversion.php" );
    }

    # We currently don't support Omniweb 4.5, although 5.1.1 works
    if ( preg_match( '/OmniWeb\/v496$/', $b_id )) {
        header( "Location: /scriptversion.php" );
    }

	# Check for Safari after Omniweb, since OW uses Safari's string
    if ( preg_match( '/Safari\/(.*)$/', $b_id, $Rev ) &&
		( !strlen( $Rev[1] )  || ( $Rev[1] < 2 ))) {
        header( "Location: /scriptversion.php" );
   } 

    $regex = "/(.*) \((.*)\)/";
    if( preg_match( $regex, $b_id, $matches )) {
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

        if ( preg_match( "/^Mac/i", $comp, $matches )) {
            $mac = 1;
        }
    }

    // We currently don't support MSIE on mac
	// This also catches Opera 7.5 and 8 for mac.
	// Mac Opera 7.5 is broken, Opera 8 works, but they both have the
	// same browser identification string. PUNT! Use a real browser!
    if ( $msie_major && $mac ) {
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
