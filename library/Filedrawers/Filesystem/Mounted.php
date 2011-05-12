<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

abstract class Filedrawers_Filesystem_Mounted extends Filedrawers_Filesystem {
    protected $fsStat = null;
    protected $startCWD = null;
    protected $helpers = array();

    public function __construct()
    {
        $this->startCWD = getcwd();
        $this->fsStat   = stat(Zend_Registry::get('config')->filesystem->services->afs->root);
    }


    public function createDirectory($path, $name)
    {
        $name = basename($name);
        $this->localizePath($path);

        if ( ! @mkdir(trim($name), 0744, true)) {
            throw new Filedrawers_Filesystem_Exception(sprintf(
                'Unable to create the directory "%s".', $name), 5);
        }

        chdir($this->startCWD);
    }


    public function remove($path, $files)
    {
        $deletedCount = 0;
        $files = (array)$files;

        foreach ($files as $file) {
            if(empty($file)){
                // otherwise you will start deleting directories
                continue;
            }

            $itemPath = $path . '/' . $file;

            if ( is_dir($itemPath) && ! is_link( $itemPath )) {
                $this->_removeDir($itemPath);
                $deletedCount++;
            } else {
                $this->localizePath($path);

                if ( ! @unlink( basename($file))) {
                    chdir($this->startCWD);
                    throw new Filedrawers_Filesystem_Exception(sprintf(
                'Unable to remove the file "%s".', basename($file)), 2);
                } else {
                    chdir($this->startCWD);
                    $deletedCount++;
                }
            }
        }

        chdir($this->startCWD);

        if ($deletedCount == 0) {
            throw new Filedrawers_Filesystem_Exception('No files deleted', 2);
        }
    }


    // Remove an existing directory
    // jackylee at eml dot cc
    protected function _removeDir($path)
    {
        $name = basename($path);
        $this->localizePath($path);

        if ( !$handle = @opendir( '.' )) {
            @chdir( $this->startCWD );
            throw new Filedrawers_Filesystem_Exception(sprintf(
                'Unable to remove "%s" because it no longer exists.',
                $name), 404);
        }

        while ( false !== ( $item = readdir( $handle ))) {

            if ( $item == "." || $item == ".." ) {
                continue;
            }

            $itemPath = $path . '/' . $item;

            if (@is_dir($itemPath) && ! @is_link($itemPath)) {
                $this->_removeDir($itemPath);
            } else {
                $this->localizePath($path);
                $base = basename($item);
                if ( ! @is_writable($base) || ! @unlink($base)) {
                    throw new Filedrawers_Filesystem_Exception(sprintf(
                        'Unable to remove the file "%s".', $base), 5);
                }

                chdir( $this->startCWD );
            }
        }

        closedir( $handle );

        $this->localizePath($path);
        $cwdBase = basename(getcwd());

        $rmPath = '../' . $cwdBase;

        if (@is_writable($rmPath) && @rmdir($rmPath)) {
            chdir($this->startCWD);
        } else {
            chdir( $this->startCWD );
            throw new Filedrawers_Filesystem_Exception(sprintf(
                        'Unable to remove the folder "%s".', $cwdBase), 5);
        }
    }


    public function rename($oldPath, $newPath)
    {
        // Don't remove this because filedrawers_rename doesn't check for existing files
        if ($this->_fileExists($newPath)) {
            chdir($this->startCWD);
            throw new Filedrawers_Filesystem_Exception(sprintf('A file or directory with name "%s" already exists.', basename($newPath)), 5);
        }

        if ( ! @filedrawers_rename($oldPath, $newPath,
                    Zend_Registry::get('config')->filesystem->services->afs->root)) {
            chdir($this->startCWD);
            throw new Filedrawers_Filesystem_Exception(sprintf('Unable to rename this file or folder "%s"', basename($newPath)), 5);
        }

        chdir( $this->startCWD );
    }


