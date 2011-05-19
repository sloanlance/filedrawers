<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Model_UserFavs extends Zend_Db_Table
{
    protected $_name = 'filedrawers_favorites';

/*    public function __construct()
    {
       parent::__construct();

    }
*/
   
    public function insertFavs()
    {
        $table = new Model_UserFavs();
        $insert = $table->insert($favs);
    }


    public function fetchAll()
    {
        $table = new Model_UserFavs();
        $select = "select * from 'filedrawers_favorites'";

echo($select);
$rows = $table->fetchAll($select);
echo "The table name is " . $rows['username'] . "\n";

     }

    public function getMessages()
    {
        return $this->errorMsg;
    }
}

