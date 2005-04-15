<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

define( "DIRMIMETYPE", "0000000dir" );
define( "PATHTOIMGS", "../html-ssl/images/mime/small/" );

if ( !extension_loaded( 'fileinfo' )) {
    dl( 'fileinfo.' . PHP_SHLIB_SUFFIX );
}

if ( !extension_loaded( 'fileinfo' )) {
    error_log( 'mFile: fileinfo extension is not avaliable, please compile it.' );
}

class Mime
{
    // Returns the mime type for the file identified in path
    function getMimeType( $path )
    {
        $res  = finfo_open( FILEINFO_MIME );
        $mime = finfo_file( $res, $path );

        finfo_close( $res );

        // Eliminate any warning messages from finfo that could cause js problems
        return ( strpos( $mime, ' ' ) !== false ) ? '' : $mime;
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
        $mime = Mime::getMimeType( $path );

        if ( !$mime ) {
            return 'application';
        }

        list( $mimeClass, $mimeType ) = explode( '/', $mime );

        if ( file_exists( PATHTOIMGS . $mimeType . '.gif' )) {
            return $mimeType;  // It's a file and we have a mime type icon for it
        } else if ( file_exists( PATHTOIMGS . $mimeClass . '.gif' )) {
            return $mimeClass;  // It's a file and we have a mime class icon for it
        }

        // The file type is unknown (finfo probably didn't have permission to examine its type)
        return 'application';
    }
}
