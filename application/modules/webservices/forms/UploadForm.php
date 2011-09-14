<?php

class Form_UploadForm extends Zend_Form
{
    protected $_csrfToken = null;
    protected $_path = null;

    public function __construct($csrfToken, $path)
    {
        $this->_csrfToken = $csrfToken;
        $this->_path = $path;
        parent::__construct();
    }

    public function init()
    {
        $this->setMethod('post');
        $this->setName('upload');
        $path = new Zend_Form_Element_Text('path');
        $path->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $path->setLabel('Path to upload to');
        $path->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy the parent path for the upload.')
            )
        );
        $path->addValidator('FilePath', true, array(
            'modifyable' => true,
            'type' => 'dir'
        ));
        $path->addFilter('StringTrim');
        $path->setValue($this->_path);
        $this->addElement($path);

        $this->addElement($this->_csrfToken);
        $this->addElement('submit', 'Submit');


		
    }
}
