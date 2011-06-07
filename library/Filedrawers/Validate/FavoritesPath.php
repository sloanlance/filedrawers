<?php

class Filedrawers_Validate_FavoritesPath extends Zend_Validate_Abstract
{
    const EXISTS = 'doesNotExist';
    const ALREADY_EXISTS = 'alreadyExists';
    const WRONG_TYPE = 'wrongType';
    const NO_READ = 'noRead';
    const NO_MODIFY = 'noModify';
    const NO_CONTEXT = 'noContext';
    const OLD_EXISTS = 'oldexists';

    protected $_options = array();

    protected $_messageTemplates = array(
        self::EXISTS => "'%value%' does not exist or cannot be modified.",
        self::ALREADY_EXISTS => "'%value%' already exists.",
        self::WRONG_TYPE => "'%value%' is not the correct type.",
        self::NO_READ => "'%value%' cannot be modified.",
        self::NO_MODIFY => "'%value%' cannot be modified.",
        self::NO_CONTEXT => "No path value was found in the input."
    );


    public function __construct(Array $options=array())
    {
        $this->_options = $options;
    }


    public function isValid($value, $context=null)
    {
        $this->_setValue($value);
        $filesystem = Zend_Registry::get('filesystem');
        $input = $value;
         
        if (isset($this->_options['pathContext'])) {
            if ( ! isset($context[$this->_options['pathContext']])) {
                $this->_error(self::NO_CONTEXT);
                return false;
            }
            $value = $filesystem->pathConcat($context[$this->_options['pathContext']], $value);
        }
                
        $info = $filesystem->getInfo($value);
         
        if (isset($this->_options['oldexists']) ) {
            $foldername = $filesystem->favoriteExists($value);
            if ($foldername === false){
                $this->_error(self::EXISTS);
                return false;
            } else {
                    return true;
	    }
        }

        if (isset($this->_options['exists']) ) {
            $foldername = $filesystem->favoriteExists($value);
            if ($foldername === true){
                $this->_error(self::ALREADY_EXISTS);
                return false;
            } 
        }
 
        else if ( !is_array($info)) {
            $this->_error(self::EXISTS);
            return false;
        }

        if (isset($this->_options['type']) && $info['type'] !== $this->_options['type']) {
            $this->_error(self::WRONG_TYPE);
            return false;
        }

        if (isset($this->_options['readable']) && $info['readable'] !== true) {
            $this->_error(self::NO_READ);
            return false;
        }

        if (isset($this->_options['modifyable']) && $info['modifyable'] !== true) {
            $this->_error(self::NO_MODIFY);
            return false;
        }

        return true;
    }
}

