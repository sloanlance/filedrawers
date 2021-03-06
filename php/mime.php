<?php
/*
 * Copyright (c) 2005 - 2009 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

define( "DIRMIMETYPE", "0000000dir" );

if ( !extension_loaded( 'fileinfo' )) {
    dl( 'fileinfo.' . PHP_SHLIB_SUFFIX );
}

if ( !extension_loaded( 'fileinfo' )) {
    error_log( 'fileinfo extension is not avaliable, please compile it.' );
}

class Mime
{
    // Returns the mime type for the file identified in path
    public function getMimeType( $path )
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
    public function getIcon( $mimeType, $path )
    {
        if ( @is_link( $path )) {
            return 'application';
        }

        if ( @is_dir( $path )) {
            return DIRMIMETYPE;
        }

        $ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ));
        if ( file_exists( PATHTOIMGS . $ext . '.gif' )) {
            return $ext;
        }

        if ( !strlen( $mimeType )) {
            return 'application';
        }
		preg_match( '/^([^\/]+)\/?([^; ]*).*$/', $mimeType, $matches );
		$mClass = $matches[1];
		$mType = $matches[2];

        if ( file_exists( PATHTOIMGS . $mType . '.gif' )) {
            return $mType;  // It's a file and we have a mime type icon for it
        } else if ( file_exists( PATHTOIMGS . $mClass . '.gif' )) {
            return $mClass;  // It's a file and we have a mime class icon for it
        }

        // The file type is unknown 
		// (finfo probably didn't have permission to examine its type)
        return 'application';
    }


    public function getPreviewType( $mimeType )
    {
        preg_match( '/^([^\/]+)\/?([^; ]*).*$/', $mimeType, $matches );
        $mType     = $matches[1];
        $mSubtype  = $matches[2];

        if ( $mType == 'image' && $mSubtype != 'bmp'
                && $mSubtype != 'vnd.adobe.photoshop' ) {
            return 'image';
        }

        if ( $mType == 'audio' || $mType == 'video' ||
                ( $mType == 'application' && $mSubtype == 'x-shockwave-flash' )) {
            return 'embed';
        }

        if ( $mType == 'html' || ( $mType == 'text' && $mSubtype != 'rtf' )) {
            return 'text';
        }

        return false;
    }
}

