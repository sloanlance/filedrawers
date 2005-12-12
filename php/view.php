<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'afs.php' );
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
	#text/plain has multiple endings
	case ( preg_match( '/^text\/plain/', $mimetype ) == TRUE ):
		header( 'Content-Type: '.$mimetype );
		header( 'Content-Length: ' . filesize( $download->path )); 
		header( 'Content-disposition: inline; filename="'.
			$download->filename.'"' );
		@readfile( $download->path );
		break;
	default:
		require( 'index.php' );
		break;
}

?>
