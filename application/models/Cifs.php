<?php
/* $Revision: $
 *
 * Copyright (c) 2011 Regents of the University of Michigan.
 * All rights reserved.
 */

class Model_Cifs extends Filedrawers_Filesystem_URL {

    protected $_dirHandle;
    protected $_shareName;
    protected $_path;

    public function __construct()
    {
        if ( ! extension_loaded( 'libsmbclient' )) {
            // throw an exception
        }

        putenv( 'KRB5CCNAME='. $_SERVER[ 'KRB5CCNAME' ] );
        putenv( 'KRB5_CONFIG='. '/etc/krb5-no-default_enctypes.conf' );
    }


    public function setShareName( $shareName )
    {
        $this->_shareName = $shareName;
    }


    public function addListHelper($function)
    {
        $this->listHelpers[] = $function;
    }


    public function getHomedir()
    {
        return '/';
    }


    public function listDirectory($path, $associativeArray=false)
    {
        $this->setPath( $path );

        // Open the path and read its contents
        if ( !$dh = @smbclient_opendir( $this->getUrl())) {
            chdir($this->startCWD);
            throw new Filedrawers_Filesystem_Exception(sprintf('Unable to view: %s', $path), 5);
        }

        while ( $fileinfo = smbclient_readdir( $dh )) {
            $row = $this->_getInfo($fileinfo);

            if ($row === false) {
                continue;
            }

            if ($associativeArray) {
                $files['contents'][$fileinfo[ 'name' ]] = $row;
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

        @smbclient_closedir( $dh );
        return $files;
    }


    protected function _getInfo($fileinfo, $useListHelpers=true)
    {
        clearstatcache();
        if ( ! $fileStats = smbclient_stat( $this->getUrl() . $fileinfo[ 'name' ] )) {
            $modTime = '';
            $size = 0;
        }
        else {
            $modTime = $fileStats['mtime'];
            $size = $fileStats['size'];
        }

        $info = array(
            'type' => $fileinfo[ 'type' ],
            'filename' => $fileinfo[ 'name' ],
            'modTime' => $modTime,
            'size' => $size);

        if ($useListHelpers) {
            foreach($this->listHelpers as $helper) {
                call_user_func_array($helper, array(&$info));
            }
        }

        return $info;
    }

}
