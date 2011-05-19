<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Model_UserFavs extends Zend_Db_Table
{
    protected $_name = 'filedrawers_favorites';

    public function __construct()
    {
       parent::__construct();

    }

   
    public function insertFavs()
    {
        $table = new Model_UserFavs();
        $insert = $table->insert($favs);
    }


    public function getAllFavs()
    {
        $table = new Model_UserFavs();
        $select = $table->select();
        $rows = $table->fetchAll($select);

     }

    public function getMessages()
    {
        return $this->errorMsg;
    }
}

