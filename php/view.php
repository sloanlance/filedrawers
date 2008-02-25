<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'afs.php' );
if ( !isset( $_GET['path'] ) || !strlen( $_GET['path'] )) {
	echo "<p>Error: file not specified</p>\n";
	exit( 1 );
}
$download = new Afs( $_GET['path'] );
$mime = new Mime( );
$mimetype = $mime->getMimeType( $download->path );

switch( $mimetype )
{
	case 'image/jpeg':
	case 'image/gif':
	case 'image/png':
	case 'video/mpeg':
	case 'video/quicktime':
	case 'audio/mpeg':
	case 'application/x-shockwave-flash':
		header( 'Content-Type: '.$mimetype );
		header( 'Content-Length: ' . filesize( $download->path )); 
		header( 'Content-disposition: inline; filename="'.
			$download->filename.'"' );
		$download->readfile();
		break;
	default:
		break;
}

?>
