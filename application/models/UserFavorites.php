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

    public function insertFavs($favs)
    {
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

    public function renameFavs()
    {



    }


    public function deleteFavs()
    {





    }

    public function getMessages()
    {
        return $this->errorMsg;
    }















}

