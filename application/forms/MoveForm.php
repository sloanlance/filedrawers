<?php

class Form_MoveForm extends Zend_Form
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
        $this->setName('move');

        $files = new Zend_Form_Element_MultiCheckbox('files');
        $files->setLabel('Select file(s) to move');
        $files->addFilter('StringTrim');
        $files->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy at least one file or folder to move.')
            )
        );

        $files->addMultiOptions($this->_options);
        $files->setValue(array());
        $this->addElement($files);

        $toPath = new Zend_Form_Element_Text('toPath');
        $toPath->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $toPath->setLabel('To Path');
        $toPath->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy the path for the file or folder to rename.')
            )
        );
        $toPath->addValidator('FilePath', true, array(
            'modifyable' => true,
            'type' => 'dir'
        ));
        $toPath->addFilter('StringTrim');
        $this->addElement($toPath);

        $this->addElement($this->_csrfToken);
        $this->addElement('submit', 'Submit');
    }
}