    public function move($files, $fromPath, $toPath)
    {
        $files = (array)$files;

        foreach ( $files as $file ) {
            if ( empty( $file )) {
                continue;
            }

            $from = $fromPath . '/' . $file;
            $to   = $toPath . '/' . $file;

            try {
                $this->rename($from, $to);
            }
            catch (Filedrawers_Filesystem_Exception $e) {
                throw new Filedrawers_Filesystem_Exception(sprintf('Unable to move "%s"', $file), 5);
            }
        }
    }


    public function duplicate($oldPath, $newPath)
    {
        if ( filetype( $oldPath ) == 'dir' ) {
            try {
                $this->_copyDirectory( $oldPath, $newPath );
            }
            catch (Filedrawers_Filesystem_Exception $e) {
                chdir($this->startCWD);
                throw new Filedrawers_Filesystem_Exception(sprintf('Unable to copy directory "%s"', $oldPath), 5);
            }
        } else {
            try {
                $this->_copyFiles( $oldPath, $newPath );
            }
            catch (Filedrawers_Filesystem_Exception $e) {
                chdir($this->startCWD);
                throw new Filedrawers_Filesystem_Exception(sprintf('Unable to copy file "%s"', $oldPath), 5);
            }
        }
    }


    public function copy($files, $fromPath, $toPath)
    {
        $files = (array) $files;

        foreach ( $files as $file ) {
            if ( empty( $file )) {
                continue;
            }

            // Security checks in Filesystem::_copyFiles() and Filesystem::_copyDirectory
            $from = $fromPath . '/' . $file;
            $to   = $toPath . '/' . $file;

            try {
                $this->duplicate($from, $to);
            } catch (FileDrawers_Filesystem_Exception $e) {
                throw new FileDrawers_Filesystem_Exception(sprintf('Unable to copy "%S"', $file), 5);
            }
        }
    }


    /* A helper function for copy().  Copies an entire directory at once.
     * Original author: swizec at swizec dot com, php.net
     */
    protected function _copyDirectory( $source, $target )
    {
        $this->localizePath(dirname($target));

        $targetCheck = getcwd();

        if ( ! @mkdir( basename( $target ), 0755 )) {
            chdir($this->startCWD);
            throw new Filedrawers_Filesystem_Exception('Copy directory: unable to create new directory', 5);
        }
        
        $this->localizePath($source);

        $destCheck = getcwd();

        // Prevent copying directory inside of itself
        if ( $targetCheck == $destCheck ) {
            chdir($this->startCWD);
            throw new Filedrawers_Filesystem_Exception('Directory cannot be copied inside of itself.', 5);
        }

        $dir = dir( '.' );

        while ( false !== ( $entry = $dir->read())) {
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }

            $sourcePath = $source . '/' . $entry;
            $targetPath = $target . '/' . $entry;

            if ( filetype( $sourcePath ) == 'dir' ) {
                $this->_copyDirectory($sourcePath, $targetPath);
            } else {
                $this->_copyFiles($sourcePath, $targetPath);
            }
        }

