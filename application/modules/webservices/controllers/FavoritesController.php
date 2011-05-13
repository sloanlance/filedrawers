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
      $this->_favoritesPath = $this->_filesystem->getHomeDir() . '/Favorites';

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

    }

}
