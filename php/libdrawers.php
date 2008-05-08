<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'config.php' );

// Standard javascripts
$javascripts 	= array( "/js/filemanage.js" );

// Standard stylesheets
$stylesheets 	= array( "/css/fileman.css" );

// Default value for $displayfileman
$displayfileman	= 1;

// AFS homedir default location.
$afsBase	   	= '/afs/umich.edu/user/';

// Take care of file uploads.
function process_upload( &$notifyMsg, &$errorMsg )
{
	$uploadError = false;
	$errorMsg	 = '';

	if ( ! isset( $_GET['finishid'] ) ||
            preg_match( "/[^a-f0-9]/", $_GET['finishid'] )) {
        return false;
    }

    $db = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );

    if ( mysqli_connect_errno()) {
        return false;
    }

    // There should only ever be one result row
    if ( ! $stmt = $db->prepare( "SELECT filename FROM " .
            "filedrawers_progress WHERE session_id = ? LIMIT 1" )) {
        return false;
    }

    $stmt->bind_param( 's', $_GET['finishid'] );
    $stmt->execute();
    $stmt->bind_result( $filename );
    $stmt->fetch();

    // Check for upload errors
    $filename = trim( $filename );

    if ( strpos( $filename, 'ERROR:' ) === 0 ) {
        if ( strpos( $filename, 'File exists' )) {
            $errorMsg = "One or more files already exist. " .
                    "The upload cannot continue.";
            $uploadError = true;
        } else {
            $errorMsg = "One or more files did not upload sucessfully";
            $uploadError = true;
        }
    } else {
        $notifyMsg = "Successfully received file(s).";
    }

    $stmt->close();

    if ( ! $stmt = $db->prepare( "DELETE FROM filedrawers_progress " .
            "WHERE session_id = ? OR (datediff(NOW(), last_update)) > 5" )) {
        return false;
    }

    $stmt->bind_param( 's', $_GET['finishid'] );
    $stmt->execute();
    $stmt->close();

    $db->close();

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
		$stylesheets[] = "/css/ie5specific.css";
		break;
	case "6":
		$stylesheets[] = "/css/ie6specific.css";
		break;
	default:
		break;
	}
}

/*
 * Get the directory field out of the user's password entry.
 *
 * Returns 1 on success, and sets "dir" with the directory
 *   field out of the user's password entry
 * Returns 0 on failure and sets "error_msg" with a human-readable error.
 * If getpwnam fails, then fall back to getBasePath to construct a directory
 */
function GetHomeDir( $name, &$dir, &$error_msg )
{
	$dir = "";

	if ( empty( $name )) {
		$error_msg .= 'No username supplied';
		return false;
	}

	if ( !extension_loaded( 'posix' ) && !dl( 'posix.so' )) {
		$error_msg .= "Couldn't load necessary posix function. ";
	} else {
		$Pwent = posix_getpwnam( $name );
		if ( empty( $Pwent['dir'] ) || !is_dir( $Pwent['dir'] )) {
			$error_msg .= "Couldn't retrieve $name home directory [" .
				$Pwent['dir'] . ']. ';
		} else {
			$dir = $Pwent['dir'];
		}
	}

	if ( empty( $dir ) || !is_dir( $dir )) {
		$dir = getBasePath( $name );
		if ( empty( $dir ) || !is_dir( $dir )) {
			$error_msg .= 'Could not construct home directory. ';
			$dir = '';
			return false;
		}
	}

	return true;
}

/*
 * This constructs the root of a user's afs space based on his/her uniqname.
 */
function getBasePath( $user )
{
	global $afsBase;

	if ( !$user ) {
		return false;
	}

	# does the username contain illegal characters?
	if ( preg_match( "/[^a-zA-Z]/", $user )) {
		return false;
	}

	return $afsBase . $user[0] . "/" . $user[1] . "/" . $user;
}

