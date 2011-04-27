<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Webservices_V1Controller extends Zend_Controller_Action {
    protected $_filesystem = null;
    protected $_form = null;
    protected $_csrfToken = null;
    protected $_baseFilter = array('*' => 'StringTrim');
    protected $_context = null;
    protected $_availableServices = array();

    public $contexts = array(
        'services' => array('xml', 'json', 'html'),
        'list' => array('xml', 'json', 'html'),
        'rename' => array('xml', 'json', 'html'),
        'delete' => array('xml', 'json', 'html'),
        'move' => array('xml', 'json', 'html'),
        'duplicate' => array('xml', 'json', 'html'),
        'copy' => array('xml', 'json', 'html'),
        'mkdir' => array('xml', 'json', 'html'),
        'gettoken' => array('xml', 'json', 'html'),
        'uploadstatus' => array('xml', 'json', 'html'),
        'uploadfinish' => array('xml', 'json', 'html')
    );


    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_csrfToken = new Zend_Form_Element_Hash('formToken');
        $this->_csrfToken->initCsrfToken();

        foreach( Zend_Registry::get('config')->filesystem->active->toArray() as $id ) {
            $serviceClass = 'Service_'. ucfirst( $id );

            if ( class_exists( $serviceClass )) {
                $this->_availableServices[ $id ] = $serviceClass;
            }
        }

        $this->_context = $this->_helper->getHelper('contextSwitch');
        $this->_context->setContext('xml', array(
            'callbacks' => array('post' => array($this->_helper, 'xmlSerialize'))
        ));
        $this->_context->initContext();

        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoController( TRUE );

        $this->_view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
        $this->_view->setScriptPath( null );
        $this->_view->addScriptPath( APPLICATION_PATH .'/modules/webservices/views/scripts/v1/' );
    }


    public function preDispatch()
    {
        if ( in_array( $this->_request->action, array( 'services' ))) {
            return;
        }

        $serviceValidator = new Zend_Validate_InArray( array_keys( $this->_availableServices ));
        $serviceValidator->setStrict( TRUE );
        $validators = array(
            'service' => array(
                $serviceValidator,
                'presence' => 'optional',
                'default' => Zend_Registry::get('config')->filesystem->default
            )
        );
        $options = array('inputNamespace' => 'Filedrawers_Validate');
        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);

        if ( ! $input->isValid( 'service' )) {
            $this->view->errorMsg = array( 'service' => array( 'invalid' => 'invalid service specified' ));
            throw( new Zend_Exception( 'service parameter must be one of: '. implode( ', ', array_keys( $this->_availableServices ))));
        }

        $this->_filesystem = new $this->_availableServices[ $input->service ]();
        $this->_filesystem->init();
        Zend_Registry::set('filesystem', $this->_filesystem);
    }


    public function postDispatch()
    {
        if (is_null($this->_context->getCurrentContext()) && $this->_form) {
            $this->view->form = $this->_form;
        }
    }


    public function servicesAction()
    {
        $this->view->services = array();
        $serviceInfo = Zend_Registry::get('config')->filesystem->services->toArray();
        foreach( $this->_availableServices as $id => $serviceClass ) {
            $this->view->services[ 'services' ][ $id ] = $serviceInfo[ $id ];
        }

        $this->view->services[ 'default' ] = Zend_Registry::get('config')->filesystem->default;
    }


    public function indexAction() {
        $this->_helper->layout->enableLayout();
    }


    public function listAction()
    {
        $validators = array(
            'path'     => array(
                array(
                    'FilePath', array(
                        'type' => 'dir',
                        'readable' => true
                     )
                ), 'allowEmpty' => true),
            'friendly' => array('Alpha', 'allowEmpty' => true),
            'limit'    => array('Digits', 'allowEmpty' => true),
            'offset'   => array('Digits', 'allowEmpty' => true)
        );
        $options = array('inputNamespace' => 'Filedrawers_Validate');
        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);

        if ( ! $input->isValid()) {
            $this->view->errorMsg = $input->getMessages();
            return;
        }

        if ( ! is_null($input->friendly)) {
            $this->_filesystem->addListHelper(array($this, 'setFriendlyValues'));
        }

        $path = (empty($input->path)) ? $this->_filesystem->getHomeDir() : $input->path;
        $this->_filesystem->addListHelper(array('Model_Mime', 'setMimeType'));

        $files = $this->_filesystem->listDirectory($path);
        $this->view->path = $path;
        $this->view->offset = $input->offset;
        $this->view->limit = $input->limit;
        $this->view->count = count($files['contents']);
        $contentsSlice =  array_slice($files['contents'], $input->offset, $input->limit);
        unset($files['contents']);
        $this->view->contents = $contentsSlice;
    }


    public function downloadAction()
    {
        $this->_helper->viewRenderer->setNoRender();

        $validators = array(
            'path'     => array(
                array(
                    'FilePath', array(
                        'type' => 'file',
                        'readable' => true
                     )
                ), 'allowEmpty' => false)
        );

        $options = array('inputNamespace' => 'Filedrawers_Validate');
        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);

        if ( ! $input->isValid()) {
            $errors = $input->getErrors();
            if (in_array('doesNotExist', $errors['path'])) {
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
            }
            else if (in_array('wrongType', $errors['path'])) {
                $this->getResponse()->setRawHeader('HTTP/1.1 403 Only files can be downloaded.');
            }
            else {
                $this->getResponse()->setRawHeader('HTTP/1.1 500 Internal Server Error');
            }
            return;
        }

        $handle = $this->_filesystem->getFileHandle($input->path);
        $fileTransfer = new Filedrawers_Filetransfer($handle, basename($input->path));
        $fileTransfer->send();
    }


    public function renameAction()
    {
        $this->_form = new Form_RenameForm($this->_csrfToken,
            $this->_request->getParam('path'));

        if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }

        $values = $this->_form->getValidValues($_POST);

        $oldPath = $values['path'] . '/' . $values['oldName'];
        $newPath = $values['path'] . '/' . $values['newName'];
        $this->_filesystem->rename($oldPath, $newPath);

        $this->view->status = 'success';
        $this->view->message = 'Successfully renamed the file or directory.';
    }


    public function deleteAction()
    {
        $validators = array(
            'path'     => array(
                array(
                    'FilePath', array(
                        'type' => 'dir',
                        'readable' => 'true'
                     )
                ), 'presence' => 'required')
        );

        $options = array(
            'inputNamespace' => 'Filedrawers_Validate',
            'missingMessage' => 'You must specify the path that contains the file(s) or folder(s) you want to delete in the URL.'
        );

        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);

        if ( ! $input->isValid('path')) {
            $this->view->errorMsg = $input->getMessages();
            return;
        }

        $this->_filesystem->addListHelper(array($this, 'filterByFileName'));
        $files = $this->_filesystem->listDirectory($input->path, true);
        $this->_form = new Form_DeleteForm($this->_csrfToken, $files['contents']);

        if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }

        $values = $this->_form->getValidValues($_POST);

        $this->_filesystem->remove($input->path, $values['files']);
        $this->view->status = 'success';
        $this->view->message = 'Delete successful.';
    }


    public function moveAction()
    {
        $validators = array(
            'path'     => array(
                array(
                    'FilePath', array(
                        'type' => 'dir',
                        'readable' => 'true'
                     )
                ), 'presence' => 'required')
        );

        $options = array(
            'inputNamespace' => 'Filedrawers_Validate',
            'missingMessage' => 'You must specify an origin path in the URL before moving files or folders.'
        );

        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);

        if ( ! $input->isValid('path')) {
            $this->view->errorMsg = $input->getMessages();
            return;
        }

        $this->_filesystem->addListHelper(array($this, 'filterByFileName'));
        $files = $this->_filesystem->listDirectory($input->path, true);
        $this->_form = new Form_MoveForm(
            $this->_csrfToken,
            $files['contents'],
            $input->path
        );

        if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }

        $values = $this->_form->getValidValues($_POST);

        $this->_filesystem->move($values['files'], $input->path, $values['toPath']);
        $this->view->status = 'success';
        $this->view->message = 'Successfully moved the files(s) or folder(s).';
    }


    public function duplicateAction()
    {
        $this->_form = new Form_DuplicateForm($this->_csrfToken,
            $this->_request->getParam('path'));

        if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }

        $values = $this->_form->getValidValues($_POST);

        $oldPath = $values['path'] . '/' . $values['originName'];
        $newPath = $values['path'] . '/' . $values['newName'];
        $this->_filesystem->duplicate($oldPath, $newPath);

        $this->view->status = 'success';
        $this->view->message = 'Successfully duplicated the file or directory.';
    }


    public function copyAction()
    {
        $validators = array(
            'path'     => array(
                array(
                    'FilePath', array(
                        'type' => 'dir',
                        'readable' => 'true'
                     )
                ), 'presence' => 'required')
        );

        $options = array(
            'inputNamespace' => 'Filedrawers_Validate',
            'missingMessage' => 'You must specify an origin path in the URL before copying files or folders.'
        );

        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);

        if ( ! $input->isValid('path')) {
            $this->view->errorMsg = $input->getMessages();
            return;
        }

        $this->_filesystem->addListHelper(array($this, 'filterByFileName'));
        $files = $this->_filesystem->listDirectory($input->path, true);
        $this->_form = new Form_CopyForm(
            $this->_csrfToken,
            $files['contents'],
            $input->path
        );

        if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }

        $values = $this->_form->getValidValues($_POST);

        $this->_filesystem->copy($values['files'], $input->path, $values['toPath']);
        $this->view->status = 'success';
        $this->view->message = 'Successfully copied the files(s) or folder(s).';
    }


    public function mkdirAction()
    {
        $this->_form = new Form_MkdirForm($this->_csrfToken,
            $this->_request->getParam('path'));

        if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }

        $values = $this->_form->getValidValues($_POST);
        $this->_filesystem->createDirectory($values['path'], $values['folderName']);
        $this->view->status = 'success';
        $this->view->message = 'Successfully created the directory.';
    }


    public function uploadstatusAction()
    {;
        $validators = array(
            'id' => array('allowEmpty' => false)
        );

        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET);

        if ( ! $input->isValid('id')) {
            $this->view->errorMsg = $input->getMessages();
            return;
        }

        $status = new Model_UploadStatus($input->id);
        $this->view->uploadProgress = $status->getProgress();
    }


    public function uploadfinishAction()
    {
        $validators = array(
            'id' => array('allowEmpty' => false)
        );

        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET);
        if ( ! $input->isValid('id')) {
            $this->view->errorMsg = $input->getMessages();
            return;
        }

        $upload = new Model_UploadStatus($input->id);
        if ( ! $upload->isValid()) {
            $this->view->errorMsg = $upload->getMessages();
        }
        else {
            $this->view->status = 'success';
            $this->view->message = 'Upload successful';
        }
    }


    public function gettokenAction()
    {
        $this->view->formToken = $this->_csrfToken->getValue();
    }


    public function setFriendlyValues(&$row)
    {
        $row['size'] = $this->view->formatBytes($row['size']);

        if( ! empty($row['modTime'])){
            $row['modTime'] = date("m/d/Y h:i:s",$row['modTime']);
        }

        return true;
    }


    public function filterByFileName(&$row)
    {
        $row = $row['filename'];
        return true;
    }
}

