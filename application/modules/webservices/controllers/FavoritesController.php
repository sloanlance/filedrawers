<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Webservices_FavoritesController extends Webservices_FDController 
{

    public $contexts = array('list' => array('xml', 'json', 'html'),
      	'add' => array('xml', 'json', 'html'),
       	'rename' => array('xml', 'json', 'html'),
       	'delete' => array('xml', 'json', 'html')
    );


    public function init()
    {
//      parent::init(); 

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

      //TODO:set the filesystem to afs to make testing easy, remove when finished testing
//      $this->view->services[ 'services' ][ 'afs' ] = $serviceInfo[ 'afs' ];
    
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
        $input = new Zend_Filter_Input($this->_baseFilter, $validators, $_GET, $options);

        if ( ! $input->isValid()) {
            $this->view->errorMsg = $input->getMessages();
            return;
        }

        $files = $this->_filesystem->listFavs();
        echo("here");
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

	 public function testAction()
        {
                echo "Execute /filedrawers/application/modules/webservices/controllers/Favorites";
                //$this->_favoritesPath = $this->_filesystem->listFavs();


                //$fPath = $this->_filesystem->listFavs();
                //echo "<br/>List Favs : " . $fPath;

                $files = $this->_filesystem->listFavs();
                echo "<br/>List Favs : " . $files[0];
 
        //$this->view->contents = $files['contents'];

        }



}
