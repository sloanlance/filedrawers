<?php
class Zend_View_Helper_DisplayErrors extends Zend_View_Helper_Abstract
{
    public function displayErrors($errors)
    {
        $html = '<ul>';

        if ( ! is_array($errors)) {
            return '';
        }

        foreach($errors as $param => $error) {
            if ( ! is_array($error)) {
                continue;
            }

            foreach($error as $message) {
                $html .= '<li><strong>' . $this->view->escape( $param ) .'</strong>: ' . $this->view->escape($message) . '</li>';
            }
        }

        $html .= '</ul>';

        return $html;
    }
}

