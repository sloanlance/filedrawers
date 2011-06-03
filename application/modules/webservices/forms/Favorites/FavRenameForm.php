<?php

class Form_Favorites_FavRenameForm extends Zend_Form
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

        $oldName = new Zend_Form_Element_Text('oldName');
        $oldName->setLabel('Old Name');
        $oldName->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $oldName->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                '*You must specifiy the name of the file or folder to rename.')
            )
        );

        $oldName->addValidator('FavoritesPath', true, array(
	    'oldexists' => 'false' 
        )
       );

        $oldName->addFilter('StringTrim');
        $this->addElement($oldName);

        $newName = new Zend_Form_Element_Text('newName');
        $newName->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $newName->setLabel('New Name');
        $newName->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                '**You must specifiy a new name for the file or folder.')
            )
        );

        $newName->addValidator('FavoritesPath', true, array(
            'exists' => true 
        ));


        $newName->addFilter('StringTrim');
        $this->addElement($newName);

        $this->addElement($this->_csrfToken);
        $this->addElement('submit', 'Submit');
    }
}
