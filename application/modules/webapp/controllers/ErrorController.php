<?php

class ErrorController extends Zend_Controller_Action
{
    public $contexts = array(
        'error' => array('xml', 'json', 'html')
    );


    public function init()
    {
        $this->_context = $this->_helper->getHelper('contextSwitch');
        $this->_context->setContext('xml', array(
            'callbacks' => array('post' => array($this->_helper, 'xmlSerialize'))
        ));
        $this->_context->initContext();
    }


    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        $trace = $errors->exception->getTrace();

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->errorMsg = 'Page not found';
                break;
            default:
                // application error
                error_log($errors->exception->getTraceAsString());
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->errorMsg = $errors->exception->getMessage();
                break;
        }

        // conditionally display exceptions
        if (APPLICATION_ENVIRONMENT == 'development' or $this->getInvokeArg('displayExceptions') == true) {
            $this->view->displayExceptions = true;
            $this->view->exception = $errors->exception;
            $this->view->request   = $errors->request;
        }        
    }

    public function missingAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
        $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
    }
}

