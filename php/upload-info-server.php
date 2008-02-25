<?php

require_once( 'config.php' );

$fn = trim( $_REQUEST['filename_'] );
if ( preg_match( "/[^a-f0-9]/", $fn )) {
	echo htmlentities( "filename is not valid [$fn]\n" );
	exit();
}

/*
 * Note: this may not work if mod_security is used with the
 * SecRequestBodyAccess option turned on. This is because the file is uploaded
 * to tmp using a different filename than the string generated for us. This
 * file format looks like this:
 * 20070419-163138-75.45.215.95-request_body-zutdsO where this script is
 * looking for a tmp file that looks like this: 1ad60b47035b651643df9e712ec24ac7
 */
echo @file_get_contents( $upload_session_tmp . '/' . $fn );

?>
