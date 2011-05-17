<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Webservices_FavoritesController extends Webservices_FDController 
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
        //if filesystem set just check favorites for that filesystem otherwise loop through avalaible services and send back all faves.
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

	public function setFilesystem()
	{
	       $this->_filesystem = new $allServices;
               $this->_filesystem->init();
               Zend_Registry::set('filesystem', $this->_filesystem);
	}

	public function getFavs()
	{
		$favs = $this->_filesystem->listFavs();
                $this->view->message = 'Test List Favs.';
                $this->view->contents = $favs;
	}


}
