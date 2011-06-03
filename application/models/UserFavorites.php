<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Model_UserFavorites extends Zend_Db_Table
{
    protected $_name = 'filedrawers_favorites';
    protected $_errorMsg;

 
    public function __construct()
    {
    
        parent::__construct();
        $username = 
        $servicename =  

    }

 
    public function insertFavorite($favorite)
    {
   
        $this->insert($favorite);

    }


    public function listFavorites()
    {
        $select = $this->select();
        $rows = $this->fetchAll($select);
        $rowArray = $rows->toArray();
        return $rowArray;
    }


     public function renameFavorite( $old, $new )
     {

        $row = NULL;

        $select = $this->select()
            ->from('filedrawers_favorites')
            ->where("username = ?", $old['username'])
            ->where("servicename = ?", $old['servicename'])
            ->where("location = ?", $old['location'])
            ->where("foldername = ?", $old['foldername'])
            ->limit(1);

        $row = $this->fetchRow($select);
   
        $row->username = $new['username'];
        $row->servicename = $new['servicename'];
        $row->location = $new['location'];
        $row->foldername = $new['foldername'];
        $row->save(); 
      
    }


    public function deleteFavorite($del)
    {

        $row = NULL;
 
        $select = $this->select()
            ->from('filedrawers_favorites')
            ->where("username = ?", $del['username'])
            ->where("servicename = ?", $del['servicename'])
            ->where("location = ?", $del['location'])
            ->where("foldername = ?", $del['foldername'])
            ->limit(1);
        
        $row = $this->fetchRow($select);
  
        $row->delete();
    
    }


}

