<?php

require_once 'Zend/Auth/Storage/Interface.php';


class CoSign_Auth_Storage_CoSign implements Zend_Auth_Storage_Interface
{

    public function isEmpty()
    {
        return (!isset($_SERVER['REMOTE_USER']));
    }


    public function read()
    {
        return $_SERVER['REMOTE_USER'];
    }


    public function write($contents)
    {}


    public function clear()
    {}
}
