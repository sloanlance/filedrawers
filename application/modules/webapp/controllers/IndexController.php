<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class IndexController extends Zend_Controller_Action
{
    protected $_filesystem;
    protected $_flashMessenger = null;
    protected $_redirector = null;

    public function init()
    {
        $this->_filesystem = Zend_Registry::get('filesystem');
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }


    public function preDispatch()
    {
        $this->view->messages = $this->_flashMessenger->getMessages();
    }

    public function listAction()
    {
        $path = $this->_request->getUserParam('path');

        if (empty($path)){
            $path = $this->_filesystem->getHomeDir();
        }

        /*
        $uploadForm = new Form_UploadForm();
        $uri = $uploadForm->getElement('returnToURI');
        $sid = $uploadForm->getElement('sessionid');
        $uri->setValue($this->view->serverUrl() .
                $this->view->url(array($sid->getHash(), $path),
                'finishUpload', false, false));
        $formPath = $uploadForm->getElement('path');
        $formPath->setValue($path);

        $this->view->uploadForm = $uploadForm;
         */

        $this->view->path  = $path;
    }


    public function finishAction()
    {
        $filters = array(
            '*' => 'StringTrim'
        );

        $validators = array(
            'id' => array()
        );

        $input = new Zend_Filter_Input($filters, $validators, $_GET);
        if ( ! $input->isValid()) {
            $this->_response = array(
                'errorCode' => 5,
                'errorMsg' => $input->getMessages()
            );

            return;
        }

        $upload = new Model_UploadStatus($input->id);
        if ( ! $upload->isValid()) {
            $this->_response = array(
                'errorCode' => 5,
                'errorMsg' => $upload->getMessages()
            );
        }

        $this->_redirector->gotoRoute(array('path' => $path), 'list', false, false);
    }
}

