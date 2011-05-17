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
		return( @smbclient_stat( $url ) );
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

	public function rename( $oldPath, $newPath ) 
	{
		return( @smbclient_rename( $oldPath, $newPath ) ); 
	}

	public function unlink( $path )
	{
		//smbclient_unlink appears to return 0 on success
		return ( @smbclient_unlink( $path ) !== false );
	}

	public function rmdir( $path )
	{
		//smbclient_rmdir appears to return 0 on success
		return( @smbclient_rmdir( $path ) !== false );
	}

	public function mkdir( $path, $mode, $recursive = false )
	{
		return ( @smbclient_mkdir( $path, $mode ) !== false ); 
	}

	public function stream_open( $path, $mode, $options, &$opened_path )
	{
		$path = rtrim( $path, "/" );
		return( @smbclient_open( $path ) );  
	}
}

?>

