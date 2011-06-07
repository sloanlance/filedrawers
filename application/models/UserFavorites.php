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
    protected static $_dbTbl = NULL;
    protected $_username;
    protected $_servicename;

    public function __construct()
    {
        parent::__construct();
        $this->_username = $this->getUniqname();
        $this->_servicename = $this->getService();
    }

    static public function getDB()
    {
        if ( self::$_dbTbl === NULL ) {
            self::$_dbTbl = new self();
        }

        return self::$_dbTbl;   
    }

    public function getUniqname()
    {
        $userInfo = posix_getpwnam(Zend_Auth::getInstance()->getIdentity());
        return $uniqname = $userInfo['name'];
    }

    public function getService()
    {
        $service = Zend_Registry::get('filesystem');
        return $service = get_class($service);
    }

    public function getPath($old)
    {
        $row = NULL;
        
        $select = $this->select()
           ->from('filedrawers_favorites')
           ->where("username = ?", $this->_username)
            ->where("servicename = ?", $this->_servicename)
            ->where("foldername = ?", $old)
            ->limit(1);

        $row = $this->fetchRow($select);
        if ($row !== NULL){      
            $rowArray = $row->toArray();
            return $row['location'];
        } 
    }
 
    public function insertFavorite($path, $favorite)
    {
       $fav = array(
           'username'   => $this->_username,
           'servicename' => $this->_servicename,
           'location' => $path,
           'foldername' => $favorite
        );

        $this->insert($fav);
    }

    public function listFavorites()
    {
        $select = $this->select();
        $rows = $this->fetchAll($select);
        $rowArray = $rows->toArray();
        return $rowArray;
    }

    public function renameFavorite($old, $new, $path)
    {
        $row = NULL;
 
        $select = $this->select()
           ->from('filedrawers_favorites')
           ->where("username = ?", $this->_username)
            ->where("servicename = ?", $this->_servicename)
            ->where("location = ?", $path)
            ->where("foldername = ?", $old)
            ->limit(1);

        $row = $this->fetchRow($select);
   
        $row->username = $this->_username;
        $row->servicename = $this->_servicename;
        $row->location = $path;
        $row->foldername = $new;
        $row->save(); 
    }

    public function deleteFavorite($path, $favorite)
    {
        $row = NULL;
        $select = $this->select()
            ->from('filedrawers_favorites')
            ->where("username = ?", $this->_username)
            ->where("servicename = ?", $this->_servicename)
            ->where("location = ?", $path)
            ->where("foldername = ?", $favorite)
            ->limit(1);
        
        $row = $this->fetchRow($select);
        $row->delete();
    }

}
