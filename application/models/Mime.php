<?php
/*
 * Copyright (c) 2005 - 2009 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

define( "DIRMIMETYPE", "0000000dir" );

if ( !extension_loaded( 'fileinfo' )) {
    if ( !dl( 'fileinfo.so' )) {
        error_log( "Couldn't load necessary fileinfo function" );
        echo "<p>Couldn't load necessary fileinfo function</p>\n";
        exit( 1 );
    }
}

class Model_Mime
{
    // Returns the mime type for the file identified in path
    public function getMimeType($filename)
    {
        $finfo = new finfo(FILEINFO_MIME);

        if ( ! $finfo) {
            return '';
        }

        $mimetype = $finfo->file($filename);

		// this is a hack, but people complain, and it's not obvious why
		// excel files are getting msword mimetypes
		if (( $mimetype == 'application/msword' ) &&
			( preg_match( '/.xls$/', $filename ))) {
			$mimetype = 'application/msexcel';
		}

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


    // TODO: Set viewability flag. If filesize is empty, file is not viewable
    // Also, if user does not have read permission, file is not viewable
    public function setIcon(&$row)
    {
        $imagesPath = Config::getInstance()->mime['imagesPath'];
        $filename = $row['filename'];

        if ( is_link( $filename )) {
            $row['mimeImage'] = 'application';
            return true;
        }

        if ( is_dir( $filename )) {
            $row['mimeImage'] = DIRMIMETYPE;
            return true;
        }

        $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ));
        if (file_exists($imagesPath . '/' . $ext . '.gif' )) {
            $row['mimeImage'] = $ext;
            return true;
        }

        $mimeType = self::getMimeType($filename);

        if ( !strlen( $mimeType )) {
            $row['mimeImage'] = 'application';
            return true;
        }

		preg_match( '/^([^\/]+)\/?([^; ]*).*$/', $mimeType, $matches );
		$mClass = $matches[1];
		$mType = $matches[2];

        if ( file_exists( $imagesPath . $mType . '.gif' )) {
            $row['mimeImage'] =  $mType; // It's a file and we have a mime type icon for it
            return true;  
        } else if ( file_exists( $imagesPath . $mClass . '.gif' )) {
            $row['mimeImage'] =  $mClass; // It's a file and we have a mime class icon for it
            return true;
        }

        // The file type is unknown
		// (finfo probably didn't have permission to examine its type)
        $row['mimeImage'] = 'application';
        return true;
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

