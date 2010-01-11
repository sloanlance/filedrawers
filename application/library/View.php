<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

class View
{
    private $viewData   = array();
    private $renderView = true;

    public function __construct() {
    }


    public function __get( $property )
    {
        if ( isset( $this->viewData[$property] )) {
            return $this->viewData[$property];
        } else {
            return null;
        }
    }


    public function __set( $property, $value )
    {
        $this->viewData[$property] = $value;
    }


    public function __call($helperName, $arguments) {
        $helperNameCap = ucfirst($helperName);
        require_once APPLICATION_PATH . "/views/helpers/$helperNameCap.php";
        $helperClass = 'Helper_' . $helperNameCap;
        $helper = new $helperClass($this);
        return call_user_func_array(array($helper, $helperName), array('test'));
    }


    public function setNoRender()
    {
        $this->renderView = false;
    }


    public function frontRender()
    {
        if ( ! $this->renderView ) {
            return true;
        }

        $router = Router::getInstance();
        $this->_baseUrl = $router->getBaseUrl();

        $route = $router->getRoute();

        $viewFile = '../application/views/scripts/' . strtolower($route[0])
                . '/' . $route[1] . '.phtml';

        $this->doRender( $viewFile );
    }


    public function render( $path )
    {
        $viewFile = '../application/views/scripts/' . $path;

        $this->doRender( $viewFile );
    }


    public function baseUrl()
    {
        return $this->_baseUrl;
    }
    
    
    public function escape($val)
    {
        return htmlspecialchars($val);
    }


    private function doRender( $path )
    {
        if ( ! file_exists( $path )) {
            // throw exception: view does not exist
            exit( 'view does not exist' );
        }

        require_once( $path );
    }
}

