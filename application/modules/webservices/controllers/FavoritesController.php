<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Webservices_FavoritesController extends Webservices_FiledrawersControllerAbstract 
{
    protected $_favoritesPath = null;

    public function init()
    {
      $this->contexts['list'] = array('xml', 'json', 'html');
      $this->contexts['add'] = array('xml', 'json', 'html');
      $this->contexts['rename'] = array('xml', 'json', 'html');
      $this->contexts['delete'] = array('xml', 'json', 'html');
      parent::init();
    }

    public function listAction()
    {
	$validators = array(
        'path'     => array(
             array(
                'FavoritesPath', array(
                'type' => 'dir',
                'readable' => true
            )
       ), 'allowEmpty' => true),
            'friendly' => array('Alpha', 'allowEmpty' => true),
            'limit'    => array('Digits', 'allowEmpty' => true),
            'offset'   => array('Digits', 'allowEmpty' => true)
       );
        
        $options = array('inputNamespace' => 'Filedrawers_Validate');
        $filterInput = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);

        if ( ! $filterInput->isValid()) {
            $this->view->errorMsg = $filterInput->getMessages();
            return;
        }
        
	//if filesystem set check favorites if not loop through avalaible services and return all favorites.
        if (! $this->_filesystem){
            foreach($this->_availableServices as $allServices){
                    setFilesystem();
	            getFavs();
            } 
        }
        else{    
	    $this->getFavs();   
	} 
    }

     public function getFavs()
    {
       $favs = $this->_filesystem->listFavs();
       $this->view->contents = $favs;
    }

    public function setFilesystem()
    {
        $this->_filesystem = new $allServices;
        $this->_filesystem->init();
        Zend_Registry::set('filesystem', $this->_filesystem);
    }

    public function addAction()
    {
        $this->_form = new Form_FavoritesAddForm($this->_csrfToken, $this->_request->getParam('path'));
              
        if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }
       
        $values = $this->_form->getValidValues($_POST);
        $this->_filesystem->addFavs($values['path'], $values['folderName']);
        $this->view->status = 'success';
        $this->view->message = 'Successfully created favorites link.';

    }
    
    public function renameAction()
    {
        $this->_form = new Form_FavoritesRenameForm($this->_csrfToken,
        $this->_request->getParam('path'));
    
        if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }
   
        $values = $this->_form->getValidValues($_POST);
        check($values);
        $oldPath = $values['oldName'];
        $newPath = $values['newName'];
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
        $this->_form = new Form_FavoritesDeleteForm($this->_csrfToken, $files['contents']);

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

}