        $dir->close();
        chdir($this->startCWD);
    }


    /* A safe version of the PHP copy builtin - this will only copy
     * a file with a source and destination on the specified filesystem device.
     * If we relied on the copy builtin, there is a small possibility of a race
     * condition where the copy could be symlink'ed out of specified device.
     * This function works on file handles only after making sure the source and
     * destination are on the specified device.
     */
    protected function _copyFiles($source, $dest)
    {
        if (is_link($source)) {
            $this->localizePath(dirname($source));

            $name   = basename($source);
            $target = readlink($name);
            
            $this->localizePath(dirname($dest));

            if ( ! @symlink($target, $name)) {
                chdir($this->startCWD);
                throw new Filedrawers_Filesystem_Exception(sprintf('Unable to create symbolic link "%s"', $name), 5);
            }

            chdir($this->startCWD);
        }

        if ( ! ($sourceHdl = @fopen($source, "rb"))) {
            chdir($this->startCWD);
            throw new Filedrawers_Filesystem_Exception(sprintf('Unable to copy "%s". Permission denied.', $name), 3);
        }

        $sourceStat = @fstat($sourceHdl);

        if (is_array($sourceStat) && $sourceStat['dev'] != $this->fsStat['dev']) {
            chdir($this->startCWD);
            throw new Filedrawers_Filesystem_Exception(sprintf('Unable to copy "%s". Permission denied.', $name), 3);
        }
        
        $this->localizePath(dirname($dest));

        // If you want copy to overwrite, then do unlink(basename($dest)) here
        if ( ! ($destHdl = @fopen(basename($dest), "xb"))) {
            chdir($this->startCWD);
            throw new Filedrawers_Filesystem_Exception(sprintf('Unable to copy "%s". Permission denied.', $name), 3);
        }

        while ( ! feof($sourceHdl)) {
            $buffer = fread($sourceHdl, 1024 * 1024);
            fwrite($destHdl, $buffer);
        }

        fclose($sourceHdl);
        fclose($destHdl);
        chdir($this->startCWD);
    }


    public function getFileHandle($path)
    {
        clearstatcache();

        if ( $handle = @fopen($path, "rb")) {
            $stat = @fstat($handle);
            if ( is_array($stat) && $stat['dev'] == $this->fsStat['dev'] ) {
                return $handle;
            }
            else {
                chdir($this->startCWD);
                throw new Filedrawers_Filesystem_Exception(
                    'The specified file or directory does not exist or is inaccessible', 404
                );
                return;
            }
        }
        else {
            throw new Filedrawers_Filesystem_Exception(
                'The specified file or directory does not exist or is inaccessible', 404
            );
        }
    }


    public function addListHelper($function)
    {
        $this->listHelpers[] = $function;
    }


    public function listDirectory($path, $associativeArray=false)
    {
        $files = array();
        $path = (is_file($path)) ? dirname($path) : $path;
        $this->localizePath( $path );
        $files['path'] = $path;
        
        // Open the path and read its contents
        if ( !$dh = @opendir( '.' )) {
            chdir($this->startCWD);
            throw new Filedrawers_Filesystem_Exception(sprintf('Unable to view: %s', $path), 5);
        }
        echo($dh);
        while ( $filename = readdir( $dh )) {
            $row = $this->_getInfo($filename);

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
        @chdir( $this->startCWD );
        return $files;                                    
    }


    public function localizePath($path)
    {
        if ( ! @chdir( $path )) {
            @chdir( $this->startCWD );
            throw new Filedrawers_Filesystem_Exception("Couldn't change directory", 3);
        }

        clearstatcache();
        $stat = stat( '.' );
        if ( $this->fsStat["dev"] != $stat["dev"] ) {
            throw new Filedrawers_Filesystem_Exception("Unable to access path: permission denied.", 3);
        }

        return $stat;
    }


    protected function _fileExists($path)
    {
        clearstatcache();
        return (is_array(@lstat($path))) ? true : false;
    }


    protected function _getInfo($filename, $useListHelpers=true)
    {
        clearstatcache();
        if ( ! $fileStats = @lstat($filename)) {
            $modTime = '';
            $size = 0;
        }
        else {
            $modTime = $fileStats['mtime'];
            $size = $fileStats['size'];
        }

        $info = array(
            'type' => @filetype($filename),
            'filename' => $filename,
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
            if (is_file($path)) {
                $stat = $this->localizePath(dirname($path));
                $file = basename($path);
            }
            else {
                $stat = $this->localizePath($path);
                $file = '.';
            }

            if ( ! $this->_fileExists($file)) {
                return false;
            }

            $info = $this->_getInfo($file, false);
            $info['modifyable'] = @is_writable($file);
            $info['readable'] = @is_readable($file);

            return $info;
        }
        catch (Filedrawers_Filesystem_Exception $e) {
            return false;
        }
    }


 public function listFavs( )
        {
                /* service:name:path: */   
                $favoritesPath = $this->getHomedir() . '/Favorites';
                echo($favoritesPath); 
                $files = $this->listDirectory( $favoritesPath );
?><pre><?
        var_dump($files['contents']);
?></pre><?
                return $files;
                    
        }

       public function addFavs()
	{
		$favoritesPath = $this->getHomedir() . '/Favorites';
                
                $files = $this->listDirectory( $favoritesPath );
                $ginfo = $this->getInfo( $favoritesPath );








	}




}

