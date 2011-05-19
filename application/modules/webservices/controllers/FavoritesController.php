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
       parent::preDispatch();
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
       $ifs_class = get_class($this->_filesystem);
       if ($ifs_class == 'Service_Ifs'){
            $favs = $this->Arr2d($favs);
        }
       echo("here");
       $this->view->contents = $favs;
    }

    public function setFilesystem()
    {
       $this->_filesystem = new $allServices;
       $this->_filesystem->init();
       Zend_Registry::set('filesystem', $this->_filesystem);
    }

    public function Arr2d($ifs_favs = NULL)
    {
        $newFavs = array();

	if ( is_array($ifs_favs) ) {
            
	    foreach ($ifs_favs as $fav) {
	            if ( is_array($fav)){      
	               foreach ($fav as $info){
		                if( is_array($info)){   
			          foreach ($info as $f){
		                           $newFavs [ 'contents' ][] = array( 'type' => 'dir', 'filename' => $f );
                                  }
	                        }	
		       } 
                    }	
	    }
        
	return $newFavs;

	}
    }

}
