<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Webservices_FavoritesController extends Zend_Controller_Action
{
    protected $_filesystem = null;
    protected $_favoritesPath = null;
    protected $_form = null;
    protected $_csrfToken = null;
    protected $_baseFilter = array('*' => 'StringTrim');
    protected $_context = null;

    public $contexts = array(
        'list' => array('xml', 'json', 'html'),
        'add' => array('xml', 'json', 'html'),
        'rename' => array('xml', 'json', 'html'),
        'delete' => array('xml', 'json', 'html')
    );


    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_filesystem = Zend_Registry::get('filesystem');
        $this->_csrfToken = new Zend_Form_Element_Hash('formToken');
        $this->_csrfToken->initCsrfToken();
        $this->view->formToken = $this->_csrfToken->getValue();
        $this->_context = $this->_helper->getHelper('contextSwitch');
        $this->_context->setContext('xml', array(
            'callbacks' => array('post' => array($this->_helper, 'xmlSerialize'))
        ));
        $this->_context->initContext();

        $this->_favoritesPath = $this->_filesystem->getHomeDir() . '/Favorites';

        if ( ! $this->_filesystem->getInfo($this->_favoritesPath)) {
            $this->_filesystem->createDirectory($this->_favoritesPath);
        }

        $this->_filesystem->addListHelper(array($this, 'setSymLink'));
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

        $files = $this->_filesystem->listDirectory($this->_favoritesPath);
        $this->view->contents = $files['contents'];
    }


    public function addAction() {}


    public function renameAction() {}


    public function deleteAction() {}


    public static function setSymLink(&$row)
    {
        if (strpos($row['filename'], '.') === 0 || ! is_link($row['filename'])) {
            $row = false;
        }
        else {
            $link = @readlink($row['filename']);
            $row['target'] = $link;
        }
    }
}

