<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Model_UserFavorites extends Zend_Db_Table
{
    protected $_name = 'filedrawers_favorites';

    public function __construct()
    {
        parent::__construct();
    }

    public function insertFavs($favs)
    {
        //check if favorite already exists, if not insert

        //check validity of filename
        $name = trim( $name, $this->ILLEGAL_DIR_CHARS );
  
        //check validity of directory

        //check validity of uniqname

        //check validity of service
 
        //insert if valid and does not already exist
        $insert = $this->insert($favs);
    }


    public function listFavs()
    {
        $entries = array(); 
        $select = $this->select();
        $rows = $this->fetchAll($select);
        $entries ['contents'][] = $rows;
        return $entries;
     }

     public function renameFavs( $old, $new )
     {
        if ($this->_fileExists($new)) {
            // do nothing
        }
        if ( $old == $new ) {
            // do nothing
        }
        rename( $oldPath, $newPath );
      }


    public function deleteFavs()
    {





    }

    public function getMessages()
    {
        return $this->errorMsg;
    }















}

