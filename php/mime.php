<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

define( "DIRMIMETYPE", "0000000dir" );
define( "PATHTOIMGS", "/images/mime/small/" );

if ( !extension_loaded( 'fileinfo' )) {
    dl( 'fileinfo.' . PHP_SHLIB_SUFFIX );
}

if ( !extension_loaded( 'fileinfo' )) {
    error_log( 'fileinfo extension is not avaliable, please compile it.' );
}

class Mime
{
    // Returns the mime type for the file identified in path
    function getMimeType( $path )
    {
        $res = finfo_open( FILEINFO_MIME );
        $mimetype = finfo_file( $res, $path );
        finfo_close( $res );

		# this is a hack, but people complain, and it's not obvious why
		# excel files are getting msword mimetypes
		if (( $mimetype == 'application/msword' ) &&
			( preg_match( '/.xls$/', $path ))) {
			$mimetype = 'application/msexcel';
		} 

        // Eliminate any warning messages from finfo that could cause js problems
        // return ( strpos( $mimetype, ' ' ) !== false ) ? '' : $mimetype;
        return $mimetype;
    }

    // Returns the name of an appropriate mime icon
    function mimeIcon( $path )
    {
        // This is a folder
        if ( @is_dir( $path )) {
            return DIRMIMETYPE;
        }

        $ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ));
        if ( file_exists( PATHTOIMGS . $ext . '.gif' )) {
            return $ext;
        }

        // Determine mime class and type
        $mimetype = Mime::getMimeType( $path );

        if ( !strlen( $mimetype )) {
            return 'application';
        }
		preg_match( '/^([^\/]+)\/?([^; ]*).*$/', $mimetype, $Matches );
		$mClass = $Matches[1];
		$mType = $Matches[2];

        if ( file_exists( '../'.PATHTOIMGS . $mType . '.gif' )) {
            return $mType;  // It's a file and we have a mime type icon for it
        } else if ( file_exists( '../'.PATHTOIMGS . $mClass . '.gif' )) {
            return $mClass;  // It's a file and we have a mime class icon for it
        }

        // The file type is unknown 
		// (finfo probably didn't have permission to examine its type)
        return 'application';
    }
}
