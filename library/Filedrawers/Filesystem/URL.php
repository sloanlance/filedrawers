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

    public function getUrl( $path = NULL ) {
        $url = $this->_scheme .'://';
        if ( ! empty( $this->_user )) {
            $url .= $this->_user;
            if ( ! empty( $this->_pass )) {
                $url .= ':'. $this->_pass;
            }
            $url .= '@';
        }
        $url .= $this->_host;

        if ( $path === NULL ) {
            $path = $this->_path;
        }

        $url .= '/'. ltrim( $path, '/' );

        return $url;
    }
}
