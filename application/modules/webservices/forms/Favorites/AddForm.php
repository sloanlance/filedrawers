<?php

class Form_Favorites_AddForm extends Zend_Form
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
        $this->setName('add');

        $path = new Zend_Form_Element_Text('path');
        $path->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $path->setLabel('Path to existing directory');
        $path->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy the path to folder.')
            )
        );
        $path->addValidator('FavoritesPath', true, array(
            'modifyable' => true,
            'type' => 'dir'
        ));
        $path->addFilter('StringTrim');
        $path->setValue($this->_path);
        $this->addElement($path);

        $folderName = new Zend_Form_Element_Text('folderName');
        $folderName->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $folderName->setLabel('Add Fav Name');
        $folderName->addFilter('StringTrim');
        $folderName->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy a name for the favorite.')
            )
        );
        $folderName->addValidator('FavoritesPath', true, array(
            'pathContext' => 'path',
            'exists' => 'true' 
        ));
        $this->addElement($folderName);

        $this->addElement($this->_csrfToken);
        $this->addElement('submit', 'Submit');
    }
}
