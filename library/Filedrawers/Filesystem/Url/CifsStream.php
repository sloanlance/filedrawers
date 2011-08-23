<?php


class CifsStream {

	private $_dirHandle;
	private $_fileHandle;
	private $_fileSize;
	private $_filePos;
	
    public function __construct()
    {
        if ( ! extension_loaded( 'libsmbclient' )) {
            // throw an exception
        }

        putenv( 'KRB5CCNAME='. $_SERVER[ 'KRB5CCNAME' ] );
        putenv( 'KRB5_CONFIG='. '/etc/krb5-no-default_enctypes.conf' );
		$this->_fileSize = 0;
		$this->_filePos = 0;
    }


	public function url_stat( $url, $flags )
	{
		return @smbclient_stat( $url );
	}

	public function dir_opendir ( $url, $options ) {
		$this->_dirHandle = @smbclient_opendir( $url );	
		return $this->_dirHandle !== false;
	}

	public function dir_readdir() 
	{
		$dirInfo = @smbclient_readdir( $this->_dirHandle );
		if ( $dirInfo !== false ) {
			return $dirInfo['name'];
		}
		return false;
	}

	public function dir_closedir()
	{
		$retval = @smbclient_closedir( $this->_dirHandle );
		unset( $this->_dirHandle );
		return $retval;
	}

	public function rename( $oldPath, $newPath ) 
	{
		return @smbclient_rename( $oldPath, $newPath ); 
	}

	public function unlink( $path )
	{
		//smbclient_unlink appears to return 0 on success
		return @smbclient_unlink( $path ) !== false;
	}

	public function rmdir( $path )
	{
		//smbclient_rmdir appears to return 0 on success
		return @smbclient_rmdir( $path ) !== false;
	}

	public function mkdir( $path, $mode, $recursive = false )
	{
		return @smbclient_mkdir( $path, $mode ) !== false; 
	}

	public function stream_open( $path, $mode, $options, &$opened_path )
	{
		$path = rtrim( $path, "/" );
		$this->_fileHandle= smbclient_open( $path, $mode );
		return $this->_fileHandle !== false;
	}

	public function stream_stat( )
	{
		$retval =  smbclient_fstat( $this->_fileHandle);
		$this->_fileSize = $retval[ 'size' ];
		return $retval;
	
	}

	public function stream_read( $count )
	{
		$data = smbclient_read( $this->_fileHandle, $count );
		$this->_filePos = $this->_filePos + strlen( $data );
		if ( $this->_filePos == $this->_fileSize ) $this->_filePos++;
		return $data;
	}

        public function stream_write( $data )
        {
                $bytesWritten = smbclient_write( $this->_fileHandle, $data, 1024 );
                $this->_filePos = $this->_filePos + $bytesWritten;
                return $bytesWritten;
        }

	public function stream_eof()
	{
		return $this->_filePos > $this->_fileSize;
	}

	public function close()
	{
		smbclient_close( $this->_fileHandle );
	}
}



