<?php
/* $Revision: $
 *
 * Copyright (c) 2011 Regents of the University of Michigan.
 * All rights reserved.
 */

class Service_MainstreamStorage extends Filedrawers_Filesystem_Url_Cifs {

    public function getUrl( $filename = null )
    {
        return( $url = 'smb://'. $this->_shareName .'.m.storage.umich.edu'.$this->pathConcat( $this->_path, $filename ) );
    }

    public function getUniqname()
    {
        $userInfo = posix_getpwnam(Zend_Auth::getInstance()->getIdentity());
        return $uniqname = $userInfo['name'];
    }


    public function setPath( $path )
    {
        if ( ! preg_match( '|^/|', $path )) {
            // invalid path
            // throw exception
            return;
        }
        $pathParts = explode( '/', $path );

        $this->setShareName( $pathParts[ 1 ] );
        $this->_path = $path;
    }

    public function favoriteExists($favorite){

        $testArr = explode('/',$favorite);
        $foldername = array_pop($testArr);
        $path = implode('/', $testArr); 
        $uniqname = $this->getUniqname();
        $favsTbl = new Model_UserFavorites;
        $clause =  $favsTbl->getAdapter()->quoteInto("username =?", $uniqname) . $favsTbl->getAdapter()->quoteInto("AND location =?", $path);
 
        $validator = new Zend_Validate_Db_NoRecordExists(
          array(
              'table' => 'filedrawers_favorites',
              'field' => 'foldername',
              'exclude' => $clause
          )
      );
      
        if ($validator->isValid($foldername)) {
            //favs does not exist
            return false;
      } else {
             //favs already exists.
             return true;
      }

    }


    public function listDirectory($path, $associativeArray=false)
    {
        if ( $path == '/' ) {
            $fake = array( 'ath-groups', 'DEV-SHARE', 'FIN-IO-Department', 'FIN-RM-Oasis_Storage', 'kines-temp', 'kines-users', 'nur-mainstreamroot', 'snre-cafi', 'snre-communications', 'snre-diana', 'snre-jpnewelllab', 'snre-esa', 'kines-sml', 'ulib-staff', 'kines-adidas', 'kines-cmbds', 'kines-vbl', 'kines-borer', 'cgh-root', 'chgd-Research', 'kines-gross', 'kines-mml', 'tri-vehsafety', 'kines-sportmgt', 'mmpei-adminsvr', 'kines-groups', 'kines-neuro', 'snre-gesi', 'kines-complex', 'bhl-archive', 'bhl-root', 'EA-PHOTOLIB', 'SMTD-BlockM', 'kines-brown', 'ssw-groups', 'ssw-users', 'nur-data', 'ENGIN-InterproShare', 'kines-hsl', 'snre-bayesian', 'snre-scavialab', 'IOE-CHEPS', 'dsp-digisign', 'its-users', 'snre-cardinalelab', 'EA-COMM-MARK', 'its-files', 'ath-users', 'tri-tdc', 'IOE-ErgoHand', 'its-amsl-abcd', 'me-abcd-its', 'SNRE-ReoLab', 'tri-tdcdata', 'kines-mtel', 'snre-css', 'kines-hnl', 'swd-deploy', 'its-mfile' );
            $rc[ 'path' ] = '/';
            $fake = array_map( 'strtolower', $fake );
            sort( $fake );
            foreach( $fake as $share ) {
                $rc[ 'contents' ][] = array( 'type' => 'dir', 'filename' => $share );
            }
            return $rc;
        } else {
            return parent::listDirectory($path, $associativeArray );
        }
    }

    public function getInfo( $path )
    {
        if ( $path == '/' ) {
            return array(
                'type' => 'dir',
                'filename' => '/',
                'readable' => TRUE
            );
        } else {
            return parent::getInfo( $path );
        }
    }
    

    private function mkdir_tree( $path )
    {
        if ( !is_dir( dirname( $this->getUrl( $path ) ) ) )
	{
            $this->mkdir_tree( dirname( $path ) );
        }
        if ( ! mkdir($this->getUrl( $path), 0744 )) {
            throw new Filedrawers_Filesystem_Exception(sprintf(
                'Unable to create the directory "%s".', $name), 5);
        }
    }
   
 
    public function createDirectory($path, $name)
    {
        $this->setPath( $path );
	$name = trim( $name, $this->ILLEGAL_DIR_CHARS );
	$this->mkdir_tree( $name ); 
    }


    public function listFavorite()
    {
        $favsTbl =  new Model_UserFavorites;
        $listFavs =  $favsTbl->listFavs();
        $myFavs = array( 'count' => 0 );
        $c = 0;

        foreach( $listFavs as $row ){ 
           foreach( $row as $column => $value ) {

               if ($column == 'servicename'){ 
                   $myFavs['contents'][$c]['service'] = $value;
               }    
 
               if ($column == 'favorite'){
	           $myFavs['contents'][$c]['name'] = $value;
               }

               if ($column == 'location'){ 
                   $myFavs['contents'][$c]['path'] = $value;
               }          
           }
           $c++; 
        }

        $myFavs['count'] = $c;
        return $myFavs;
    }


    public function addFavorite($path,$favorite)
    {
        $uniqname = $this->getUniqname(); 
        $favsTbl = new Model_UserFavorites;
        
        $favs = array(
           'username'   => $uniqname,
           'servicename' => 'mainstreamStorage',
           'location' => $path,
           'favoritename' => $favoriteName
        );
         
        $isValidInsert = $favsTbl->insertFavorite($path, $favoriteName);
    }


     public function renameFavorite($oldFavorite,$newFavorite,$path)
    {
        $uniqname = $this->getUniqname();
        $favsTbl = new Model_UserFavorites;
 
           'foldername' => $oldPath

        $favsNew = array(
           'username'   => $uniqname,
           'servicename' => 'mainstreamStorage',
           'location' => $path,
           'foldername' => $newPath
        );

        $isValidRename = $favsTbl->renameFavs($favsOld, $favsNew);
    }


     public function deleteFavorite($path,$foldername)
    {
        $uniqname = $this->getUniqname();
        $favsTbl = new Model_UserFavorites;

        $favs = array(
           'username'   => $uniqname,
           'servicename' => 'mainstreamStorage',
           'location' => $path,
           'foldername' => $foldername
        );

        $isValidDelete = $favsTbl->deleteFavs($favs);
    }

}
