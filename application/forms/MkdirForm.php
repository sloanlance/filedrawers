<?php

class Form_MkdirForm extends Zend_Form
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
        $this->setName('mkdir');

        $path = new Zend_Form_Element_Text('path');
        $path->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $path->setLabel('Path');
        $path->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy the path for the file or folder to rename.')
            )
        );
        $path->addValidator('FilePath', true, array(
            'modifyable' => true,
            'type' => 'dir'
        ));
        $path->addFilter('StringTrim');
        $path->setValue($this->_path);
        $this->addElement($path);

        $folderName = new Zend_Form_Element_Text('folderName');
        $folderName->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $folderName->setLabel('Folder Name');
        $folderName->addFilter('StringTrim');
        $folderName->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy a name for the new folder.')
            )
        );
        $folderName->addValidator('FilePath', true, array(
            'pathContext' => 'useContext',
            'exists' => 'checkExisting'
        ));
        $this->addElement($folderName);

        $this->addElement($this->_csrfToken);
        $this->addElement('submit', 'Submit');
    }
}
