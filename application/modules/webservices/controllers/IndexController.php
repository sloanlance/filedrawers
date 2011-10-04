<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Webservices_IndexController extends Webservices_FiledrawersControllerAbstract {


    public function init()
    {
        $this->contexts['services'] = array('xml', 'json', 'html');
        $this->contexts['list'] = array('xml', 'json', 'html');
        $this->contexts['rename'] = array('xml', 'json', 'html');
        $this->contexts['delete'] = array('xml', 'json', 'html'); 
        $this->contexts['move'] = array('xml', 'json', 'html');
        $this->contexts['duplicate'] = array('xml', 'json', 'html');
        $this->contexts['copy'] = array('xml', 'json', 'html');
        $this->contexts['mkdir'] = array('xml', 'json', 'html');
        $this->contexts['gettoken'] = array('xml', 'json', 'html');
        $this->contexts['upload'] = array('xml', 'json', 'html');  
        $this->contexts['uploadstatus'] = array('xml', 'json', 'html');  
        $this->contexts['uploadfinish'] = array('xml', 'json', 'html');
        $this->contexts['acl'] = array('xml', 'json', 'html');
        parent::init(); 
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
            ),
            'wappver' => array(
                'presence' => 'optional',
                'default' => 0
            )
        );
        $options = array('inputNamespace' => 'Filedrawers_Validate');
        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);

        if ( ! $input->isValid( 'service' )) {
            $this->view->errorMsg = array( 'service' => array( 'invalid' => 'invalid service specified' ));
            throw( new Zend_Exception( 'service parameter must be one of: '. implode( ', ', array_keys( $this->_availableServices ))));
        }
        $this->view->service = $input->service;

        if ( ! $input->isValid( 'wappver' )) {
            $this->view->errorMsg = array( 'wappver' => array( 'invalid' => 'invalid wappver flag' ));
            throw( new Zend_Exception( 'wappver (Web App Version) parameter must be an integer.' ));
        }
        if ( $input->wappver > 0 ) {
            if ( $input->wappver != Zend_Registry::get( 'webAppVersion' )) {
                // TODO there are better ways to do this:
                $this->view->errorMsg = '<a href="" >Refresh</a> to get an updated version of the interface.';
            }

            $this->view->webAppVersion = Zend_Registry::get( 'webAppVersion' );
        }

        if ($input->service !== NULL){
            $this->_filesystem = new $this->_availableServices[ $input->service ]();
            $this->_filesystem->init();
            Zend_Registry::set('filesystem', $this->_filesystem);
        }
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
            $this->view->services[ 'contents' ][ $id ] = $serviceInfo[ $id ];
            $service = new $serviceClass;
            $this->init();
            $this->view->services[ 'contents' ][ $id ][ 'home' ] = $service->getHomedir();
        }

        $this->view->services[ 'defaultService' ] = Zend_Registry::get('config')->filesystem->default;
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
        $this->view->quota = $this->_filesystem->getQuota($path);
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


    public function aclAction()
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
        $path = (empty($input->path)) ? $this->_filesystem->getHomeDir() : $input->path;
        $this->view->path = $path;

        $acl = $this->_filesystem->readAcl($path);
        $this->view->acl = $acl;
        $this->_form = new Form_AclForm($this->_csrfToken, $acl);

    }


    public function uploadAction()
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
            'missingMessage' => 'You must specify an origin path in the URL before uploading files.'
        );
        
        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);
        if ( ! $input->isValid('path')) {
            $this->view->errorMsg = $input->getMessages();
            return;
        }
// use form to do validation. All we can validate is the path.

        $this->_filesystem->addListHelper(array($this, 'filterByFileName'));
        $files = $this->_filesystem->listDirectory($input->path, true);
        $this->_form = new Form_UploadForm(
            $this->_csrfToken,
            $input->path
        );

        $overwrite = $this->_request->getParam('overwrite');
        $filename = $this->_request->getParam('name');

        if ( $overwrite === "false") {
            if (file_exists($input->path.'/'.$filename )) {
                //error back to javascript for plupload
                $this->view->errorMsg = "Failed upload, file exists.";
            }
        }


        // TODO handle methods other than stream uploads
        // TODO validate input
        $out = $this->_filesystem->getFileHandle(FileDrawers_Filesystem::pathConcat($_GET['path'], $_GET['name']), 'wb');
        if ($out) {
            // Read binary input stream and append it to temp file
            $in = fopen("php://input", "rb");
            if ($in) {
                while ($buff = fread($in, 4096))
                    fwrite($out, $buff);
            } else
                 $this->view->errorMsg = "Failed to open input stream.";

            fclose($in);
            fclose($out);
        } else
            $this->view->errorMsg = "Failed to open output stream.";
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

        if( is_array(@$row['perms'])){
            $perm_parts = array();
            foreach( $row['perms'] as $perm => $value ) {
                if ($value) {
                    $perm_parts[] = ucfirst($perm);
                }
            }
            if (empty($perm_parts)) {
                $row['perms'] = 'no permissions';
            } else {
                $row['perms'] = implode(', ', $perm_parts);
            }
        }

        return true;
    }


    public function filterByFileName(&$row)
    {
        $row = $row['filename'];
        return true;
    }
 }

