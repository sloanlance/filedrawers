<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

abstract class Filesystem {
    protected $fsStat    = null;
    protected $startCWD  = null;
    protected $helpers   = array();
    public    $errorMsg  = null;
    public    $notifyMsg = null;


    public function __construct()
    {
        $this->startCWD = getcwd();
        $this->fsStat   = stat(Config::getInstance()->filesystem['root']);
    }


    public function createDirectory($path, $name)
    {
        $name = basename($name);

        if ( ! $this->localizePath($path)) {
            // TODO: Throw exception here
            exit('unable to create directory (localize path)');
        }

        if ( $this->linkSafeFileExists($name)) {
            $this->errorMsg = "The folder \'$name\' " .
                "already exists. Please select a different name.";
            @chdir($this->startCWD);
            return false;
        }

        if ( ! mkdir(trim($name), 0644, true)) {
            $this->errorMsg = 'Unable to create folder.';
            @chdir($this->startCWD);
            return false;
        }

        @chdir($this->startCWD);
        return true;
    }


    // Remove an existing directory
    // jackylee at eml dot cc
    public function removeDirectory($path)
    {
        if ( ! $this->localizePath($path)) {
            return false;
        }

        if ( !$handle = @opendir( '.' )) {
            $this->errorMsg = 'Unable to remove the folder because ' .
                'it no longer exists.';
            @chdir( $this->startCWD );
            return false;
        }

        while ( false !== ( $item = readdir( $handle ))) {

            if ( $item == "." || $item == ".." ) {
                continue;
            }

            $itemPath = $path . '/' . $item;

            if ( is_dir( $itemPath ) && ! is_link( $itemPath )) {                
                if ( !$this->removeDirectory( $itemPath )) {
                    @chdir( $this->startCWD );
                    return false;
                }
            } else {
                if ( !$this->localizePath( $path )) {
                    @chdir( $this->startCWD );
                    return false;
                }

                unlink( basename( $item ));
                @chdir( $this->startCWD );
            }
        }

        closedir( $handle );

        if ( !$this->localizePath( $path )) {
            @chdir( $this->startCWD );
            return false;
        }

        if ( rmdir( '../' . basename( getcwd()))) {
            $this->notifyMsg = "Successfully deleted file(s).";
            @chdir( $this->startCWD );
            return true;
        }

        @chdir( $this->startCWD );
        $this->errorMsg = 'Unable to remove the folder.';
        return false;
    }


    public function deleteFiles($path, $files)
    {
        $files = (array)$files;

        foreach ($files as $file) {
            // Security checks are in Filesystem::removeDirectory()
            $itemPath = $path . '/' . $file;

            if ( is_dir($itemPath) && ! is_link( $itemPath )) {
                if ( ! $this->removeDirectory( $itemPath )) {
                    return false;
                }
            } else {
                if ( ! $this->localizePath( $path )) {
                    return false;
                }

                if ( !@unlink( basename( $file ))) {
                    @chdir( $this->startCWD );
                    $this->errorMsg = "Unable to delete $file.";
                    return false;
                } else {
                    @chdir( $this->startCWD );
                    $this->notifyMsg = "Successfully deleted file(s).";
                }
            }
        }

        @chdir( $this->startCWD );
        return true;
    }


    public function rename($oldPath, $newPath)
    {
        // Security checks are in filedrawers_rename
        if (is_link($oldPath)) {
            $this->errorMsg = "Symbolic links cannot be renamed or moved.";
            @chdir($this->startCWD);
            return false;
        }

        if ($this->linkSafeFileExists($newPath)) {
            $this->errorMsg = "The file or folder '" . basename($newPath) .
                "' already exists.";
            @chdir($this->startCWD);
            return false;
        }

        if ( ! @filedrawers_rename($oldPath, $newPath,
                    Config::getInstance()->filesystem['root'])) {
            $this->errorMsg = 'Unable to rename this file or folder.';
            @chdir( $this->startCWD );
            return false;
        }
        
        $this->notifyMsg = "Successfully renamed the file or folder.";

        @chdir( $this->startCWD );
        return true;
    }


    public function moveFiles($files, $fromPath, $toPath)
    {
        $files = (array) $files;

        foreach ( $files as $file ) {
            if ( empty( $file )) {
                continue;
            }

            // Security checks are in Filesystem::rename
            $from = $fromPath . '/' . $file;
            $to   = $toPath . '/' . $file;

            if ( ! $this->rename( $from, $to,
                        Config::getInstance()->filesystem['root'])) {
                $this->errorMsg = "Unable to move: $file.";
                return false;
            }
        }

        return true;
    }


    function copyFiles($files, $fromPath, $toPath)
    {
        $files = (array) $files;

        foreach ( $files as $file ) {
            if ( empty( $file )) {
                continue;
            }

            // Security checks in Filesystem::copy() and Filesystem::copyDirectory
            $from = $fromPath . '/' . $file;
            $to   = $toPath . '/' . $file;

            if ( filetype( $sourcePath ) == 'dir' ) {
                if ( ! $this->copyDirectory( $from, $to )) {
                    $this->errorMsg = "Unable to copy $file.";
                    return false;
                }
            } else if ( !$this->copy( $from, $to )) {
                $this->errorMsg = "Unable to copy $file.";
                return false;
            }
        }
    }


