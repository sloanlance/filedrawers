<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

abstract class Filedrawers_Filesystem_URL extends Filedrawers_Filesystem {
    protected $_scheme = 'file';
    protected $_host = '';
    protected $_port = NULL;
    protected $_user = '';
    protected $_pass = '';
    protected $_path = '/';

    public function setHost( $host ) {
        $this->_host = $host;
    }

    public function setPort( $port ) {
        $this->_port = $port;
    }

    public function setUser( $user ) {
        $this->_user = $user;
    }

    public function setPath( $path ) {
        $this->_path = $path;
    }

    public function getUrl( $file = NULL ) {
        $url = $this->_scheme .'://';
        if ( ! empty( $this->_user )) {
            $url .= $this->_user;
            if ( ! empty( $this->_pass )) {
                $url .= ':'. $this->_pass;
            }
            $url .= '@';
        }
        $url .= $this->_host;

        $url .= '/'. ltrim( $this->_path, '/' );

        if ( ! empty( $file )) {
            $url = rtrim( $url, '/' ) . '/' .$file;
        }

        return trim( $url );
    }


    public function createDirectory($path, $name)
    {
        $this->setPath( $path );
        $name = basename($name);

        if ( ! mkdir($this->getUrl( $name ), 0744, true)) {
            throw new Filedrawers_Filesystem_Exception(sprintf(
                'Unable to create the directory "%s".', $name), 5);
        }
    }


    public function remove($path, $files)
    {
        $this->setPath( $path );
        $files = (array)$files;
        $deletedCount = 0;

        foreach ($files as $file) {
            if(empty($file)){
                // otherwise you will start deleting directories
                continue;
            }

            $url = $this->getUrl( $file );

            if ( is_dir($url) && ! is_link( $url )) {
                $this->_removeDir($url);
                $deletedCount++;
            } else {
                if ( ! @unlink( $url )) {
                    throw new Filedrawers_Filesystem_Exception(sprintf(
                'Unable to remove the file "%s".', basename($file)), 2);
                } else {
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount == 0) {
            throw new Filedrawers_Filesystem_Exception('No files deleted', 2);
        }
    }


    // Remove an existing directory
    // jackylee at eml dot cc
    protected function _removeDir($url)
    {
        $name = basename($url);

        if ( !$handle = @opendir( $url )) {
            throw new Filedrawers_Filesystem_Exception(sprintf(
                'Unable to remove "%s" because it no longer exists.',
                $name), 404);
        }

        while ( false !== ( $item = readdir( $handle ))) {

            if ( $item == "." || $item == ".." ) {
                continue;
            }

            $itemUrl = ltrim( $url, '/' ) .'/'. $item;

            if (@is_dir($itemUrl) && ! @is_link($itemUrl)) {
                $this->_removeDir($itemUrl);
            } else {
                if ( ! @is_writable($itemUrl) || ! @unlink($itemUrl)) {
                    throw new Filedrawers_Filesystem_Exception(sprintf(
                        'Unable to remove the file "%s".', basename( $itemUrl )), 5);
                }
            }
        }

        closedir( $handle );

        if (is_writable($url) && $this->_rmdir($url)) {
        } else {
            throw new Filedrawers_Filesystem_Exception(sprintf(
                        'Unable to remove the folder "%s".', $url ), 5);
        }
    }

    protected function _rmdir( $url )
    {
        return rmdir( $url );
    }



    public function addListHelper($function)
    {
        $this->listHelpers[] = $function;
    }


    public function listDirectory($path, $associativeArray=false)
    {
        $this->setPath( $path );
        $url = $this->getUrl();
        $url = (is_file($url)) ? dirname($url) : $url;

        $files = array();
        $files['path'] = $path;

        // Open the path and read its contents
        if ( !$dh = @opendir( $url )) {
            throw new Filedrawers_Filesystem_Exception(sprintf('Unable to view: %s', $path), 5);
        }

        while ( $filename = readdir( $dh )) {
            $row = $this->_getInfo($this->getUrl( $filename ));

            if ($row === false) {
                continue;
            }

            if ($associativeArray) {
                $files['contents'][$filename] = $row;
            } else {
                $files['contents'][] = $row;
            }
        }

        function naturalSortByName($a, $b) {
            return strnatcasecmp($a['filename'], $b['filename']);
        }

        // Some list helpers may disable filename
        if (isset($files['contents'][0]['filename'])) {
            usort($files['contents'], 'naturalSortByName');
        }

        @closedir( $dh );
        return $files;
    }


    protected function _fileExists($url)
    {
        clearstatcache();
        return (is_array(@lstat($url))) ? true : false;
    }


    protected function _getInfo($url, $useListHelpers=true)
    {
        clearstatcache();
        if ( ! $fileStats = @lstat($url)) {
            $modTime = '';
            $size = 0;
        }
        else {
            $modTime = $fileStats['mtime'];
            $size = $fileStats['size'];
        }

        $info = array(
            'type' => filetype($url),
            'filename' => basename( $url ),
            'modTime' => $modTime,
            'size' => $size);

        if ($useListHelpers) {
            foreach($this->listHelpers as $helper) {
                call_user_func_array($helper, array(&$info));
            }
        }

        return $info;
    }


    public function getInfo($path)
    {

        try {
            $this->setPath( $path );
            $url = $this->getUrl();

            if ( ! $this->_fileExists($url)) {
                return false;
            }

            $info = $this->_getInfo($url, false);
            $info['modifyable'] = @is_writable($url);
            $info['readable'] = @is_readable($url);

            return $info;
        }
        catch (Filedrawers_Filesystem_Exception $e) {
            return false;
        }
    }
}
