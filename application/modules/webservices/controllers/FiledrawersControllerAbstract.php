<?php

abstract class Webservices_FiledrawersControllerAbstract extends Zend_Controller_Action
{
	protected $_filesystem = null;
	protected $_form = null;
	protected $_csrfToken = null;
	protected $_baseFilter = array('*' => 'StringTrim');
	protected $_context = null;
 	protected $_availableServices = array();
        protected $_request = null;
        public $contexts = array();      	

    // Common methods
    public function init() {
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
        $this->view->service = $input->service;
        
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


     public function postDispatch()
    {
        if (is_null($this->_context->getCurrentContext()) && $this->_form) {
            $this->view->form = $this->_form;
        }
    }

    
}
