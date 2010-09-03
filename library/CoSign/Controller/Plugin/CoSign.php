<?php
class CoSign_Controller_Plugin_CoSign extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $auth    = Zend_Auth::getInstance();
        $adapter = new CoSign_Auth_Adapter_CoSign();

        $auth->setStorage(new CoSign_Auth_Storage_CoSign());
        $auth->authenticate($adapter);
    }
}
