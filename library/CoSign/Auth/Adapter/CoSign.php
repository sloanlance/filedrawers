<?php

require_once 'Zend/Auth/Adapter/Interface.php';
require_once 'Zend/Auth/Adapter/Exception.php';

class CoSign_Auth_Adapter_CoSign implements Zend_Auth_Adapter_Interface
{

    private $_response = null;


    public function __construct(Zend_Controller_Response_Abstract $response = null) {
        $this->_response = $response;
    }


    public function authenticate()
    {
        if (!isset($_SERVER['REMOTE_USER'])) {
            $this->redirect();
        }

        if (!isset($_SERVER['REMOTE_USER']) || empty($_SERVER['REMOTE_USER'])) {
            throw new Zend_Auth_Adapter_Exception('Unable to retrieve remote user identity');
        }

        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS,
                $_SERVER['REMOTE_USER'],
                array("Authentication successful"));
    }


    private function redirect()
    {
        $url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        if (null === $this->_response) {
            require_once "Zend/Controller/Response/Http.php";
            $this->_response = new Zend_Controller_Response_Http();
        }

        if ( ! $this->_response->canSendHeaders()) {
            $this->_response->setBody("<script language=\"JavaScript\"" .
                 " type=\"text/javascript\">window.location='$url';" .
                 "</script>");
        } else {
            $this->_response->setRedirect($url);
        }

        $this->_response->sendResponse();
        exit();
    }
}
