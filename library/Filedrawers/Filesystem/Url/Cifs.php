<?php
/* $Revision: $
 *
 * Copyright (c) 2011 Regents of the University of Michigan.
 * All rights reserved.
 */

require_once( "CifsStream.php" );

class Filedrawers_Filesystem_Url_Cifs extends Filedrawers_Filesystem_Url {

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
		stream_wrapper_register( "smb", "CifsStream" );
    }


    public function setShareName( $shareName )
    {
        $this->_shareName = $shareName;
    }


    public function addListHelper($function)
    {
        $this->listHelpers[] = $function;
    }

}
