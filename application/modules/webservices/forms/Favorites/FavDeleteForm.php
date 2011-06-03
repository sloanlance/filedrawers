<?php

class Form_Favorites_FavDeleteForm extends Zend_Form
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

        $folderName = new Zend_Form_Element_Text('folderName');
        $folderName->addPrefixPath('Filedrawers_Validate', 'Filedrawers/Validate/', 'validate');
        $folderName->setLabel('Delete Fav Name');
        $folderName->addFilter('StringTrim');
        $folderName->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' =>
                'You must specifiy a name for the favorite.')
            )
        );
        $folderName->addValidator('FavoritesPath', true, array(
            //'pathContext' => 'path',
            'oldexists' => false 
        ));
        $this->addElement($folderName);

        $this->addElement($this->_csrfToken);
        $this->addElement('submit', 'Submit');
    }
}
