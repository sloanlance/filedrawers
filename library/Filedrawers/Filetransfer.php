<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

require_once 'Filedrawers/Filetransfer/Exception.php';

class Filedrawers_Filetransfer {
    protected $_handle   = NULL; 
    protected $_filename = NULL;
    protected $_stats    = NULL;

    public function __construct( $handle, $filename )
    {
        $this->_handle = $handle;
        $this->_filename = $filename;
        $this->_stats = @fstat($this->_handle);

        if ( ! is_array($this->_stats)) {
            throw new Filedrawers_Filetransfer_Exception(
                'The specified file to transfer does not exist or is inaccessible.', 404
            );
        }
    }


    public function send()
    {
        $range = array();
        $rangeIsSet = isset( $_SERVER['HTTP_RANGE'] );
        $bytesread = 0;

        if ( isset( $_SERVER['HTTP_IF_RANGE'] )) {
            /* XXX should support ETag, too */
            if ( $_SERVER['HTTP_IF_RANGE'] !=
                    date('D, m M Y H:i:s O', $this->_stats['mtime'])) {
                $rangeIsSet = false;
            }
        }

        if ( $rangeIsSet ) {
            $range = $this->getRanges( $_SERVER['HTTP_RANGE'] );
            if ( empty( $range )) {
                header( 'HTTP/1.0 416 Requested Range Not Satisfiable' );
                return;
            }

            header('HTTP/1.1 206 Partial Content');
            header('Accept-Ranges: bytes');
            header('Content-Range: bytes ' . $range['start'] . '-' .
                    $range['end'] . '/' . $this->_stats['size']);
            header('Content-Length: '.($range['end'] - $range['start']));
        }
        else {
            header('Content-Length: ' . $this->_stats['size']);
        }

        $name = str_replace('"', '\"', $this->_filename);

        header('Content-Description: File Transfer');
        header("Content-Disposition: attachment; filename=\"$name\";");
        header('Content-Type: application/force-download' );
        header("Last-Modified: " . date('D, m M Y H:i:s O', $this->_stats['mtime']));

        while ( !feof( $this->_handle )) {
            set_time_limit(0);

            if ( isset( $range['start'] )) {
                $chunksize = max( 1, min( $this->_stats['size'] - $range['start'],
                        $this->_stats['size'] - $bytesread, 1024 * 1024 ));
            }
            else {
                $chunksize = 1024 * 1024;
            }

            echo fread( $this->_handle, $chunksize );
            $bytesread += $chunksize;

            flush();
            ob_flush();
        }

        fclose( $this->_handle );
    }


    protected function getRanges( $rangeHeader )
    {
       /*
        * this needs to support all possible range formats. for the
        * first pass, we're only implementing "bytes=start-[end]".
        *
        * return an array of ranges if the Range header is valid,
        * false otherwise.
        */
        
        if ( !preg_match( "/^bytes=(\d+)-(\d*)/i", $rangeHeader, $matches )) {
            return( false );
        }


        $ranges = array();
        if ( isset( $matches[ 1 ] )) {
            if ( (int)$matches[ 1 ] >= $this->_stats['size'] ) {
                return( false );
            }
            $ranges['start'] = $matches[ 1 ];
        }

        if ( isset( $matches[ 2 ] )) {
            if ( (int)$matches[ 2 ] > $this->_stats['size'] ) {
                return( false );
            }
            $ranges['end'] = $matches[ 2 ];
        }

        return( $ranges );
    }
}

