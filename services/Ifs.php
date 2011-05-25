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

    public function listFavs( )
    {
        $myFavs = array( 'count' => 0 );
		$homedir = $this->getHomedir(); 
        $favoritesPath = $homedir . '/Favorites/';
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
		$homedir = $this->getHomedir();
		$favoritesPath = $homedir . '/Favorites';
        $newFav = $favoritesPath .'/'.$name;


		if ($this->_fileExists($newFav)) {
			// ??
			throw new Filedrawers_Filesystem_Exception(sprintf(
            	'A Fav with this name already exists "%s".', $newFav), 5);
		}


        if ( is_dir( $path )) {
            // add symlink
            symlink( $path, $newFav );
		} else {
			// not a dir, fail?
		}
	}

	public function deleteFavs( $name ) {
		$homedir = $this->getHomedir();
        $favoritesPath = $homedir . '/Favorites';
		$filename = $favoritesPath .'/'.$name;
		if ( is_link( $filename ) ) {
			if ( ! @unlink($filename) ) {
				throw new Filedrawers_Filesystem_Exception(sprintf(
                        'Unable to remove the file "%s".', $filename), 5);
			}
		}
	}

	public function renameFavs( $oldPath, $newPath )
	{
		if ( !is_link( $oldPath ) ) {
			// do nothing
			throw new Filedrawers_Filesystem_Exception(sprintf(
                'Unable to rename the file "%s".', basename($oldPath)), 2);
		}

		rename( $oldPath, $newPath );
	}

}
