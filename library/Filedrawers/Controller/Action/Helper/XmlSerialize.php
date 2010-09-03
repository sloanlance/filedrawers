<?php
class Filedrawers_Controller_Action_Helper_XmlSerialize extends Zend_Controller_Action_Helper_Abstract
{
    protected $_doc;

    public function __construct()
    {
        $this->_doc = new DOMDocument();
        $this->_doc->formatOutput = true;
    }


    public function direct()
    {        
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setNoRender(true);
        $view = $viewRenderer->view;

        $this->serializeArray($this->_doc, array('results' => $view->getVars()));
        echo $this->_doc->saveXML();
    }


    protected function serializeArray($parent, array $array)
    {
        foreach ($array as $key => $value) {
            if ( ! is_string($key)) {
                $key = 'result';
            }

            if (is_array($value)) {
                $el = $this->_doc->createElement($key);
                $parent->appendChild($el);
                $this->serializeArray($el, $value);
            } else {
                $el = $this->_doc->createElement($key);
                $el->appendChild($this->_doc->createTextNode($value));
                $parent->appendChild($el);
            }
        }
    }
}

