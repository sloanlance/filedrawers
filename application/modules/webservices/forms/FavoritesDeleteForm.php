<?php

class Form_FavoritesDeleteForm extends Zend_Form
{
    protected $_csrfToken = null;
    protected $_options = array();

    public function __construct($csrfToken, $options)
    {
        $this->_options = $options;
        $this->_csrfToken = $csrfToken;
        parent::__construct();
    }

    public function init()
    {
        $this->setMethod('post');
        $this->setName('delete');

        $files = new Zend_Form_Element_MultiCheckbox('files');
        $files->setLabel('Select file(s) to delete');
        $files->addFilter('StringTrim');
        $files->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy at least one file or folder to delete.')
            )
        );
        $files->addMultiOptions($this->_options);
        $files->setValue(array());
        $this->addElement($files);

        $this->addElement($this->_csrfToken);
        $this->addElement('submit', 'Submit');
    }
}
