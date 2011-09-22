<?php

class Form_AclForm extends Zend_Form
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
        $this->setName('acl');

        foreach ($this->_options['lists'] as $listName => $list) {
            $lists[$listName] = new Filedrawers_Form_Element_Acl($listName);
            $lists[$listName]->setLabel($listName)
                ->setRights($this->_options['rights'])
                ->setAcl($list);
            $this->addElement($lists[$listName]);
        }

        $this->addElement($this->_csrfToken);
        $this->addElement('submit', 'Submit');
    }
}
