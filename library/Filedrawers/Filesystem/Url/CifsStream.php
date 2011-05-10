<?php


class CifsStream {

	private $_dirHandle;
	
    public function __construct()
    {
        if ( ! extension_loaded( 'libsmbclient' )) {
            // throw an exception
        }

        putenv( 'KRB5CCNAME='. $_SERVER[ 'KRB5CCNAME' ] );
        putenv( 'KRB5_CONFIG='. '/etc/krb5-no-default_enctypes.conf' );
    }


	public function url_stat( $url, $flags )
	{
        $stat = array();
        $stat['dev'] = 0;
        $stat['ino'] = 0;
        $stat['mode'] = 0777;
        $stat['nlink'] = 0;
        $stat['uid'] = 0;
        $stat['gid'] = 0;
        $stat['rdev'] = 0;
        $stat['size'] = 0;
        $stat['atime'] = 0;
        $stat['mtime'] = 0;
        $stat['ctime'] = 0;
        $stat['blksize'] = -1;
        $stat['blocks'] = -1;
		
		$cifsStat = @smbclient_stat( $url );
		return( $cifsStat );
	}

	public function dir_opendir ( $url, $options ) {
		$this->_dirHandle = @smbclient_opendir( $url );	
		return ( $this->_dirHandle !== false );
	}
	public function dir_readdir() 
	{
		$dirInfo = @smbclient_readdir( $this->_dirHandle );
		if ( $dirInfo !== false ) {
			return( $dirInfo['name'] );
		}
		return( false );
	}
	public function dir_closedir()
	{
		return ( @smbclient_closedir( $this->_dirHandle ) );
	}
}

?>

