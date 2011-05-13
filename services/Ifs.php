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
                    if ( ( is_link( $favoritesPath .$value )) ) {
                        $myFavs['contents'][$c]['service'] = 'IFS';
                        $myFavs['contents'][$c]['name'] = $value;
                        $myFavs['contents'][$c]['path'] = readlink( $favoritesPath .$value );
                        $c++;
                    } elseif ( !(is_dir($favoritesPath .$value))) {
						$myFavs['contents'][$c]['service'] = 'IFS';
						$myFavs['contents'][$c]['name'] = $value;
						$myFavs['contents'][$c]['path'] = $homedir;
						$c++;
					} 
                }
            }
        }
        $myFavs['count'] = $c;
        return $myFavs;
    }

}
