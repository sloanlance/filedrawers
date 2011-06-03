<?php

class Form_RenameForm extends Zend_Form
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
        $this->setName('rename');

        $path = new Zend_Form_Element_Text('path');
        $path->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $path->setLabel('Path');
        $path->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy the path for the file or folder to rename.')
            )
        );
        $path->addValidator('FavoritesPath', true, array(
            'modifyable' => true,
            'type' => 'dir'
        ));
        $path->addFilter('StringTrim');
        $path->setValue($this->_path);
        $this->addElement($path);

        $oldName = new Zend_Form_Element_Text('oldName');
        $oldName->setLabel('Old Name');
        $oldName->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $oldName->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy the name of the file or folder to rename.')
            )
        );
        $oldName->addValidator('FilePath', true, array(
            'modifyable' => true,
            'pathContext' => 'path'
        ));
        $oldName->addFilter('StringTrim');
        $this->addElement($oldName);

        $newName = new Zend_Form_Element_Text('newName');
        $newName->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $newName->setLabel('New Name');
        $newName->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy a new name for the file or folder.')
            )
        );
        $newName->addValidator('FavoritesPath', true, array(
            'pathContext' => 'path',
            'exists' => 'checkExisting'
        ));
        $newName->addFilter('StringTrim');
        $this->addElement($newName);

        $this->addElement($this->_csrfToken);
        $this->addElement('submit', 'Submit');
    }
}
