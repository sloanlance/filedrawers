<?php

class Form_UploadForm extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setName('upload2');
        $this->setAction('/mfile-bin/upload.cgi');

        $csrf = new Zend_Form_Element_Hash('sessionid');
        $csrf->setDecorators(array('ViewHelper'));
        $this->addElement($csrf);

        $uri = new Zend_Form_Element_Hidden('returnToURI');
        $uri->setDecorators(array('ViewHelper'));
        $this->addElement($uri);

        $path = new Zend_Form_Element_Hidden('path');
        $path->setDecorators(array('ViewHelper'));
        $this->addElement($path);
    }


    public function loadDefaultDecorators() {
        $this->setDecorators(
            array(
                array('ViewScript', 
                    array('viewScript' => 'index/uploadForm.phtml')
                )
            )
        );        
    }
}
