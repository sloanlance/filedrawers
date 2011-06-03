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
        $this->_form = new Form_Favorites_FavAddForm($this->_csrfToken, $this->_request->getParam('path'));
              
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
        $this->_form = new Form_Favorites_FavRenameForm($this->_csrfToken, $this->_request->getParam('path'));
    
        if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }
   
        $values = $this->_form->getValidValues($_POST);

        $oldName = $values['oldName'];
        $newName = $values['newName'];
        $this->_filesystem->renameFavs($oldName, $newName);

        $this->view->status = 'success';
        $this->view->message = 'Successfully renamed the Favs.';
    }

	public function deleteAction()
	{
		$this->_form = new Form_Favorites_FavDeleteForm($this->_csrfToken, $this->_request->getParam('path'));

		if ( ! $this->getRequest()->isPost()) {
            return;
        }
        else if ( ! $this->_form->isValid($_POST)) {
            $this->view->errorMsg = $this->_form->getMessages(null, true);
            return;
        }
                
        $values = $this->_form->getValidValues($_POST);

		$name = $values['folderName'];
		$this->_filesystem->deleteFavs( $name );
            
        $this->view->status = 'success';
        $this->view->message = 'Successfully deleted the Favs.';

	}

}
