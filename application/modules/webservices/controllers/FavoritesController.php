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

     public function preDispatch()
    {
         if ( in_array( $this->_request->action, array( 'services' ))) {
            return;
         }

        $serviceValidator = new Zend_Validate_InArray( array_keys( $this->_availableServices ));
        $serviceValidator->setStrict( TRUE );
        $wappverValidator = new Zend_Validate_Int();
        $validators = array(
            'service' => array(
                $serviceValidator,
                'presence' => 'optional',
                'default' => Zend_Registry::get('config')->filesystem->default
            ),
            'wappver' => array(
                $wappverValidator,
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
//        $this->view->service = $input->service;

        if ( ! $input->isValid( 'wappver' )) {
            $this->view->errorMsg = array( 'wappver' => array( 'invalid' => 'invalid wappver flag' ));
            throw( new Zend_Exception( 'wappver (Web App Version) parameter must be an integer.' ));
        }
        if ( (int) $input->wappver > 0 ) {
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
         
        $this->view->services = array();
        foreach( $this->_availableServices as $id => $serviceClass ) {
                 $fs = new $serviceClass;	
                 $fs->init();
                 $this->view->services[$id] = $fs->listFavs(); 
        } 
    }

    public function addAction()
    {
        $this->_form = new Form_Favorites_AddForm($this->_csrfToken, $this->_request->getParam('path'));
              
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
        $this->view->message = 'Successfully added favorite.';
        
    }
    
    public function renameAction()
    {
        $this->_form = new Form_Favorites_RenameForm($this->_csrfToken, $this->_request->getParam('path'));
 
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
        $this->view->message = 'Successfully renamed favorite.';
    }

    public function deleteAction()
    {
        $this->_form = new Form_Favorites_DeleteForm($this->_csrfToken, $this->_request->getParam('path'));

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
        $this->view->message = 'Successfully deleted favorite.';

    }

}