    /* A helper function for copyFiles().  Copies an entire directory at once.
     * Original author: swizec at swizec dot com, php.net
     */
    public function copyDirectory( $source, $target )
    {
        if ( !$this->localizePath( dirname( $target ))) {
            return false;
        }

        $targetCheck = getcwd();

        if ( !@mkdir( basename( $target ), 0755 )) {
            @chdir( $this->startCWD );
            return false;
        }

        if ( !$this->localizePath( $source )) {
            @chdir( $this->startCWD );
            return false;
        }

        $destCheck = getcwd();

        // Prevent copying directory inside of itself
        if ( $targetCheck == $destCheck ) {
            @chdir( $this->startCWD );
            return false;
        }

        $dir = dir( '.' );

        while ( false !== ( $entry = $dir->read())) {
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }

            $sourcePath = $source . '/' . $entry;
            $targetPath = $target . '/' . $entry;

            // Security checks in Filesystem::copy() and Filesystem::copyDirectory
            if ( filetype( $sourcePath ) == 'dir' ) {
                if ( !$this->copyDirectory( $sourcePath, $targetPath )) {
                    @chdir( $this->startCWD );
                    return false;
                }
            } else if ( !$this->copy( $sourcePath, $targetPath )) {
                @chdir( $this->startCWD );
                return false;
            }
        }

        $dir->close();
        @chdir( $this->startCWD );
        return true;
    }


    /* A safe version of the PHP copy builtin - this will only copy
     * a file with a source and destination on the specified filesystem device.
     * If we relied on the copy builtin, there is a small possibility of a race
     * condition where the copy could be symlink'ed out of specified device.
     * This function works on file handles only after making sure the source and
     * destination are on the specified device.
     */
    public function copy($source, $dest)
    {
        if (is_link($source)) {
            if ( ! $this->localizePath(dirname($source))) {
                return false;
            }

            $name   = basename($source);
            $target = readlink($name);

            if ( ! $this->localizePath(dirname($dest))) {
                return false;
            }

            if ( ! symlink($target, $name)) {
                @chdir($this->startCWD);
                return false;
            }

            @chdir($this->startCWD);
            return true;
        }

        if ( ! ($sourceHdl = @fopen($source, "rb"))) {
            @chdir($this->startCWD);
            return false;
        }

        $sourceStat = fstat($sourceHdl);

        if ($sourceStat['dev'] != $this->fsStat['dev']) {
            @chdir($this->startCWD);
            return false;
        }

        if ( ! $this->localizePath(dirname($dest))) {
            return false;
        }

        // If you want copy to overwrite, then do unlink(basename($dest)) here
        if ( ! ($destHdl = @fopen(basename($dest), "xb"))) {
            @chdir($this->startCWD);
            return false;
        }

        while ( ! feof($sourceHdl)) {
            $buffer = fread($sourceHdl, 1024 * 1024);
            fwrite($destHdl, $buffer);
        }

        @fclose($sourceHdl);
        @fclose($destHdl);
        @chdir($this->startCWD);

        return true;
    }


    // A safe version of the PHP readfile builtin - this will only
    // read files which are stored on a specified filesystem device.
    public function readfile($path)
    {
        clearstatcache();

        if ( $handle = @fopen( $path, "rb" )) {
            $stat = fstat( $handle );
            if ( $stat['dev'] == $this->fsStat['dev'] ) {
                while ( !feof( $handle )) {
                    $buffer = fread( $handle, 1024 * 1024 );
                    echo $buffer;
                }
            }

            @fclose( $handle );
        }
    }


    public function getSize($path)
    {
        $size = 0;
        clearstatcache();

        if ($handle = @fopen($path, "rb")) {
            $stat = fstat($handle);
            if ($stat['dev'] == $this->fsStat['dev']) {
                $size = $stat['size'];
            }

            @fclose($handle);
        }

        return $size;
    }


    public function addListHelper($function)
    {
        $this->listHelpers[] = $function;
    }


    public function listDirectory($path)
    {
        $files = array();

        if ( is_file( $path )) {
            $path = dirname( $path );
        }
 
        if ( !$this->localizePath( $path )) {
            $this->errorMsg = "Unable to view: $path.";
            return false;
        }

        $files['path'] = $path;

        if ( !@is_dir( '.' )) {
            @chdir( $this->startCWD );
            return false;
        }

        // Open the path and read its contents
        if ( !$dh = @opendir( '.' )) {
            $this->errorMsg = "Unable to view: $path.";
            @chdir( $this->startCWD );
            return false;
        }

        while ( $filename = readdir( $dh )) {
            clearstatcache();
            if ( !$fileStats = lstat( $filename )) {
                $modTime = '';
                $size = 0;
            } else {
                $modTime = $fileStats['mtime'];
                $size = $fileStats['size'];
            }


            $row = array(
                'filename' => $filename,
                'modTime' => $modTime,
                'size' => $size);

            foreach($this->listHelpers as $helper) {
                call_user_func_array($helper, array(&$row));
            }

            if (is_array($row)) {
                $files['contents'][] = $row;
            }
        }

        function naturalSortByName($a, $b) {
            return strnatcasecmp($a['filename'], $b['filename']);
        }

        usort($files['contents'], 'naturalSortByName');

        closedir( $dh );
        @chdir( $this->startCWD );
        return $files;                                    
    }


    public function localizePath( $path )
    {
        if ( ! @chdir( $path )) {
            $this->errorMsg = "Couldn't change directory";
            @chdir( $this->startCWD );
            return false;
        }

        clearstatcache();
        $stat = stat( '.' );
        if ( $this->fsStat["dev"] != $stat["dev"] ) {
            $this->errorMsg = "Unable to access path: permission denied.";
            @chdir( $this->startCWD );
            return false;
        }

        return true;
    }


    public function linkSafeFileExists( $path )
    {
        clearstatcache();

        if ( is_array( @lstat( $path ))) {
            return true;
        } else {
            return false;
        }
    }
}
