<?php

class Form_MoveForm extends Zend_Form
{
    protected $_csrfToken = null;
    protected $_options = array();
    protected $_fromPath = null;

    public function __construct($csrfToken, $options, $fromPath)
    {
        $this->_options = $options;
        $this->_csrfToken = $csrfToken;
        $this->_fromPath = $fromPath;

        parent::__construct();
    }

    public function init()
    {
        $this->setMethod('post');
        $this->setName('move');

        $fromPath = new Zend_Form_Element_Hidden('fromPath');
        $fromPath->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $fromPath->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy the source path for the file(s) or folder(s).')
            )
        );
        $fromPath->addValidator('FilePath', true, array(
            'modifyable' => true,
            'type' => 'dir'
        ));
        $fromPath->addFilter('StringTrim');
        $fromPath->setValue($this->_fromPath);
        $this->addElement($fromPath);

        $files = new Zend_Form_Element_MultiCheckbox('files');
        $files->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $files->setLabel('Select file(s) to move');
        $files->addFilter('StringTrim');
        $files->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy at least one file or folder to move.')
            )
        );
        $files->addValidator('FilePath', true, array(
            'modifyable' => true,
            'pathContext' => 'fromPath'
        ));

        $files->addMultiOptions($this->_options);
        $this->addElement($files);

        $toPath = new Zend_Form_Element_Text('toPath');
        $toPath->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $toPath->setLabel('To Path');
        $toPath->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy the path where the file(s) or folder(s) should be moved to.')
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
