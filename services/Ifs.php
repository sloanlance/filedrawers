<?php
/* $Revision: $
 *
 * Copyright (c) 2011 Regents of the University of Michigan.
 * All rights reserved.
 */

class Service_Ifs extends Filedrawers_Filesystem_Mounted_Afs {
    public function getHomedir()
    {
        // When we're working on our dev server, our home directories are not
        // in AFS so we have to find it.  I'm leaving it in for production.
        // Andrew says our /etc/password is really big.  There will be a delay
        // getting the home dir until the path is cached by nscd and everytime
        // the cache is cleared.  I'd like to see this plugin declared in the
        // ini if possible.
        $config = Zend_Registry::get('config');
        $userInfo = posix_getpwnam(Zend_Auth::getInstance()->getIdentity());

        if ( ! empty($userInfo['dir']) && is_dir($userInfo['dir'])) {
            $homedir = $userInfo['dir'];
        }

        $forceAfsUserDir = $config->filesystem->services->afs->forceAfsUserDir;

        if (strpos($homedir, '/home') === 0) {
            $userSuffix = '/' . $userInfo['name'][0] . '/'.$userInfo['name'][1] . '/' . $userInfo['name'];
            $homedir = $forceAfsUserDir . $userSuffix;
        }

        return $homedir;
    }

	public function getFavPath( )
	{
		$homedir = $this->getHomedir();
        $favoritesPath = $homedir . '/Favorites/';
		return $favoritesPath;
	}

	public function favExists($fav)
	{
		$favoritesPath = $this->getFavPath();
		$fav = $favoritesPath . $fav;

        if ( $this->_fileExists($fav)) 
		{
			return true;
        } else {
			return false;
		}

	}

    public function listFavs( )
    {
        $myFavs = array( 'count' => 0 );
		$favoritesPath = $this->getFavPath();
        $files = $this->listDirectory( $favoritesPath );
    
        for ( $i = 0, $c = 0; $i < count($files['contents']); $i++) {
            foreach( $files['contents'][$i] as $key => $value ) {
                if ( $key == 'filename' ) {
					if ( is_dir($favoritesPath .$value) && ( is_link( $favoritesPath .$value )) ) {
                        $myFavs['contents'][$c]['service'] = 'IFS';
                        $myFavs['contents'][$c]['name'] = $value;
                        $myFavs['contents'][$c]['path'] = realpath( $favoritesPath .$value );
                        $c++;
					} 
                }
            }
        }
        $myFavs['count'] = $c;
        return $myFavs;
    }

	public function addFavs( $path, $name )
	{
		//adding directory
		$name = trim( $name, $this->ILLEGAL_DIR_CHARS );
		$favoritesPath = $this->getFavPath();
        $newFav = $favoritesPath .$name;

        if ( is_dir( $path )) {
            // add symlink
            symlink( $path, $newFav );
		} else {
			// not a dir, fail?
		}
	}

	public function deleteFavs( $name )
	{
		$favoritesPath = $this->getFavPath();
		$filename = $favoritesPath .$name;

		if ( is_link( $filename ) ) {
			if ( ! @unlink($filename) ) {
				throw new Filedrawers_Filesystem_Exception(sprintf(
                        'Unable to remove the file "%s".', $filename), 5);
			}
		}
	}

	public function renameFavs( $oldName, $newName )
	{
		$favoritesPath = $this->getFavPath();
		$newPath = $favoritesPath .$newName;
		$oldPath = $favoritesPath .$oldName;

		if ( !is_link( $oldPath ) ) {
			// do nothing
			throw new Filedrawers_Filesystem_Exception(sprintf(
                'Unable to rename the file "%s".', basename($oldPath)), 2);
		}

		rename( $oldPath, $newPath );
	}

}
